<?php

/*
	VoIP24H_Connector
	Author: Hieu Nguyen
	Date: 2019-04-18
	Purpose: to handle communication with VoIP24H Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_VoIP24H_Connector extends PBXManager_Base_Connector {

	public $hasExternalReport = true;   // Indicate that there is an external report to show the call history
	protected static $SETTINGS_REQUIRED_PARAMETERS = [
		'webservice_url' => 'text', 
		'api_key' => 'password',
		'secret_key' => 'password',
		'auto_call_service_url' => 'text',
		'auto_call_api_key' => 'password',
	];
	protected $webserviceUrl;
	protected $apiKey;
	protected $secretKey;
	protected $autoCallServiceUrl;
	protected $autoCallApiKey;

	// Return the connector name
	public function getGatewayName() {
		return 'VoIP24H';
	}

	// Set server parameters for this provider
	public function setServerParameters($serverModel) {
		$this->webserviceUrl = $serverModel->get('webservice_url');
		$this->apiKey = $serverModel->get('api_key');
		$this->secretKey = $serverModel->get('secret_key');
		$this->autoCallServiceUrl = $serverModel->get('auto_call_service_url');
		$this->autoCallApiKey = $serverModel->get('auto_call_api_key');
	}

	// Return SIP credential of current user
	function getSIPCredentials() {
		global $current_user;
		$userPreferences = Users_Preferences_Model::loadPreferences($current_user->id, 'callcenter_config', true) ?? [];

		return [
			'server_ip' => $userPreferences['sip_server_ip'],
			'extension' => $current_user->phone_crm_extension,
			'password' => $userPreferences['sip_ext_password'],
		];
	}

	// Make a phone call
	function makeCall($receiverNumber, $parentId) {
		$user = Users_Record_Model::getCurrentUserModel();
		$serviceUrl = $this->getServiceUrl('calling');
		$headers = [];

		$params = [
			'voip' => $this->apiKey,
			'secret' => $this->secretKey,
			'sip' => $user->phone_crm_extension,
			'phone' => $receiverNumber
		];

		// This provider needs the params in url
		$serviceUrl .= '?'. http_build_query($params);

		$client = $this->getRestClient($serviceUrl, $headers);
		$response = $this->callRestApi($client, 'GET', $params);
		CallCenterUtils::saveDebugLog('[VoIP24H] Make call request: '. $serviceUrl, $headers, $params, $response);

		if ($response) {
			if ($response->call_status == 'Success') {
				return ['success' => true];
			}

			if ($response->call_status == 'Offline') {
				return ['success' => false, 'message' => vtranslate('LBL_MAKE_CALL_DEVICE_OFFLINE_ERROR_MSG', 'PBXManager')];
			}
		}

		return ['success' => false];
	}

	// Make an auto call
	function makeAutoCall($receiverNumber, $textToCall, $parentId) {
		$serviceUrl = $this->autoCallServiceUrl;
		$headers = ['Content-Type: application/x-www-form-urlencoded'];
		
		$params = [
			'voip' => $this->autoCallApiKey,
			'phone' => $receiverNumber,
			'data_speech' => $textToCall,
			'LanguageCode' => 'vi-VN',  // TODO: support selecting language dynamically later
			'WaitTime' => 60,   // Wait 60 secs for the customer to answer the call
			'MaxRetries' => 2,  // Retry 2 more times if the first call is failed
			'RetryTime' => 600  // Retry after 10 mins
		];

		$client = $this->getRestClient($serviceUrl, $headers);
		$response = $this->callRestApi($client, 'POST', http_build_query($params), false);
		CallCenterUtils::saveDebugLog('[VOIP24H] Make auto call request: '. $serviceUrl, null, $params, $response);

		if ($response && $response->msg == 'Success') {
			return ['success' => true, 'call_id' => $response->result->espeakid];
		}

		return ['success' => false];
	}

	// Get the right call direction from the request
	static function getCallDirection($data) {
		return $data['type'];
	}

	// Fetch history report from vendor system
	function getHistoryReport($headers, $params) {
		$serviceUrl = $this->getServiceUrl('dial/search');
		$serviceUrl .= '?'. http_build_query($params);

		$client = $this->getRestClient($serviceUrl, $headers);
		$response = $this->callRestApi($client, 'GET', $params);

		return $response;
	}

	// Return recording data only to prevent user to access file url out side the system
	function getRecordingData($callRecordModel) {
		$callId = $callRecordModel->get('sourceuuid');

		// In INBOUND call, we have generated unique call id using format '<callid>-<ext>', thefore we have to remove the ext part here to get the actual call id
		if ($callRecordModel->get('direction') == 'inbound') {
			$callId = explode('-', $callId)[0];	// Get only actual call id part
		}

		$serviceUrl = $this->getServiceUrl('play');
		$headers = [];

		$params = [
			'voip' => $this->apiKey,
			'callid' => $callId
		];

		// This provider needs the params in url
		$serviceUrl .= '?'. http_build_query($params);

		$client = $this->getRestClient($serviceUrl, $headers);
		$response = $this->callRestApi($client, 'GET', $params);
		CallCenterUtils::saveDebugLog('[VoIP24H] Get Recording request: '. $serviceUrl, $headers, $params, $response);

		if ($response && $response->message && $response->message->message == 'successful') {
			$recordingUrl = $response->result->mp3;
			$recordingData = getRemoteFile($recordingUrl);
		}

		return $recordingData;
	}

	// Return the agent ext number based on the incomming customer number
	static function handleSkillBasedRouting($customerNumber, $hotline) {
		if (strpos($customerNumber, '84') === 0) {
			$customerNumber = '0' . substr($customerNumber, 2);   // Replace prefix 84 with 0
		}

		$extNumber = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);
		return $extNumber;
	}

	// Modified by Phu Vo on 2019.07.17 to send notification and email
	static function handleMissedCallEvent($data) {
		$customerPhoneNumber = $data['src'];
		$agentExtNumber = $data['dst'];
		$callId = $data['linkedid'];
		$callTime = $data['calldate'];
		$hotlineNumber = $data['did'];
		
		try {
			$agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
			$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber);

			// Retrieve missed call alert users on config
			$userIds = CallCenterUtils::getMissedCallAlertUsers($customer);

			// Save Missed Call PBXLog
			$params = [
				'starttime' => $callTime,
				'sourceuuid' => $callId,
				'gateway' => 'VoIP24H',
				'customer' => $customer['id'],
				'customernumber' => $customerPhoneNumber,
				'customertype' => $customer['type'],
				'hotline' => $hotlineNumber,
				'totalduration' => $data['duration'],
			];
			
			PBXManager_Data_Model::saveMissedCall($params, $customer, $agent, $userIds);

			// Save Missed Call Events
			CallCenterUtils::saveMissedCallLog($customer, $customerPhoneNumber, $callId, $callTime, $userIds);

			// Send push notification
			CallCenterUtils::sendMissedCallNotification($customer, $customerPhoneNumber, $userIds);

			// Send alert email
			CallCenterUtils::sendMissedAlertEmail($customer, $customerPhoneNumber, $hotlineNumber, $callTime, $userIds);
		}
		catch (Exception $e) {
			CallCenterUtils::saveDebugLog('[PBXManager_VoIP24H_Connector::handleMissedCallEvent] Error: '. $e->getMessage(), null, $e->getTrace());
		}
	}


	// Handle auto call event
	static function handleAutoCallEvent($data) {
		require_once('modules/PBXManager/workflow/VTAutoCallTask.php');
		if (empty($data['cdr'])) return;
		
		$callId = $data['espeakid'];
		$callStatus = $data['cdr']['disposition'] == 'ANSWERED' ? 'ANSWERED' : 'BUSY';
		$answerDuration = $data['cdr']['duration'];
		$responseKey = '';
		
		if (!empty($data['dtmf'])) {
			$responseKey = $data['dtmf']['digit'];
			VTAutoCallTask::handleResponse($callId, $responseKey);
		}

		PBXManager_Data_Model::updateAutoCallStatus($callId, $callStatus, $answerDuration, $responseKey);
	}

	static function isExists($callId, $status) {
		$statusMapping = [
			'Ring' => 'ringing',
			'Up' => 'answered',
			'Hangup' => 'hangup',
			'Cdr' => 'completed',
		];

		return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
	}

	// Handle all call events from webhook
	static function handleCallEvent($data) {
		// New call
		if ($data['state'] == 'Ring') {
			$customerPhoneNumber = $data['phone'];

			// Get prefetch data from global
			$agent = $GLOBALS['agent'];
			$customer = $GLOBALS['customer'];

			$params = [
				'direction' => $data['direction'],
				'callstatus' => 'ringing',
				'starttime' => date('Y-m-d H:i:s'),
				'sourceuuid' => $data['callid'],
				'gateway' => 'VoIP24H',
				'user' => $agent['id'],
				'customer' => $customer['id'],
				'customernumber' => $customerPhoneNumber,
				'customertype' => $customer['type'],
				'hotline' => '',    // No data
				'assigned_user_id' => $agent['id'],
			];
			
			PBXManager_Data_Model::handleStartupCall($params);
		}

		// Call answered
		if ($data['state'] == 'Up') {
			$params = [
				'callstatus' => 'answered'
			];

			PBXManager_Data_Model::handleHangupCall($data['callid'], $params);
		}

		// Call completed
		if ($data['state'] == 'Hangup' || $data['state'] == 'Cdr') {
			$params['callstatus'] = 'hangup';

			if ($data['state'] == 'Cdr') {
				$params['callstatus'] = 'completed';
				$params['endtime'] = $data['cdr']['endtime'];
				$params['totalduration'] = $data['cdr']['duration'];
				$params['billduration'] = $data['cdr']['billsec'];

				// Recording file is only available when connected time > 3 seconds
				if ($data['cdr']['billsec'] > 3) {
					$params['recordingurl'] = $data['callid'];  // Recording url must not empty to be able to play recording
				}
			}

			PBXManager_Data_Model::handleHangupCall($data['callid'], $params);
		}
	}
}