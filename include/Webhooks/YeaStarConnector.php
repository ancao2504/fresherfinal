<?php

/*
	Webhook YeaStarConnector
	Author: Hieu Nguyen
	Date: 2019-06-27
	Purpose: to handle request from YeaStar AMI Bridge Proxy and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class YeaStarConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();
		
		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$rawRequest = $request->getAllPurified();

		CallCenterUtils::saveLog('[YeaStar] Webhook data', null, $rawRequest);

		if (count($rawRequest) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Ignore useless signals
		if (strpos($rawRequest['Channel'], 'trunk') !== false) {
			if (!in_array($rawRequest['Event'], ['DialBegin', 'Cdr']) && !($rawRequest['Event'] == 'Newstate' && strpos($rawRequest['Context'], 'callin_trunk') !== false)) {
				exit;
			}
		}

		// Ignore useless dial begin signals
		if ($rawRequest['Event'] == 'DialBegin' && strpos($rawRequest['DialString'], '@') === false) {
			exit;
		}

		// Ignore useless up signals in outbound call with orignate command
		if ($rawRequest['Event'] == 'Newstate' && $rawRequest['ChannelStateDesc'] == 'Up' && $rawRequest['Exten'] == 's') {
			exit;
		}

		$data = PBXManager_YeaStar_Connector::processEventData($rawRequest);

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_YeaStar_Connector::isExists($data['callid'], $data['state'])) {
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
				'Hangup' => 'HANGUP',
				'CDR' => 'HANGUP',
			];

			$msg = [
				'call_id' => $callId,                       // Required
				'receiver_id' => $receiverId,               // Required (CRM user id)
				'state' => $stateMapping[$data['state']],   // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['state'] == 'Ringing') {
				if ($data['state'] == 'Ringing') {
					$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND
				}

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

			if ($data['state'] == 'CDR') {
				$msg['duration'] = $data['duration'];
			}

			PBXManager_YeaStar_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[YeaStar] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['state'] == 'Ringing' && !empty($receiverId)) || $data['state'] != 'Ringing') {
			PBXManager_YeaStar_Connector::handleCallEvent($data);
		} 
	}
}