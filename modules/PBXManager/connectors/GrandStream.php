<?php

/*
	GrandStream_Connector
	Author: Hieu Nguyen
	Date: 2019-04-18
	Purpose: to handle communication with GrandStream Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_GrandStream_Connector extends PBXManager_Base_Connector {

	public $isPhysicalDevice = true;    // Indicate that this is a physical device, not cloud call center
	public $hasExternalReport = true;   // Indicate that there is an external report to show the call history
	protected static $SETTINGS_REQUIRED_PARAMETERS = [
		'ami_server_ip' => 'text',
		'ami_port' => 'text',
		'ami_username' => 'text',
		'ami_password' => 'password',
		'cdrapi_server_url' => 'text',
		'cdrapi_username' => 'text',
		'cdrapi_password' => 'password',
	];
	protected $amiServerIP;
	protected $amiPort;
	protected $amiUsername;
	protected $amiPassword;
	protected $cdrApiServerUrl;
	protected $cdrApiUsername;
	protected $cdrApiPassword;

	// Return the connector name
	public function getGatewayName() {
		return 'GrandStream';
	}

	// Set server parameters for this provider
	public function setServerParameters($serverModel) {
		$this->amiServerIP = $serverModel->get('ami_server_ip');
		$this->amiPort = $serverModel->get('ami_port');
		$this->amiUsername = $serverModel->get('ami_username');
		$this->amiPassword = $serverModel->get('ami_password');
		$this->cdrApiServerUrl = $serverModel->get('cdrapi_server_url');
		$this->cdrApiUsername = $serverModel->get('cdrapi_username');
		$this->cdrApiPassword = $serverModel->get('cdrapi_password');
	}

	// Make a phone call
	function makeCall($receiverNumber, $parentId) {
		$user = Users_Record_Model::getCurrentUserModel();
		$agentExt = $user->get('phone_crm_extension');
		
		$socket = fsockopen($this->amiServerIP, $this->amiPort, $errCode, $errMsg, 10);
		fputs($socket, "Action: Login\r\n");
		fputs($socket, "UserName: {$this->amiUsername}\r\n");
		fputs($socket, "Secret: {$this->amiPassword}\r\n\r\n");

		$response = fgets($socket, 128);

		fputs($socket, "Action: Originate\r\n" );
		fputs($socket, "Channel: PJSIP/$agentExt\r\n" );
		fputs($socket, "Context: from-internal\r\n" );
		fputs($socket, "Exten: {$receiverNumber}\r\n" );
		fputs($socket, "Priority: 1\r\n" );
		fputs($socket, "Async: yes\r\n" );
		fputs($socket, "Timeout: 60000\r\n\r\n" );
		fputs($socket, "Action: Logoff\r\n\r\n");

		while (!feof($socket)) {
			$response .= fread($socket, 8192);
		}

		fclose($socket);
		CallCenterUtils::saveDebugLog('[GrandStream] Make call request: ', null, null, $response);

		if (strpos($response, 'Originate successfully queued') > 0) {
			return ['success' => true];
		}

		return ['success' => false];
	}

	// Get the right call direction from the request
	function getCallDirection($data) {
		if ($data['state'] == 'Hangup') {
			return PBXManager_Data_Model::getCallDirection($data['callid']);
		}

		return $data['type'];
	}

	// Make request to CDR server
	function makeRequest($url, $responseAsJSON = true) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		curl_setopt($curl, CURLOPT_USERPWD, "{$this->cdrApiUsername}:{$this->cdrApiPassword}");
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			return false;
		}

		if ($responseAsJSON) {
			return json_decode($response);
		}

		return $response;
	}

	// Fetch history report from vendor system
	function getHistoryReport($headers, $params) {
		$serviceUrl = $this->cdrApiServerUrl .'cdrapi';
		$serviceUrl .= '?'. http_build_query($params);
		$response = $this->makeRequest($serviceUrl);

		return $response;
	}

	// Return recording data only to prevent user to access file url out side the system
	function getRecordingDataByFileName($fileName) {
		$serviceUrl = $this->cdrApiServerUrl .'recapi?filename=' . $fileName;
		$recordingData = $this->makeRequest($serviceUrl, false);

		return $recordingData;
	}

	static function isExists($callId, $status) {
		$statusMapping = [
			'Ringing' => 'ringing',
			'Up' => 'answered',
			'Hangup' => 'hangup',
			'CDR' => 'completed',
		];

		return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
	}

	// Process event data from GrandStream call center
	static function processEventData($eventData) {
		global $callCenterConfig;
		$amiVersionNum = str_replace('.', '', $callCenterConfig['ami_version']);

		if (empty($amiVersionNum) || intval($amiVersionNum) <= 270) {
			return self::processEventData1($eventData);
		}
		else {
			return self::processEventData2($eventData);
		}
	}

	// For legacy device (AMI version <= 2.7.0)
	static function processEventData1($eventData) {
		$data = [
			'callid' => $eventData['Linkedid'],
			'direction' => 'inbound',
			'state' => $eventData['ChannelStateDesc'],
		];

		if ($eventData['ChannelStateDesc'] == 'Ringing') {
			if (
				$eventData['Application'] == 'AppQueue' ||	// Old format
				($eventData['Application'] == 'AppDial' && empty($eventData['Reg_calleenum']))	// New format
			) {
				$data['direction'] = 'inbound';
			}
			else {
				$data['direction'] = 'outbound';
			}
		}

		if ($eventData['ChannelStateDesc'] == 'Up' && strpos($eventData['Context'], 'ext-did') !== false) {	// New format of outbound call
			$data['direction'] = 'outbound';
		}

		if ($data['direction'] == 'inbound' && $eventData['ChannelStateDesc'] == 'Ringing') {
			$data['state'] = 'Ringing';
			$data['caller'] = $eventData['ConnectedLineNum'];
			$data['callee'] = $eventData['CallerIDNum'];
		}
		else if ($data['direction'] == 'outbound' && $eventData['ChannelStateDesc'] == 'Ringing') {
			$data['state'] = 'Ringing';
			$newFormat = false;

			if (!empty($eventData['CallerIDNum']) && $eventData['CallerIDNum'] == $eventData['ConnectedLineNum']) {
				$newFormat = true;
			}

			if ($newFormat) {
				$data['caller'] = $eventData['Reg_callernum'];
				$data['callee'] = $eventData['Reg_calleenum'];
			}
			else {
				$data['caller'] = $eventData['CallerIDNum'];
				$data['callee'] = '';	// Will be empty in old AMI event format
			}
		}

		if ($eventData['Event'] == 'Hangup') {
			$data['state'] = 'Hangup';
		}

		if ($eventData['Event'] == 'Cdr') {
			$data['callid'] = $eventData['UniqueID'];
			$data['state'] = 'CDR';
			$data['duration'] = $eventData['Duration'];
			$data['billsecs'] = $eventData['BillableSeconds'];
		}

		// Outbound call in old AMI event format has no customer number in the RINGING event, fetch that in UP event
		if ($data['direction'] == 'outbound' && $data['state'] == 'Up') {
			$newFormat = false;

			if (!empty($eventData['Reg_callernum']) && !empty($eventData['Reg_calleenum'])) {
				$newFormat = true;
			}

			if (!$newFormat) {
				// Click2call
				if ($eventData['Application'] == 'AppDial') {
					$data['caller'] = $eventData['ConnectedLineNum'];	// Reverse info
					$data['callee'] = $eventData['CallerIDNum'];		// Reverse info
				}
				// Softphone
				else {
					$data['caller'] = $eventData['CallerIDNum'];
					$data['callee'] = $eventData['ConnectedLineNum'];
				}
			}
		}

		return $data;
	}

	// For newer device (AMI version > 2.7.0)
	static function processEventData2($eventData) {
		$data = [
			'callid' => $eventData['Linkedid'],
			'direction' => 'inbound',
			'state' => $eventData['ChannelStateDesc'],
		];

		// Only inbound call has CallerIDNum inside Channel at RINGING event
		if ($eventData['ChannelStateDesc'] == 'Ringing') {
			if (strpos($eventData['Channel'], 'PJSIP/' . $eventData['CallerIDNum']) !== false) {
				$data['direction'] = 'inbound';
			}
			else {
				$data['direction'] = 'outbound';
			}
		}

		if ($data['direction'] == 'inbound' && $eventData['ChannelStateDesc'] == 'Ringing') {
			$data['state'] = 'Ringing';
			$data['caller'] = $eventData['ConnectedLineNum'];
			$data['callee'] = $eventData['Exten'];
		}
		else if ($data['direction'] == 'outbound' && in_array($eventData['ChannelStateDesc'], ['Ring', 'Ringing'])) {
			$data['state'] = 'Ringing';
			$data['caller'] = $eventData['Reg_callernum'];
			$data['callee'] = $eventData['Reg_calleenum'];
		}

		if ($eventData['Event'] == 'Hangup') {
			$data['state'] = 'Hangup';
		}

		if ($eventData['Event'] == 'Cdr') {
			$data['callid'] = $eventData['UniqueID'];
			$data['state'] = 'CDR';
			$data['duration'] = $eventData['Duration'];
			$data['billsecs'] = $eventData['BillableSeconds'];
		}

		return $data;
	}

	// Handle all call events from webhook
	static function handleCallEvent($data) {
		// New call
		if ($data['state'] == 'Ringing') {
			$customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['callee'], $data['direction']);

			// Get prefetch data from global
			$agent = $GLOBALS['agent'];
			$customer = $GLOBALS['customer'];   // In some AMI version, this info will be empty in outbound call

			$params = [
				'direction' => $data['direction'],
				'callstatus' => 'ringing',
				'starttime' => date('Y-m-d H:i:s'),
				'sourceuuid' => $data['callid'],
				'gateway' => 'GrandStream',
				'user' => $agent['id'],
				'customer' => $customer['id'],
				'customernumber' => $customerPhoneNumber,
				'customertype' => $customer['type'],
				'hotline' => '',
				'assigned_user_id' => $agent['id'],
			];
			
			PBXManager_Data_Model::handleStartupCall($params);
		}

		// Call answered
		if ($data['state'] == 'Up') {
			$params = [
				'callstatus' => 'answered'
			];

			if ($data['direction'] == 'outbound') {
				// Update missing info for the call
				$customerPhoneNumber = $data['callee'];
				$customer = $GLOBALS['customer'];   // Get prefetch data from global

				if (!empty($customer)) {
					$params['customer'] = $customer['id'];
					$params['customernumber'] = $customerPhoneNumber;
					$params['customertype'] = $customer['type'];
				}

				PBXManager_Data_Model::updateCall($data['callid'], $params);
			}
			else {
				// Update call status
				PBXManager_Data_Model::updateCallStatus($data['callid'], $params);
			}
		}

		// Call hangup
		if ($data['state'] == 'Hangup') {
			$params = [
				'callstatus' => 'hangup'
			];

			PBXManager_Data_Model::updateCallStatus($data['callid'], $params);
		}

		// Call ended
		if ($data['state'] == 'CDR') {
			$params = [
				'callstatus' => 'completed',
				'endtime' => date('Y-m-d H:i:s'),
				'totalduration' => $data['duration'],
				'billduration' => $data['billsecs'],
				'recordingurl' => '',   // No data
			];

			PBXManager_Data_Model::handleHangupCall($data['callid'], $params);
		}
	}
}