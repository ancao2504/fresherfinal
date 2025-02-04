<?php

/*
	Webhook XorcomConnector
	Author: Hieu Nguyen
	Date: 2021-05-17
	Purpose: to handle request from Xorcom AMI Bridge Proxy and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class XorcomConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$rawRequest = $request->getAllPurified();

		CallCenterUtils::saveLog('[Xorcom] Webhook data', null, $rawRequest);

		if (count($rawRequest) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Ignore unused signals
		if (!in_array($rawRequest['Event'], ['Newstate', 'Hangup'])) {
			exit;
		}

		preg_match('/SIP\/\d+/', $rawRequest['Channel'], $matches);
		
		if (!$matches) {
			exit;
		}

		$data = PBXManager_Xorcom_Connector::processEventData($rawRequest);

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_Xorcom_Connector::isExists($data['callid'], $data['state'])) {
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

			// Special case: up event when make call using softphone
			if ($data['state'] == 'Up') {
				$callData = PBXManager_Data_Model::getCallData($callId);
				
				// Ringing event has no customer number so we have to catch customer number in answered event
				if ($callData['direction'] == 'outbound' && empty($callData['customernumber'])) {
					$customerPhoneNumber = $rawRequest['ConnectedLineNum'];
					$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, true, $agentExtNumber, true);

					if ($customer) {
						CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);
						$data['customer'] = $customer;
						$data['customer_number'] = $customerPhoneNumber;
					}
				}
			}

			PBXManager_Xorcom_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog("[Xorcom] Data sent to call popup for {$agentExtNumber}", null, $msg);
		}

		// Save call history
		if (($data['state'] == 'Ringing' && !empty($receiverId)) || $data['state'] != 'Ringing') {
			PBXManager_Xorcom_Connector::handleCallEvent($data);
		}
	}
}