<?php

/*
	Class IoTUtils
	Author: Hieu Nguyen
	Date: 2022-06-02
	Purpose: To provide util functions for working with IoT devices
*/

class IoTUtils {

	protected static function getClient($serviceUrl, array $headers = [], $requestAsJson = true) {
		$defaultHeaders = [
			'accept: application/json',
			'cache-control: no-cache',
		];

		if ($requestAsJson) {
			$defaultHeaders = array_merge($defaultHeaders, ['content-type: application/json']);
		}

		$headers = array_merge($headers, $defaultHeaders);
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
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		]);

		return $client;
	}

	static function callApi($method = 'POST', $serviceUrl, array $headers, array $params, $requestAsJson = true) {
		saveLog('IOT', '[IoTUtils::callApi] API Params: '. $serviceUrl, $params);
		$client = self::getClient($serviceUrl, $headers, $requestAsJson);

		if (!empty($method)) {
			curl_setopt($client, CURLOPT_CUSTOMREQUEST, $method);
		}

		if (!$requestAsJson) {
			curl_setopt($client, CURLOPT_POSTFIELDS, $params);
		}
		else {
			curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
		}

		$response = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			saveLog('IOT', '[IoTUtils::callApi] Call API Error: '. $serviceUrl, [$err]);
			return false;
		}

		$data = json_decode($response, true);
		saveLog('IOT', '[IoTUtils::callApi] Call API Success: '. $serviceUrl, $data);

		return $data;
	}
}