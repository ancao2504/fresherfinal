<?php

/*
	Class HanaUtils
	Author: Hieu Nguyen
	Date: 2019-07-03
	Purpose: To provide util functions for handling integration with Hana
*/

require_once('include/utils/WebhookUtils.php');

class HanaUtils extends WebhookUtils {

	static $logger = 'CHATBOT_INTEGRATION';

	static function getServiceUrl($path) {
		global $chatBotConfig;
		return $chatBotConfig['hana']['service_url'] . $path;
	}

	private static function getHanaClient(string $serviceUrl, string $authToken, string $method) {
		$headers = [
			'accept: application/json',
			'cache-control: no-cache',
			'content-type: application/json',
			'authorization: ' . $authToken,
		];

		$client = curl_init();
		
		curl_setopt_array($client, [
			CURLOPT_URL => $serviceUrl,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_HTTPHEADER => $headers,
		]);

		return $client;
	}

	static function callHanaApi(string $serviceUrl, string $method, string $authToken, array $params = []) {
		$client = self::getHanaClient($serviceUrl, $authToken, $method);

		// Make request
		curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
		$response = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			self::saveDebugLog("[Hana] Call Hana API: {$method} {$serviceUrl}", [], $params, [$err]);
			return false;
		}

		// Get result
		$result = json_decode($response, true);
		self::saveDebugLog("[Hana] Call Hana API: {$method} {$serviceUrl}", [], $params, $result);

		return $result;
	}

	/** Modified by Phu Vo on 2020.06.22 */
	static function getHanaBotInfo($appId) {
		$config = CPChatBotIntegration_Config_Helper::getConfig();
		return $config ? $config['chatbots'][$appId] : null;
	}

	static function saveDebugLog(string $description, array $headers = null, array $input = null, array $response = null) {
		global $chatBotConfig;

		if ($chatBotConfig['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}
}