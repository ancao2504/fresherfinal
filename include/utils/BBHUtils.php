<?php

/*
	Class BBHUtils
	Author: Hieu Nguyen
	Date: 2020-09-11
	Purpose: To provide util functions for handling integration with Bot Ban Hang
*/

require_once('include/utils/WebhookUtils.php');

class BBHUtils extends WebhookUtils {

	static $logger = 'CHATBOT_INTEGRATION';

	static function getServiceUrl($type, $path = '') {
		global $chatBotConfig;
		return $chatBotConfig['bbh'][$type . '_service_url'] . $path;
	}

	static function getConversationUrl($botId, $chatCustomerId) {
		global $chatBotConfig;
		return $chatBotConfig['bbh']['chatbox_url'] . "?page_id={$botId}&user_id={$chatCustomerId}";
	}

	private static function getBBHClient(string $serviceUrl, string $method, array $headers = []) {
		$defaultHeaders = [
			'accept: application/json',
			'cache-control: no-cache',
			'content-type: application/json',
		];

		$headers = array_merge($defaultHeaders, $headers);
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

	static function callBBHApi(string $serviceUrl, string $method, array $headers = [], array $params = []) {
		$client = self::getBBHClient($serviceUrl, $method, $headers);

		// Make request
		curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
		$response = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			self::saveDebugLog("[BBH] Call BotBanHang API: {$method} {$serviceUrl}", $headers, $params, [$err]);
			return false;
		}

		// Get result
		$result = json_decode($response, true);
		self::saveDebugLog("[BBH] Call BotBanHang API: {$method} {$serviceUrl}", $headers, $params, $result);

		return $result;
	}

	static function getBBHBotInfo($botId) {
		$config = CPChatBotIntegration_Config_Helper::getConfig();
		return $config ? $config['chatbots'][$botId] : null;
	}

	static function saveDebugLog(string $description, array $headers = null, array $input = null, array $response = null) {
		global $chatBotConfig;

		if ($chatBotConfig['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}
}