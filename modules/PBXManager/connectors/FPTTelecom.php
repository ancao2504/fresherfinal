<?php

/*
	FPTTelecom_Connector
	Author: Hieu Nguyen
	Date: 2021-02-24
	Purpose: to handle communication with FPT Telecom Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_FPTTelecom_Connector extends PBXManager_Base_Connector {

	public $hasExternalReport = true;           // Indicate that there is an external report to show the call history
	public $hasDirectPlayRecordingApi = false;  // Indicate that this provider provides an api to play recording directly from call log
	protected static $SETTINGS_REQUIRED_PARAMETERS = [
		'webservice_url' => 'text',
		'file_server_url' => 'text',
		'domain' => 'text',
		'username' => 'text',
		'password' => 'password',
	];
	protected $webserviceUrl;
	protected $fileServerUrl;
	protected $domain;
	protected $username;
	protected $password;

	// Return the connector name
	public function getGatewayName() {
		return 'FPTTelecom';
	}

	// Set server parameters for this provider
	public function setServerParameters($serverModel) {
		$this->webserviceUrl = $serverModel->get('webservice_url');
		$this->fileServerUrl = $serverModel->get('file_server_url');
		$this->domain = $serverModel->get('domain');
		$this->username = $serverModel->get('username');
		$this->password = $serverModel->get('password');
	}

	// Extract only phone number from the composite string
	static function cleanupPhoneNumbers(array &$webhookData) {
		preg_match('!\d+!', $webhookData['caller'], $caller);
		preg_match('!\d+!', $webhookData['callee'], $callee);
		preg_match('!\d+!', $webhookData['call_target'], $callTarget);

		$webhookData['caller_original'] = $webhookData['caller'];
		$webhookData['caller'] = $caller[0];
		$webhookData['callee_original'] = $webhookData['callee'];
		$webhookData['callee'] = $callee[0];
		$webhookData['call_target_original'] = $webhookData['call_target'];
		$webhookData['call_target'] = $callTarget[0];
	}

	// Make a phone call
	function makeCall($receiverNumber, $parentId) {
		$user = Users_Record_Model::getCurrentUserModel();
		$userConfig = Users_Preferences_Model::loadPreferences($user->getId(), 'callcenter_config', true) ?? [];
		$agentWebAccessPassword = $userConfig['web_access_password'];

		$params = [
			'src' => $user->phone_crm_extension,
			'to' => $receiverNumber,
			'domain' => $this->domain,
			'extension' => $user->phone_crm_extension,
			'auth' => $agentWebAccessPassword
		];

		$serviceUrl = $this->getServiceUrl('extensions/call');
		$client = $this->getRestClient($serviceUrl);
		$response = $this->callRestApi($client, 'POST', $params);
		CallCenterUtils::saveDebugLog('[FPTTelecom] Make call request: '. $serviceUrl, null, $params, $response);

		if ($response !== false && empty($response)) {
			return ['success' => true]; // Empty response means success ???!!!!
		}

		return ['success' => false, 'message' => "{$response->err_code} - {$response->msg}"];
	}

	// Get tenant access token
	function getAccessToken() {
		$params = [
			'name' => $this->username,
			'password' => $this->password
		];

		$serviceUrl = $this->getServiceUrl('account/credentials/verify');
		$client = $this->getRestClient($serviceUrl);
		$response = $this->callRestApi($client, 'POST', $params);

		if ($response && !empty($response->access_token)) {
			return $response->access_token;
		}

		return null;
	}

	// Fetch history report from vendor system
	function getHistoryReport(array $headers = [], array $params) {
		$serviceUrl = $this->getServiceUrl('recordfiles/extension/list');
		$serviceUrl .= '?' . http_build_query($params);
		$headers[] = 'access_token: ' . $this->getAccessToken();
		$client = $this->getRestClient($serviceUrl, $headers);
		$response = $this->callRestApi($client, 'GET', null, false, true);
		return $response;
	}

	// Return recording data only to prevent user to access file url out side the system
	function getRecordingDataByFilePath($filePath) {
		$fileUrl = $this->fileServerUrl . $filePath;
		$recordingData = getRemoteFile($fileUrl);
		return $recordingData;
	}

	// Return the agent ext number based on the incomming customer number
	static function handleSkillBasedRouting($customerNumber, $hotline) {
		return 'Not supported yet!';
	}

	static function isExists($callId, $status) {
		$statusMapping = [
			'target_ringing' => 'ringing',
			'call_established' => 'answered',
			'call_ended' => 'hangup',
			'cdr' => 'completed',
		];

		return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
	}

	// Handle all call events from webhook
	static function handleCallEvent($data) {
		$callId = $data['call_id'];

		// New call
		if ($data['event_type'] == 'target_ringing') {
			$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['callee'], $data['direction']);

			// Get prefetch data from global
			$agent = $GLOBALS['agent'];
			$customer = $GLOBALS['customer'];

			$params = [
				'direction' => $data['direction'],
				'callstatus' => 'ringing',
				'starttime' => date('Y-m-d H:i:s', $data['time']),
				'sourceuuid' => $callId,
				'gateway' => 'FPTTelecom',
				'user' => $agent['id'],
				'customer' => $customer['id'],
				'customernumber' => $customerPhoneNumber,
				'customertype' => $customer['type'],
				'hotline' => '',    // No data
				'assigned_user_id' => $agent['id'],
			];
			
			PBXManager_Data_Model::handleStartupCall($params);
		}

		// Call update
		if ($data['event_type'] == 'call_established') {
			$params = [
				'callstatus' => 'answered'
			];

			PBXManager_Data_Model::updateCallStatus($callId, $params);
		}

		// Call ended
		if ($data['event_type'] == 'call_ended' || $data['event_type'] == 'cdr') {
			$params = [
				'callstatus' => 'hangup',
			];

			if ($data['event_type'] == 'cdr') {
				$params['callstatus'] = 'completed';
				$params['endtime'] = date('Y-m-d H:i:s', $data['ended_time']);
				$params['totalduration'] = $data['talk_duration'];
				$params['billduration'] = $data['talk_duration'];
				$params['recordingurl'] = '';   // No data
			}

			PBXManager_Data_Model::handleHangupCall($callId, $params);
		}
	}
}