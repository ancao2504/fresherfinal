<?php

/*
	Webhook MiTekConnector
	Author: Hieu Nguyen
	Date: 2019-04-12
	Purpose: to handle request from MiTek webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class MiTekConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();
		
		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$rawRequest = $request->getAllPurified();
		$data = $request->get('value');
		$data['event'] = $request->get('event');

		// Handle skill-based routing
		if ($rawRequest['action'] == 'GetRouting' && !empty($rawRequest['fromnumber'])) {
			$response = PBXManager_MiTek_Connector::handleSkillBasedRouting($rawRequest['fromnumber'], $rawRequest['hotline']);
			CallCenterUtils::saveLog('[MiTek] Skill-Based Routing request', null, $rawRequest, $response);

			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}

		CallCenterUtils::saveLog('[MiTek] Webhook data', null, $rawRequest);

		if (count($rawRequest) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Ignore agent status event
		if ($data['event'] == 'AgentStatus') {
			$this->respond('success');
			exit;
		}

		// Ignore empty caller or receiver signals
		if (empty($data['fromnumber']) || empty($data['tonumber'])) {
			$this->respond('fromnumber and tonumber are required');
			exit;
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_MiTek_Connector::isExists($data['callrefid'], $data['event'])) {
			$this->respond('success');
			exit;
		}

		$data['direction'] = PBXManager_MiTek_Connector::getCallDirection($data['calltype']);

		// Check receiver
		$callId = $data['callrefid'];
		$receiverId = '';

		if ($data['event'] == 'ringing') {
			$agentExtNumber = CallCenterUtils::getAgentExtNumber($data['fromnumber'], $data['tonumber'], $data['direction']);
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId) && $data['event'] != 'completed') {
			$msg = [
				'call_id' => $callId,                       // Required
				'receiver_id' => $receiverId,               // Required (CRM user id)
				'state' => strtoupper($data['event']),      // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($data['event'] == 'ringing') {
				$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND

				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['fromnumber'], $data['tonumber'], $data['direction']);
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

			PBXManager_MiTek_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[MiTek] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['event'] == 'ringing' && !empty($receiverId)) || $data['event'] != 'ringing') {
			PBXManager_MiTek_Connector::handleCallEvent($data);
		} 

		$this->respond('success');       
	}

	// Return required response for this gateway
	function respond($message) {
		$response = [
			'code' => 200,
			'message' => $message
		];

		header('Content-Type: application/json');
		echo json_encode($response);
	}
}