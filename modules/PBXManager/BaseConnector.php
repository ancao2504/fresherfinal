<?php

/*
	Base_Connector
	Author: Hieu Nguyen
	Date: 2018-10-05
	Purpose: to provide a parent abstract class for all new custom connectors later
	Usage: extends this class and override the functions you want. See example: CMCTelecom.php
*/

require_once('libraries/nusoap/nusoap.php');

class PBXManager_Base_Connector extends PBXManager_PBXManager_Connector {

	public $isPhysicalDevice = false;			// Indicate that this is a physical device, not cloud call center
	public $hasExternalReport = false;          // Indicate that there is an external report to show the call history
	public $hasDirectPlayRecordingApi = true;   // Indicate that this provider provides an api to play recording directly from call log
	protected static $SETTINGS_REQUIRED_PARAMETERS = ['webservice_url' => 'text', 'api_key' => 'password'];
	protected $webserviceUrl;
	protected $apiKey;
	protected $parameters = [];

	protected $soapOptions = [
		'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
		'style' => SOAP_RPC,
		'use' => SOAP_ENCODED,
		'soap_version' => SOAP_1_1,
		'cache_wsdl' => WSDL_CACHE_NONE,
		'connection_timeout' => 30,
		'trace' => true,
		'encoding' => 'UTF-8',
		'exceptions' => true,
	];

	function __construct() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $this->parameters = $serverModel->get('parameters');
        $this->setServerParameters($serverModel);
    }

	// Return the connector name
	public function getGatewayName() {
		return 'BaseConnector';
	}
	
	// Return webservice url based on request action
	public function getServiceUrl($action) {
		return $this->webserviceUrl . $action;
	}

	// Set params. Override this function if your param is different
	public function setServerParameters($serverModel) {
		$this->webserviceUrl = $serverModel->get('webservice_url');
		$this->apiKey = $serverModel->get('api_key');
	}
	
    // Return all saved parameters
    public function getParameters() {
        return $this->parameters;
    }

	// Return all setting fields
	public function getSettingFields() {
		return static::$SETTINGS_REQUIRED_PARAMETERS;
	}

	// Return info message for setting details page
	public function getSettingInfoMsg() {
		// Nothing to show
	}

	// Return help text message for setting edit page
	public function getSettingHelpText() {
		// Nothing to show
	}

	// Return REST client for Restful communication
	protected function getRestClient($serviceURL, $headers = [], $noHeaders = false) {
		$defaultHeaders = [
			'accept: application/json',
			'cache-control: no-cache',
			'content-type: application/json'
		];

		if ($noHeaders) {
			$headers = ['cache-control: no-cache'];
		}
		else {
			$headers = array_merge($headers, $defaultHeaders);
		}

		$curl = curl_init();
		
		curl_setopt_array($curl, [
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
		]);

		return $curl;
	}

	// Call a Restful API
	protected function callRestApi($curl, $method = 'POST', $params, $sendAsJSON = true, $responseAsJSON = true) {
		if (!empty($method)) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
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
		curl_close($curl);

		if ($err) {
			saveLog('CALLCENTER', '[PBXManager_Base_Connector::callRestApi] Error', $err);
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

	static function forwardToCallCenterBridge($msg) {
		require_once('libraries/SocketIO/SocketIO.php');
		global $callCenterConfig;

		// Forward data into the realtime service
		$host = $callCenterConfig['bridge']['server_name'];
		$port = $callCenterConfig['bridge']['server_port'];
		$ssl = $callCenterConfig['bridge']['server_ssl'];
		$backendPort = $callCenterConfig['bridge']['server_backend_port'];
		$backendSSL = $callCenterConfig['bridge']['server_backend_ssl'];
		$accessDomain = $callCenterConfig['bridge']['access_domain'];

		$httpPort = !empty($backendPort) ? $backendPort : $port;
		$isSSL = false;
		$socket = new SocketIO($host, $httpPort);

		if (!empty($backendPort)) {
			$isSSL = $backendSSL;
		}
		else {
			$isSSL = $ssl;
		}

		if ($isSSL) {
			$socket->setProtocole(SocketIO::SSL_PROTOCOLE);
		}

		$accessToken = PBXManager_Logic_Helper::getCallCenterBridgeAccessToken(true);
		$socket->setQueryParams(['domain' => $accessDomain, 'access_token' => $accessToken]);

		return $socket->emit('message', $msg);
	}
}