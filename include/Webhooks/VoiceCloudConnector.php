<?php

/*
	Webhook VoiceCloudConnector
	Author: Phu Vo
	Date: 2020-06-15
	Purpose: to handle request from VoiceCloud webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class VoiceCloudConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Handle skill-based routing
		if ($data['action'] == 'GetRouting' && !empty($data['from_number'])) {
			$result = PBXManager_VoiceCloud_Connector::handleSkillBasedRouting($data['from_number'], $data['hotline']);
			CallCenterUtils::saveLog('[VoiceCloud] Skill-Based Routing request', null, $data, $result);

			echo json_encode($result);
			exit;
		}

		// Handle missed call event
		if ($data['event'] == 'MissedCall' && !empty($data['phone_number'])) {
			CallCenterUtils::saveLog('[VoiceCloud] Missed call event data', null, $data);
			PBXManager_VoiceCloud_Connector::handleMissedCallEvent($data);
			exit;
		}

		CallCenterUtils::saveLog('[VoiceCloud] Webhook data', null, $data);

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_VoiceCloud_Connector::isExists($data['call_id'], $data['state'])) {
			exit;
		}

		// Check receiver
		$callId = $data['call_id'];
		$receiverId = '';

		if ($data['state'] == 'RINGING') {
			$agentExtNumber = CallCenterUtils::getAgentExtNumber($data['from_number'], $data['to_number'], $data['direction']);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);

			if (!empty($agent)) $receiverId = $agent['id'];
		} else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'RINGING' => 'RINGING',
				'ANSWERED' => 'ANSWERED',
				'HANGUP' => 'HANGUP',
				'CDR' => 'HANGUP',
			];

			$msg = [
				'call_id' => $callId,
				'receiver_id' => $receiverId,
				'state' => $stateMapping[$data['state']],
			];

			if ($data['state'] == 'RINGING') {
				$msg['direction'] = $data['direction'];

				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from_number'], $data['to_number'], $data['direction']);
				$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'OUTBOUND', $agentExtNumber, true);
				CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

				// Register global variable so that it can be reused in handleCallEvent function
				$GLOBALS['agent'] = $agent;
				$GLOBALS['customer'] = $customer;

				// Added by Hieu Nguyen on 2020-02-04 to send related call log id to update this call log instead of creating a new one
				if (!empty($customer['call_log_id'])) {
					$msg['call_log_id'] = $customer['call_log_id'];
				}
				// End Hieu Nguyen
			}

			PBXManager_VoiceCloud_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[VoiceCloud] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['state'] == 'RINGING' && !empty($receiverId)) || $data['state'] != 'RINGING') {
			PBXManager_VoiceCloud_Connector::handleCallEvent($data);
		}
	}
}
