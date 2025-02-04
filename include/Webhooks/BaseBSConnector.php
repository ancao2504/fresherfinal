<?php

/*
	Webhook BaseBSConnector
	Author: Hieu Nguyen
	Date: 2021-07-06
	Purpose: to handle request from BaseBS webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class BaseBSConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		saveLog('CALLCENTER', '[BaseBS] Webhook data', $data);

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_BaseBS_Connector::isExists($data['LinkedID'], $data['CallStatus'])) {
			exit;
		}

		// Convert to readable direction info
		$data['direction'] = ($data['InOutCall'] == '1' ? 'inbound' : 'outbound');

		// Check receiver
		$callId = $data['LinkedID'];
		$receiverId = '';

		if ($data['CallStatus'] == 'RINGING') {
			$agentExtNumber = $data['ExtentionID'];
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'RINGING' => 'ringing',
				'ANSWER' => 'answered',
				'NOANSWER' => 'hangup',
				'SUCCESS' => 'hangup',
			];

			$msg = [
				'call_id' => $callId,                           // Required
				'receiver_id' => $receiverId,                   // Required (CRM user id)
				'state' => $stateMapping[$data['CallStatus']],  // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['CallStatus'] == 'RINGING') {
				$msg['direction'] = strtoupper($data['direction']); // Must be INBOUND/OUTBOUND
				$msg['hotline'] = $data['Hotline'];                 // Display hotline number where the call is handled

				$customerPhoneNumber = $data['CallPhone'];
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

			if ($data['CallStatus'] == 'NOANSWER' || $data['CallStatus'] == 'SUCCESS') {
				$msg['duration'] = $data['TotalDuration'];
			}

			PBXManager_BaseBS_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[BaseBS] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		PBXManager_BaseBS_Connector::handleCallEvent($data);
	}
}