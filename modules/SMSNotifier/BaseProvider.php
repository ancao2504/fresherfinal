<?php
	/*
	*	Base_Provider
	*	Author: Hieu Nguyen
	*	Date: 2018-06-27
	*	Purpose: to provide a parent abstract class for all new custom providers later
	*   Usage: extends this class and override the functions you want. See example: SouthTelecomSOAP.php
	*/

	require_once('libraries/nusoap/nusoap.php');

	class SMSNotifier_Base_Provider implements SMSNotifier_ISMSProvider_Model {

		protected $serviceURI;
		protected $username;
		protected $password;
		protected $parameters = array();
		protected $requiredParams = array();

		protected $soapOptions = array(
			'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
			'style' => SOAP_RPC,
			'use' => SOAP_ENCODED,
			'soap_version' => SOAP_1_1,
			'cache_wsdl' => WSDL_CACHE_NONE,
			'connection_timeout' => 30,
			'trace' => true,
			'encoding' => 'UTF-8',
			'exceptions' => true,
		);

		protected $isUnicodeSMSSupported = false;	// Determine if the provider support unicode SMS

		function __construct() {

		}

		// Returns the provider name
		public function getName() {
			return 'BaseProvider';
		}

		// Returns the provider info
		public function getInfo() {
			$info = [
				'provider_name' => $this->getName(),
				'unicode_sms_supported' => $this->isUnicodeSMSSupported,
			];

			return $info;
		}

		// Return call back url
		public function getCallbackUrl() {
			global $site_URL, $secretKey;
			$callbackUrl = "{$site_URL}/webhook.php?name=SMSCallback&provider=". $this->getName() ."&secret_key={$secretKey}";
			return $callbackUrl;
		}

		// Check if promotion api supported
		public function canSendPromotionMsg() {
			if (method_exists($this, 'sendPromotionMsg')) {
				return true;
			}

			return false;
		}

		// Set authorization parameters
		public function setAuthParameters($username, $password) {
			$this->username = $username;
			$this->password = $password;
		}

		// Set other parameters
		public function setParameter($key, $value) {
			$this->parameters[$key] = $value;
		}

		// Returns parameters
		public function getParameter($key, $defValue = false) {
			if (isset($this->parameters[$key])) {
				return $this->parameters[$key];
			}

			return $defValue;
		}

		// Returns required params for validating
		public function getRequiredParams() {
			return $this->requiredParams;
		}

		// Return service url based on the request type. Override this function to change the logic
		public function getServiceURL($type = '') {
			return '';
		}

		// Prepare parameter for the request. Override this function if you need to change the logic
		protected function prepareParameters() {
			$params = array('Username' => $this->username, 'Password' => $this->password);
			
			foreach ($this->requiredParams as $param) {
				$field = is_array($param) ? $param['name'] : $param;
				$params[$field] = $this->getParameter($field);
			}

			return $params;
		}

		// Cleanup special characters from the phone number to prepare for sending SMS
		protected function correctPhoneNumber($phoneNumber, $autoVNCode = true) {
			if (empty($phoneNumber)) {
				return '';
			}

			$phoneNumber = str_replace(array('(', ')', ' ', '+', '-'), '', $phoneNumber);

			if ($autoVNCode == true && $phoneNumber[0] == '0') {
				$phoneNumber = '84' . substr($phoneNumber, 1);
			}

			return $phoneNumber;
		}

		protected function getRestClient($serviceURL, $headers = array(), $noHeaders = false) {
			$defaultHeaders = array(
				'accept: application/json',
				'cache-control: no-cache',
				'content-type: application/json'
			);

			if ($noHeaders) {
				$headers = array('cache-control: no-cache');
			}
			else {
				$headers = array_merge($headers, $defaultHeaders);
			}

			$curl = curl_init();
			
			curl_setopt_array($curl, array(
				CURLOPT_URL => $serviceURL,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => $headers,
			));

			return $curl;
		}

		protected function callRestAPI($curl, $params, $sendAsJSON = true, $responseAsJSON = true) {
			global $smsConfig;

			// Do GET request if no param is specified
			if (empty($params)) {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
			}

			// Set data type
			if ($sendAsJSON) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
			}
			else {
				curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
			}

			$response = curl_exec($curl);
			$err = curl_error($curl);
			// curl_close($curl);   // Comment out this line to allow sending multiple messages at ListView

			// Save debug log
			if ($smsConfig['debug'] == true) {
				$endpointUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
				saveLog('SMS', '[SMSNotifier_Base_Provider::callRestAPI] Call API to ' . $endpointUrl, ['params' => $params, 'response' => $response, 'error' => $err]);
			}

			if ($err) {
				return false;
			}

			// Return data
			if ($responseAsJSON) {
				return json_decode($response);
			}
			else {
				return trim($response);
			}
		}

		// Send message to a single or array of multiple phone numbers. Override this function to handle the logic
		public function send($message, $toNumbers) {
			// Result must be in this format
			$results = array(
				array(
					'to' => '',								// Phone number
					'id' => '',								// Message ID
					'status' => self::MSG_STATUS_DELIVERED,	// Status value must one of the following: MSG_STATUS_DISPATCHED, MSG_STATUS_PROCESSING, MSG_STATUS_DELIVERED, MSG_STATUS_FAILED, MSG_STATUS_ERROR
					'error' => false						// Error: true, no error: false
				)
			);

			return $results;
		}

		// Query message status. Override this function if you need
		public function query($messageId) {
			return true;
		}
	}
?>