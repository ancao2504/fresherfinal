<?php

/*
	Webhook StringeeConnector
	Author: Hieu Nguyen
	Date: 2020-04-20
	Purpose: to handle request from Stringee webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class StringeeConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();
		
		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		// Handle based skill routing (step 1)
		if ($data['action'] == 'GetIVRRouting' && !empty($data['fromNumber'])) {
			$response = PBXManager_Stringee_Connector::getIvrRouting($data['fromNumber'], $data['toNumber']);
			CallCenterUtils::saveLog('[Stringee] IVR Routing request', null, $data, $response);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}
		
		// Handle based skill routing (step 2)
		if ($data['action'] == 'GetEXTRouting' && !empty($data['calls'])) {
			$response = PBXManager_Stringee_Connector::getExtRouting($data['calls']);
			CallCenterUtils::saveLog('[Stringee] Extension Routing request', null, $data, $response);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}

		CallCenterUtils::saveLog('[Stringee] Webhook data', null, $data);
		$data['direction'] = PBXManager_Stringee_Connector::getCallDirection($data);
		$isTransferredCall = PBXManager_Data_Model::isTransferredCall($data['call_id']);

		// Stringee now send CDR and ENDED events simultaneously so we have to store both event data in cache file to do work-arround
		if ($data['call_status'] == 'ended' || !empty($data['recording_url'])) {
			PBXManager_Stringee_Connector::writeEndCallEventCache($data, $isTransferredCall);
		}
		// We should reset the end call event cache file when the new call arrive
		elseif (in_array($data['call_status'], ['started', 'ringing'])) {
			$cacheFile = PBXManager_Stringee_Connector::getEndCallEventCacheFile($data, $isTransferredCall);
			PBXManager_Stringee_Connector::resetEndCallEventCache($cacheFile);
		}

		if (count($data) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Ignore empty caller or receiver signals
		if ((empty($data['from']) || empty($data['to'])) && empty($data['recording_url'])) {
			CallCenterUtils::saveLog('[Stringee] Ignore this event!');
			exit;
		}

		// Ignore signals that have no required info
		if (
			($data['callCreatedReason'] == 'EXTERNAL_CALL_IN' && $data['call_status'] == 'created') ||
			($data['callCreatedReason'] == 'CLIENT_MAKE_CALL' && in_array($data['call_status'], ['created', 'started']) && !$isTransferredCall)
		) {
			CallCenterUtils::saveLog('[Stringee] Ignore this event!');
			exit;
		}

		if (
			(empty($data['call_status']) && empty($data['recording_url'])) ||
			(in_array($data['call_status'], ['started', 'ringing']) && empty($data['callCreatedReason'])) 
		) {
			CallCenterUtils::saveLog('[Stringee] Ignore this event!');
			exit;
		}

		// Ignore unused signals from outbound call by making call using api
		if ($data['callCreatedReason'] == 'SERVER_CALL_OUT_BY_MAKE_CALL_TO_APP_BEFORE') {
			if (
				in_array($data['call_status'], ['created']) //||               // Before agent accept call request
				// ($data['call_status'] == 'ringing' && $data['direction'] == 'OUTBOUND') // Customer phone ring but has no agent info, use agent accept for ringing instead
			) {
				CallCenterUtils::saveLog('[Stringee] Ignore this event!');
				exit;
			}
		}

		// Ignore unused signals from inbound call
		if ($data['call_status'] == 'connect_failed' && !$isTransferredCall) {
			CallCenterUtils::saveLog('[Stringee] Ignore this event!');
			exit;
		}

		// Agent reject an incoming call, delete it from pbx log so that the call can be handled by other agent
		if (
			$data['callCreatedReason'] == 'EXTERNAL_CALL_IN' && $data['call_status'] == 'agentEnded' && 
			PBXManager_Data_Model::getCallStatus($data['call_id']) == 'ringing' && !$isTransferredCall
		) {
			$callRejected = true;
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($data['call_id']);
			PBXManager_Data_Model::deleteCall($data['call_id']);
		}

		// Check for transfered call
		if ($isTransferredCall) {
			$data['call_id'] = $data['call_id'] . '_transferred';
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		$data['state'] = $data['call_status'] ?? 'recorded';
		if ($data['state'] == 'started') $data['state'] = 'ringing';    // Ringing in inbound call

		if (PBXManager_Stringee_Connector::isExists($data['call_id'], $data['state'])) {
			CallCenterUtils::saveLog('[Stringee] Ignore duplicate event!');
			exit;
		}

		// Added by Phu Vo on 2020.05.16 to handle missed call
		if ($data['direction'] == 'inbound' && $data['state'] == 'ended') {
			// Check previous status to determine if it is a missed call (ringing instead of answered)
			$previousState = PBXManager_Data_Model::getCallStatus($data['call_id']);

			if ($previousState == 'ringing') {
				PBXManager_Stringee_Connector::handleMissedCallEvent($data);
				exit;
			}
		}
		// End Phu Vo

		// Check receiver
		$callId = $data['call_id'];
		$receiverId = $receiverId ?? '';

		if ($data['state'] == 'ringing') {
			$agentExtNumber = PBXManager_Stringee_Connector::getAgentExtNumberFromRingingEvent($data, $isTransferredCall);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			if (!$receiverId) {
				$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
			}
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'ringing' => 'RINGING',
				'answered' => 'ANSWERED',
				'ended' => 'HANGUP',
				'recorded' => 'HANGUP',
				'connect_failed' => 'HANGUP',   // Transferred call rejected
			];

			$state = $stateMapping[$data['state']];
			if ($callRejected) $state = 'REJECTED'; // Extra state for rejected call
			if (empty($state)) return;

			$msg = [
				'call_id' => $callId,           // Required
				'receiver_id' => $receiverId,   // Required (CRM user id)
				'state' => $state,              // Must be RINGING/REJECTED/ANSWERED/HANGUP/TRANSFERRED/COMPLETED/CUSTOMER_INFO
			];

			if ($data['state'] == 'ringing') {
				$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND
				$msg['hotline'] = $data['direction'] == 'inbound' ? $data['to']['alias'] : $data['from']['number']; // Display hotline number where the call is handled
				
				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from']['number'], $data['to']['number'], $data['direction']);

				if (strpos($customerPhoneNumber, 'btncall_') === 0) {
					$customerPhoneNumber = str_replace('btncall_', '', $customerPhoneNumber);
					$msg['from_free_call_btn'] = true;
				}

				// This is a transferred call
				if ($isTransferredCall) {
					// Get transfer info from parent call
					$parentCallId = str_replace('_transferred', '', $callId);
					$transferInfo = PBXManager_Data_Model::getExtraDataFromCall($parentCallId);

					$msg['transferred'] = true;
					$msg['transferred_from'] = [
						'name' => $transferInfo['transferrer_name'], 
						'ext' => $transferInfo['transferrer_ext']
					];

					// Get customer info from parent call
					$customer = PBXManager_Data_Model::getCustomerFromCall($parentCallId);
				}
				// This is a normal call, get customer info from this call
				else {
					$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
				}
				
				CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

				// Register global variable so that it can be reused in handleCallEvent function
				$GLOBALS['agent'] = $agent;
				$GLOBALS['customer'] = $customer;

				// Send related call log id to update this call log instead of creating a new one
				if (!empty($customer['call_log_id'])) {
					$msg['call_log_id'] = $customer['call_log_id'];
				}
			}

			PBXManager_Stringee_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[Stringee] Data sent to call popup for ' . $agentExtNumber, null, $msg);
			
			// Exit here when the call is rejected
			if ($callRejected) {
				CallCenterUtils::saveLog('[Stringee] This is a rejected call. No need to save call log!');
				exit;
			}
		}

		// Save call history
		if (($data['state'] == 'ringing' && !empty($receiverId)) || $data['state'] != 'ringing') {
			PBXManager_Stringee_Connector::handleCallEvent($data, $isTransferredCall);
		} 
	}
}