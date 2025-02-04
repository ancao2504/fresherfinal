<?php
	/*
	*	ESMS_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-29
	*	Purpose: to provide helper class for sending SMS with eSMS REST APIs
	*/

	require_once('modules/SMSNotifier/BaseProvider.php');

	class SMSNotifier_ESMS_Provider extends SMSNotifier_Base_Provider {

		private $statusMapping = [
			1 => 'Waiting for approving',
			2 => 'Waiting for sending',
			3 => 'Sending',
			4 => 'Rejected',
			5 => 'Delivered',
			6 => 'Deleted',
		];

		function __construct() {
			$this->serviceURI = 'http://rest.esms.vn/MainService.svc/json';
			$this->requiredParams = array(
				array('name' => 'api_key', 'label' => 'API Key', 'type' => 'text'),
				array('name' => 'api_secret', 'label' => 'API Secret', 'type' => 'password'),
				array('name' => 'brandname', 'label' => 'Brandname', 'type' => 'text'),
			);
		}

		public function getName() {
			return 'ESMS';
		}

		public function getServiceURL($type = false) {
			switch (strtoupper($type)) {
				case self::SERVICE_SEND: return $this->serviceURI . '/SendMultipleMessage_V4_post_json/';
				case self::SERVICE_QUERY: return $this->serviceURI . '/GetSendStatus/';
				default: return false;
			}
		}

		public function prepareParameters($phone, $content) {
			$params = array(
				'ApiKey' => $this->parameters['api_key'], 
				'SecretKey' => $this->parameters['api_secret'],
				'SmsType' => 2,
				'Brandname' => $this->parameters['brandname'],
				'Phone' => $phone,
				'Content' => $content,
				'CallbackUrl' => $this->getCallbackUrl()
			);

			return $params;
		}

		public function send($message, $toNumbers) {
			$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
			$results = array();

			foreach ($toNumbers as $number => $customerId) {
				$number = $this->correctPhoneNumber($number);
				$populatedMsg = populateTemplateWithRecordData($message, $customerId);   // Replace variables
				$populatedMsg = unUnicode($populatedMsg); // SMS message does not support unicode character

				$params = $this->prepareParameters($number, $populatedMsg);
				$client = $this->getRestClient($serviceURL);
				$response = $this->callRestAPI($client, $params);

				$result = [
					'to' => $number,
					'id' => $response->SMSID,
					'message' => $populatedMsg,  // Return populated message
					'status' => $response->CodeResult == '100' ? self::MSG_STATUS_DISPATCHED : self::MSG_STATUS_FAILED,
					'statusmessage' => $response->ErrorMessage,
					'customer_id' => $customerId,                       
					'error' => $response == false || $response->CodeResult != '100'
				];

				$results[] = $result;
			}

			return $results;
		}

		public function handleCallback(array $data) {
			if (empty($data) || empty($data['SMSID'])) return;
			$smsId = $data['SMSID'];
			$sendStatus = $data['SendStatus'];

			if ($sendStatus == 5) {
				$status = self::MSG_STATUS_DELIVERED;
				$errorMsg = '';

				if ($data['SendFailed'] > 0) {
					$status = self::MSG_STATUS_FAILED;
					$errorMsg = 'Unknown error';
				}
			}
			else {
				$status = self::MSG_STATUS_FAILED;
				$errorMsg = $this->statusMapping[$sendStatus];
			}

			CPSMSOTTMessageLog_Record_Model::updateStatusByTrackingId($smsId, $status, $errorMsg);
			return true;
		}
				
		public function getProviderEditFieldTemplateName() {
			return 'BaseProviderEditFields.tpl';    // This template will not show username and password field
		}
	}
?>