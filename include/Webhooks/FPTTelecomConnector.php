<?php

/*
	Webhook FPTTelecomConnector
	Author: Hieu Nguyen
	Date: 2021-02-24
	Purpose: to handle request from FPTTelecom webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class FPTTelecomConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		CallCenterUtils::saveLog('[FPTTelecom] Webhook data', null, $data);

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Ignore unused events
		if (in_array($data['event_type'], ['call_start', 'target_add'])) {
			CallCenterUtils::saveLog('[FPTTelecom] Ignore this event!');
			exit;
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_FPTTelecom_Connector::isExists($data['call_id'], $data['event_type'])) {
			CallCenterUtils::saveLog('[FPTTelecom] Duplicate event!');
			exit;
		}

		// Preprocess
		PBXManager_FPTTelecom_Connector::cleanupPhoneNumbers($data);
		$data['direction'] = (strlen($data['caller']) <= 5) ? 'outbound' : 'inbound';   // Work-arround as this does not provide call direction

		// Work arround for inconsistent callee info in inbound call
		if ($data['direction'] == 'inbound') {
			$data['callee'] = $data['call_target'];
		}

		// Ignore queue event in inbound call
		if ($data['direction'] == 'inbound' && $data['callee'] >= 5000) {
			CallCenterUtils::saveLog('[FPTTelecom] Ignore this Queue event!');
			exit;
		}

		// Ignore queue event in outbound call
		if ($data['direction'] == 'outbound' && $data['caller'] >= 5000) {
			CallCenterUtils::saveLog('[FPTTelecom] Ignore this Queue event!');
			exit;
		}

		// Check receiver
		$callId = $data['call_id'];
		$receiverId = '';

		if ($data['event_type'] == 'target_ringing') {
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
				'target_ringing' => 'ringing',
				'call_established' => 'answered',
				'call_ended' => 'hangup',
				'cdr' => 'hangup',
			];

			$msg = [
				'call_id' => $callId,                               // Required
				'receiver_id' => $receiverId,                       // Required (CRM user id)
				'state' => $stateMapping[$data['event_type']],      // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['event_type'] == 'target_ringing') {
				$msg['direction'] = strtoupper($data['direction']); // Must be INBOUND/OUTBOUND

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

			if ($data['event_type'] == 'cdr') {
				$msg['duration'] = $data['talk_duration'];
			}

			PBXManager_FPTTelecom_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[FPTTelecom] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		PBXManager_FPTTelecom_Connector::handleCallEvent($data);
	}
}