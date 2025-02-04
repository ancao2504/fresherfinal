<?php

/*
	Webhook VCSConnector
	Author: Hieu Nguyen
	Date: 2021-10-05
	Purpose: to handle request from VCS Web Client and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class VCSConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CallCenterUtils::checkConfig();

		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$data = $request->getAllPurified();

		saveLog('CALLCENTER', '[VCS] Webhook data', $data);

		if (count($data) <= 2) {
			echo 'Listening!';
		}

		// Event Ended will be sent directly to Webhook URL
		if ($data['Status'] == 'Ended') {
			checkSecretKey($request->get('secret_key'));	// Check secret key before handling CDR event
			$callId = $data['CallID'];
			$direction = $data['Direction'] == 'Incoming' ? 'INBOUND' : 'OUTBOUND';
			$state = 'Ended';
		}
		// Other events will be sent from the Web Client
		else {
			if (session_start() && empty($_SESSION['AUTHUSERID'])) return;		// Accept request from logged in user only
			$otherPartyNumber = $data['otherPartyNumber'];
			$currentExtension = $data['currentExtension'];
			$callId = $otherPartyNumber['CallID'];
			$direction = $otherPartyNumber['Incoming'] == 'true' ? 'INBOUND' : 'OUTBOUND';
			$state = $otherPartyNumber['State'];
			$customerPhoneNumber = $otherPartyNumber['OtherPartyNumber'];
			$agentExtNumber = $currentExtension['Number'];
			$hotline = '';	// TODO
		}

		$stateMapping = [
			1 => 'ringing',	// Inbound
			2 => 'ringing',	// Outbound
			3 => 'answered',
			5 => 'transfered',
			6 => 'hangup',
			'Ended' => 'cdr',
		];

		$state = $stateMapping[$state];

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_VCS_Connector::isExists($callId, $state)) {
			exit;
		}

		// Check receiver
		$receiverId = '';

		if ($state == 'ringing') {
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			
			if (!empty($agent)) $receiverId = $agent['id'];
		}
		else {
			$receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
		}

		// Send call event to Call Center Bridge
		if (!empty($receiverId)) {
			$msg = [
				'call_id' => $callId,			// Required
				'receiver_id' => $receiverId,	// Required (CRM user id)
				'state' => $state,				// Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
			];

			if ($state == 'ringing') {
				$msg['direction'] = strtoupper($direction);	// Must be INBOUND/OUTBOUND
				$msg['hotline'] = $hotline;					// Display hotline number where the call is handled

				$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $direction == 'OUTBOUND', $agentExtNumber, true);
				CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

				// Register global variable so that it can be reused in handleCallEvent function
				$GLOBALS['agent'] = $agent;
				$GLOBALS['customer'] = $customer;

				// Send related call log id to update this call log instead of creating a new one
				if (!empty($customer['call_log_id'])) {
					$msg['call_log_id'] = $customer['call_log_id'];
				}
			}

			if ($state == 'cdr') {
				$msg['state'] = 'hangup';
				$msg['duration'] = $data['TalkingTimeSec'];
			}

			PBXManager_VCS_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[VCS] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if ($state == 'cdr') {
			$eventData = $data;
			$eventData['call_id'] = $callId;
			$eventData['state'] = $state;
		}
		else {
			$eventData = [
				'call_id' => $callId,
				'direction' => $direction,
				'state' => $state,
				'customer_number' => $customerPhoneNumber,
				'agent_ext_number' => $agentExtNumber,
				'hotline' => $hotline,
			];
		}
		
		PBXManager_VCS_Connector::handleCallEvent($eventData);
	}
}