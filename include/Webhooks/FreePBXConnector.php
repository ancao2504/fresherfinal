<?php

/*
	Webhook FreePBXConnector
	Author: Hieu Nguyen
	Date: 2019-06-03
	Purpose: to handle request from FreePBX AMI Bridge Proxy and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class FreePBXConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		$activeProviderInstance = CallCenterUtils::checkConfig();
		$parameters = $activeProviderInstance->getParameters();
		$deviceBrand = $parameters['device_brand'];

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$rawRequest = $request->getAllPurified();

		CallCenterUtils::saveLog('[FreePBX] Webhook data', null, $rawRequest);

		if (count($rawRequest) <= 2) {
			echo 'Listening!';
			exit;
		}

		$handle = true;

		// Ignore non-sip signals
		if (strpos($rawRequest['Channel'], 'SIP') === false && strpos($rawRequest['DestinationChannel'], 'SIP') === false) {
			$handle = false;
		}
		// Ignore useless sip Ring signal in inbound call 
		else if ($rawRequest['Event'] == 'Newstate' && $rawRequest['ChannelStateDesc'] == 'Ring') {
			$handle = false;
		}
		// Ignore useless sip Ringing signal in outbound call 
		else if ($rawRequest['Event'] == 'Newstate' && $rawRequest['Context'] == 'from-internal' && $rawRequest['Exten'] == '') {
			$handle = false;
		}
		// Ignore sip sinals with useless Exten
		else if (!empty($rawRequest['Exten']) && strlen($rawRequest['Exten']) < 3) {
			$handle = false;
		}
		// Ignore sip sinals with useless Context
		else if (!in_array($rawRequest['Context'], ['from-internal', 'from-pstn'])) {
			$handle = false;
		}

		if (!$handle) {
			exit;
		}

		$data = PBXManager_FreePBX_Connector::processEventData($rawRequest);

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_FreePBX_Connector::isExists($data['callid'], $data['state'])) {
			exit;
		}

		// Check receiver
		$callId = $data['callid'];
		$receiverId = '';

		if ($data['state'] == 'Ringing') {
			$agentExtNumber = CallCenterUtils::getAgentExtNumber($data['caller'], $data['callee'], $data['direction']);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'Ringing' => 'RINGING',
				'Up' => 'ANSWERED',
				'Hangup' => 'HANGUP'
			];

			$msg = [
				'call_id' => $callId,                       // Required
				'receiver_id' => $receiverId,               // Required (CRM user id)
				'state' => $stateMapping[$data['state']],   // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['state'] == 'Ringing') {
				$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND

				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['callee'], $data['direction']);
				$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
				CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

				// Register global variable so that it can be reused in handleCallEvent function
				$GLOBALS['agent'] = $agent;
				$GLOBALS['customer'] = $customer;

				// Send related call log id to update this call log instead of creating a new one
				if (!empty($customer['call_log_id'])) {
					$msg['call_log_id'] = $customer['call_log_id'];
				}
			}

			PBXManager_FreePBX_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog("[FreePBX - {$deviceBrand}] Data sent to call popup for {$agentExtNumber}", null, $msg);
		}

		// Save call history
		if (($data['state'] == 'Ringing' && !empty($receiverId)) || $data['state'] != 'Ringing') {
			PBXManager_FreePBX_Connector::handleCallEvent($data);
		} 
	}
}