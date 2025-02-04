<?php

/*
	Webhook SouthTelecomConnector
	Author: Hieu Nguyen
	Date: 2018-10-03
	Purpose: to handle request from SouthTelecom webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class SouthTelecomConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest(false);
		$data = $request->getAllPurified();

		// Handle skill-based routing
		if ($data['action'] == 'GetRouting' && !empty($data['callernumber'])) {
			$response = PBXManager_SouthTelecom_Connector::handleSkillBasedRouting($data['callernumber'], $data['didnumber']);
			CallCenterUtils::saveLog('[SouthTelecom] Skill-Based Routing request', null, $data, $response);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}

		// Handle missed call event
		if ($data['callstatus'] == 'LocalSIPCDR' && $data['disposition'] == 'NO ANSWER' && !empty($data['src'])) {
			CallCenterUtils::saveLog('[SouthTelecom] Missed call event data', null, $data);
			PBXManager_SouthTelecom_Connector::handleMissedCallEvent($data);
			exit;
		}

		// Handle auto call signal
		if ($data['type'] == 'AutoCall' && !empty($data['ua_uuid'])) {
			CallCenterUtils::saveLog('[SouthTelecom] AutoCall Webhook data', null, $data);
			PBXManager_SouthTelecom_Connector::handleAutoCallEvent($data);
			exit;
		}

		CallCenterUtils::saveLog('[SouthTelecom] Webhook data', null, $data);

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Ignore unsued events
		if ($data['callstatus'] == 'SyncCurCalls') {
			exit;
		}

		// Ignore empty caller or receiver signals
		if ($data['callstatus'] == 'Dialing' && (empty($data['callernumber']) || empty($data['destinationnumber']))) {
			exit;
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_SouthTelecom_Connector::isExists($data['calluuid'], $data['callstatus'])) {
			exit;
		}

		// Store hotline number in Start event of inbound call to trieve it back when Dialing event arrive
		if ($data['direction'] == 'inbound' && $data['callstatus'] == 'Start') {
			$customerNumber = $data['callernumber'];
			PBXManager_SouthTelecom_Connector::saveInboundCache($customerNumber, $data);
			echo 'Cached';
			exit;
		}

		// Check receiver
		$callId = $data['calluuid'];
		$receiverId = '';

		if ($data['callstatus'] == 'Dialing') {
			$agentExtNumber = CallCenterUtils::getAgentExtNumber($data['callernumber'], $data['destinationnumber'], $data['direction']);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			if ($data['direction'] == 'inbound') {
				if ($data['callstatus'] == 'DialAnswer' || $data['callstatus'] == 'HangUp' || $data['callstatus'] == 'CDR') {
					$callId = PBXManager_Data_Model::getLatestSubCallId($data['calluuid']);
				}
			}
			
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'Dialing' => 'RINGING',
				'DialAnswer' => 'ANSWERED',
				'HangUp' => 'HANGUP',
				'CDR' => 'HANGUP',
				'Trim' => 'HANGUP',
			];

			$msg = [
				'call_id' => $callId,                               // Required
				'receiver_id' => $receiverId,                       // Required (CRM user id)
				'state' => $stateMapping[$data['callstatus']],      // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['callstatus'] == 'Dialing') {
				$customerPhoneNumber = PBXManager_SouthTelecom_Connector::getCustomerPhoneNumber($data['callernumber'], $data['destinationnumber'], $data['direction']);
				$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
				
				// No hotline info in Dialling event of inbound call so we have to get it from the cached data of event Start
				if ($data['direction'] == 'inbound') {
					$cacheData = PBXManager_SouthTelecom_Connector::getInboundCache($customerPhoneNumber);
					
					if (!empty($cacheData['dnis'])) {
						$data['dnis'] = $cacheData['dnis'];
					}
				}
				
				$msg['direction'] = strtoupper($data['direction']); // Must be INBOUND/OUTBOUND
				$msg['hotline'] = $data['dnis'];                    // Display hotline number where the call is handled
				CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

				// Register global variable so that it can be reused in handleCallEvent function
				$GLOBALS['agent'] = $agent;
				$GLOBALS['customer'] = $customer;

				// Send related call log id to update this call log instead of creating a new one
				if (!empty($customer['call_log_id'])) {
					$msg['call_log_id'] = $customer['call_log_id'];
				}
			}

			if ($data['callstatus'] == 'CDR') {
				$msg['duration'] = $data['totalduration'];
			}

			PBXManager_SouthTelecom_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[SouthTelecom] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['callstatus'] == 'Dialing' && !empty($receiverId)) || $data['callstatus'] != 'Dialing') {
			PBXManager_SouthTelecom_Connector::handleCallEvent($data);
		}
	}
}