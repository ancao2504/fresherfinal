<?php

/*
	Webhook GrandStreamConnector
	Author: Hieu Nguyen
	Date: 2019-05-15
	Purpose: to handle request from GrandStream AMI Bridge Proxy and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class GrandStreamConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		global $callCenterConfig;
		CallCenterUtils::checkConfig();
		
		// Get data from webhook
		$request = CallCenterUtils::getRequest();
		$rawRequest = $request->getAllPurified();

		CallCenterUtils::saveLog('[GrandStream] Webhook data', null, $rawRequest);

		if (count($rawRequest) <= 2) {
			echo 'Listening!';
			exit;
		}

		// Check RING event that is not actually of the call
		if ($rawRequest['Event'] == 'Newstate' && $rawRequest['ChannelStateDesc'] == 'Ring') {
			CallCenterUtils::saveLog('[GrandStream] Ignore this RING event');
			exit;
		}

		// Check CDR event that is not actually of the call
		if ($rawRequest['Event'] == 'Cdr' && empty($rawRequest['DestinationChannel'])) {
			CallCenterUtils::saveLog('[GrandStream] Ignore this CDR event');
			exit;
		}

		// Check other events
		$handle = true;
		$amiVersionNum = str_replace('.', '', $callCenterConfig['ami_version']);

		// Older AMI version
		if (empty($amiVersionNum) || intval($amiVersionNum) <= 270) {
			// Ignore non-sip signals
			if (strpos($rawRequest['Channel'], 'SIP') === false && strpos($rawRequest['DestinationChannel'], 'SIP') === false) {
				$handle = false;
				
				// Special case for Up signal in outbound call from click-to-call to get customer phone number (it's empty in SIP signal)
				if ($rawRequest['Event'] == 'Newstate' && strpos($rawRequest['Channel'], 'DAHDI') !== false && $rawRequest['ChannelStateDesc'] == 'Up' && $rawRequest['Application'] == 'AppDial') {
					$handle = true;
				}
			}
			// Ignore sip UP signal in outbound call from click-to-call as it contains empty customer phone number
			else if ($rawRequest['Event'] == 'Newstate' && strpos($rawRequest['Channel'], 'SIP') !== false && $rawRequest['ChannelStateDesc'] == 'Up' && $rawRequest['Application'] == 'AppDial2') {
				$handle = false;
			}
		}
		// Newer AMI version
		else {
			// These signals is from API Click2Call process which is duplicated with the TRUNK process so we can skip them
			if ($rawRequest['Application'] == 'AppDial2') {
				$handle = false;
			}

			// Check UP signal
			if ($rawRequest['Event'] == 'Newstate' && $rawRequest['ChannelStateDesc'] == 'Up') {
				// Inbound call has caller number in ConnectedLineNum attribute and callee number at Exten attribute so we should handle it
				if (strpos($rawRequest['Channel'], 'PJSIP/' . $rawRequest['CallerIDNum']) !== false) {
					$handle = true;
				}
				// Outbound call
				else {
					// Some duplicated UP signals has no caller and callee number so we should skip them
					if (empty($rawRequest['Reg_callernum'])) {
						$handle = false;
					}
				}
			}
		}

		if (!$handle) {
			CallCenterUtils::saveLog('[GrandStream] Ignore this event');
			exit;
		}

		$data = PBXManager_GrandStream_Connector::processEventData($rawRequest);

		// Ignore RINGING and ANSWERED signal before the call actually started
		if ($data['direction'] == 'outbound' && in_array($data['state'], ['Ringing', 'Answered']) && strpos($rawRequest['Context'], 'ext-did') === false) {
			CallCenterUtils::saveLog('[GrandStream] Ignore this outbound event');
			exit;
		}

		// Ignore the duplicated signal when the call center retry a timeout request
		if (PBXManager_GrandStream_Connector::isExists($data['callid'], $data['state'])) {
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

			if ($data['state'] == 'Ringing' || ($data['direction'] == 'outbound' && $data['state'] == 'Up')) {
				if ($data['state'] == 'Ringing') {
					$msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND
				}

				$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['callee'], $data['direction']);
				$customer = [];
				
				// Customer number will always be avaiable in RINGING signal of inbound call
				if ($data['direction'] == 'inbound' && $data['state'] == 'Ringing') {
					$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
				}
				// But in some AMI version, there is no customer number in the RINGING signal of outbound call so we have to get it from the outbound cache (Click-To-Call only)
				else if ($data['direction'] == 'outbound' && $data['state'] == 'Ringing') {
					if (empty($customerPhoneNumber)) {
						$outboundCache = PBXManager_Logic_Helper::getOutboundCache($agentExtNumber);

						if (!empty($outboundCache)) {
							$customerPhoneNumber = $data['callee'] = $outboundCache['customer_phone_number'];   // Now we have the customer number :)
						}
					}

					$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
				}
				// Then when customer number is available in UP signal, we will fetch customer info to update to the call log
				else if ($data['direction'] == 'outbound' && $data['state'] == 'Up') {
					$customerFromCall = PBXManager_Data_Model::getCustomerFromCall($callId);

					if (empty($customerFromCall)) {
						$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
					}
				}

				if (!empty($customerPhoneNumber)) {
					CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);
				}

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

			PBXManager_GrandStream_Connector::forwardToCallCenterBridge($msg);
			CallCenterUtils::saveDebugLog('[GrandStream] Data sent to call popup for ' . $agentExtNumber, null, $msg);
		}

		// Save call history
		if (($data['state'] == 'Ringing' && !empty($receiverId)) || $data['state'] != 'Ringing') {
			PBXManager_GrandStream_Connector::handleCallEvent($data);
		}
	}
}