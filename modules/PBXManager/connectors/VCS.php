<?php

/*
	VCS_Connector
	Author: Hieu Nguyen
	Date: 2021-10-04
	Purpose: to handle communication with VCS Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_VCS_Connector extends PBXManager_Base_Connector {

	protected static $SETTINGS_REQUIRED_PARAMETERS = [
		'webservice_url' => 'text',
		'api_key' => 'password',
	];
	protected $webserviceUrl;
	protected $apiKey;

	// Return the connector name
	public function getGatewayName() {
		return 'VCS';
	}

	// Set server parameters for this provider
	public function setServerParameters($serverModel) {
		$this->webserviceUrl = $serverModel->get('webservice_url');
		$this->apiKey = $serverModel->get('api_key');
	}

	// Return token for web phone
	function getWebPhoneToken() {
		return '-'; // Work arround to bypass the initWebPhone() function in CallCenterClient as this provider provide a web client with full control api but has no WebRTC integrated
	}

	// Return recording data only to prevent user to access file url out side the system
	function getRecordingData($callRecordModel) {
		$recordingUrl = $callRecordModel->get('recordingurl');
		$recordingData = getRemoteFile($recordingUrl);
		
		return $recordingData;
	}

	// Return the agent ext number based on the incomming customer number
	static function handleSkillBasedRouting($customerNumber, $hotline) {
		return 'Not supported yet!';
	}

	static function isExists($callId, $status) {
		$statusMapping = [
			'ringing' => 'ringing',
			'answered' => 'answered',
			'hangup' => 'hangup',
			'cdr' => 'completed',
		];

		return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
	}

	// Handle all call events from webhook
	static function handleCallEvent($data) {
		$callId = $data['call_id'];

		// New call
		if ($data['state'] == 'ringing') {
			// Get prefetch data from global
			$agent = $GLOBALS['agent'];
			$customer = $GLOBALS['customer'];

			$params = [
				'direction' => strtolower($data['direction']),
				'callstatus' => 'ringing',
				'starttime' => date('Y-m-d H:i:s'),
				'sourceuuid' => $callId,
				'gateway' => 'VCS',
				'user' => $agent['id'],
				'customer' => $customer['id'],
				'customernumber' => $data['customer_number'],
				'customertype' => $customer['type'],
				'hotline' => $data['hotline'],
				'assigned_user_id' => $agent['id'],
			];
			
			PBXManager_Data_Model::handleStartupCall($params);
		}

		// Call update
		if ($data['state'] == 'answered') {
			$params = [
				'callstatus' => 'answered'
			];

			PBXManager_Data_Model::updateCallStatus($callId, $params);
		}

		// Call ended
		if ($data['state'] == 'hangup') {
			$params = [
				'callstatus' => 'hangup',
			];

			PBXManager_Data_Model::handleHangupCall($callId, $params);
		}

		if ($data['state'] == 'cdr') {
			// When this call has event from WebClient
			if (self::isExists($callId, 'hangup')) {
				// Update call status as completed
				$params = [
					'callstatus' => 'completed',
					'starttime' => $data['StartTimeISO'],
					'endtime' => $data['EndTimeISO'],
					'totalduration' => $data['RingingTimeSec'] + $data['TalkingTimeSec'],
					'billduration' => $data['TalkingTimeSec'],
					'recordingurl' => $data['Recording'],
				];

				PBXManager_Data_Model::updateCall($callId, $params);
			}
			// When this call is handled outside agent PC or when CRM is not logged in yet
			else {
				// Save CDR log only
				$callDirection = $data['Direction'] == 'Outcoming' ? 'outbound' : 'inbound';
				$agent = PBXManager_Data_Model::findAgentByExtNumber($data['Extension']);
				if (empty($agent)) return;

				$customer = PBXManager_Data_Model::findCustomerByPhoneNumber($data['NumberPhone'], $callDirection == 'outbound', $data['Extension'], true);

				$params = [
					'direction' => $callDirection,
					'callstatus' => 'completed',
					'starttime' => $data['StartTimeISO'],
					'endtime' => $data['EndTimeISO'],
					'sourceuuid' => $callId,
					'gateway' => 'VCS',
					'user' => $agent['id'],
					'customer' => !empty($customer) ? $customer['id'] : '',
					'customernumber' => $data['NumberPhone'],
					'customertype' => !empty($customer) ? $customer['type'] : '',
					'hotline' => $data['hotline'],
					'assigned_user_id' => $agent['id'],
					'totalduration' => $data['RingingTimeSec'] + $data['TalkingTimeSec'],
					'billduration' => $data['TalkingTimeSec'],
					'recordingurl' => $data['Recording'],
				];
				
				PBXManager_Data_Model::handleStartupCall($params);
			}
		}
	}
}