<?php

/*
	Webhook Tel4VNConnector
	Author: Hieu Nguyen
	Date: 2021-02-24
	Purpose: to handle request from Tel4VN webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class Tel4VNConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		saveLog('CALLCENTER', '[Tel4VN] Webhook data', $data);

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_Tel4VN_Connector::isExists($data['call_id'], $data['state'])) {
			exit;
		}

		// Check receiver
		$callId = $data['call_id'];
		$receiverId = '';

		if ($data['state'] == 'ringing') {
			$agentExtNumber = CallCenterUtils::getAgentExtNumber($data['from_number'], $data['to_number'], $data['direction']);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'ringing' => 'ringing',
				'answered' => 'answered',
				'hangup' => 'hangup',
				'cdr' => 'hangup',
			];

			$msg = [
				'call_id' => $callId,                       // Required
				'receiver_id' => $receiverId,               // Required (CRM user id)
				'state' => $stateMapping[$data['state']],   // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['state'] == 'ringing') {
				$msg['direction'] = strtoupper($data['direction']); // Must be INBOUND/OUTBOUND
				$msg['hotline'] = $data['hotline'];                 // Display hotline number where the call is handled

				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from_number'], $data['to_number'], $data['direction']);
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

			if ($data['state'] == 'cdr') {
				$msg['duration'] = $data['duration'];
			}

			PBXManager_Tel4VN_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[Tel4VN] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		PBXManager_Tel4VN_Connector::handleCallEvent($data);
	}
}