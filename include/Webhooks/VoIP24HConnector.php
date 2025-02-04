<?php

/*
	Webhook VoIP24HConnector
	Author: Hieu Nguyen
	Date: 2019-04-12
	Purpose: to handle request from VoIP24H webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class VoIP24HConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();
		
		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		// Handle skill-based routing
		if ($data['action'] == 'GetRouting' && !empty($data['phone'])) {
			$agentExtNumber = PBXManager_VoIP24H_Connector::handleSkillBasedRouting($data['phone'], $data['did']);
			CallCenterUtils::saveLog('[VoIP24H] Skill-Based Routing request', null, $data, $agentExtNumber);

			echo $agentExtNumber;
			exit;
		}

		// Handle missed call event
		if ($data['type'] == 'MissedCallEvent' && !empty($data['src'])) {
			CallCenterUtils::saveLog('[VoIP24H] Missed call event data', null, $data);
			PBXManager_VoIP24H_Connector::handleMissedCallEvent($data);
			exit;
		}

		// Handle auto call signal
		if ($data['type'] == 'AutoCall' && !empty($data['espeakid'])) {
			CallCenterUtils::saveLog('[VoIP24H] AutoCall Webhook data', null, $data);
			PBXManager_VoIP24H_Connector::handleAutoCallEvent($data);
			exit;
		}

		CallCenterUtils::saveLog('[VoIP24H] Webhook data', null, $data);

		if (count($data) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Ignore empty caller or receiver signals
		if (empty($data['extend']) || empty($data['phone'])) {
			exit;
		}

		// Ignore unused signal in outbound call by api
		if ($data['note'] == 'caller') {
			exit;
		}

		// Special case: customer phone ringing in outbound call by api
		if ($data['state'] == 'Ringing') {
			$data['state'] = 'Ring';
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_VoIP24H_Connector::isExists($data['callid'], $data['state'])) {
			exit;
		}

		$callId = $data['callid'];
		$agentExtNumber = $data['extend'];
		$data['direction'] = PBXManager_VoIP24H_Connector::getCallDirection($data);

		// In INBOUND call, this provider use the same callid for all subcalls so we must create a unique callid for each EXT in the same ringgroup
		if ($data['direction'] == 'inbound') {
			$callId .= '-' . $agentExtNumber;
			$data['callid'] = $callId;	// Set id back for call log
		}

		// Check receiver
		$receiverId = '';

		if ($data['state'] == 'Ring') {
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$stateMapping = [
				'Ring' => 'RINGING',
				'Up' => 'ANSWERED',
				'Hangup' => 'HANGUP',
				'Cdr' => 'HANGUP',
			];

			$msg = [
				'call_id' => $callId,                       // Required
				'receiver_id' => $receiverId,               // Required (CRM user id)
				'state' => $stateMapping[$data['state']],   // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['state'] == 'Ring') {
				$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND

				$customerPhoneNumber = $data['phone'];
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

			if ($data['state'] == 'Cdr' && !empty($data['cdr']['duration'])) {
				$msg['duration'] = $data['cdr']['duration'];
			}

			PBXManager_VoIP24H_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[VoIP24H] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['state'] == 'Ring' && !empty($receiverId)) || $data['state'] != 'Ring') {
			PBXManager_VoIP24H_Connector::handleCallEvent($data);
		}
	}
}