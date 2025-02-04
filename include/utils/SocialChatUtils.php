<?php

/*
	Class SocialChatUtils
	Author: Hieu Nguyen
	Date: 2021-03-09
	Purpose: to provide utils functions for Social Chat
*/

class SocialChatUtils {

	protected static function getRestClient($serviceUrl) {
		$headers = array(
			'accept: application/json',
			'cache-control: no-cache',
			'content-type: application/json',
		);

		$curl = curl_init();
		
		curl_setopt_array($curl, array(
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
		));

		return $curl;
	}

	static function callChatStorageApi(string $method, string $path, array $params = []) {
		global $centralizedChatboxConfig;
		$serviceUrl = $centralizedChatboxConfig['chat_storage']['service_url'];
		$endpointUrl = $serviceUrl . $path;
		$accessToken = $centralizedChatboxConfig['chat_storage']['access_token'];
		$params['access_token'] = $accessToken;

		$curl = self::getRestClient($endpointUrl);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		// Save debug log
		if ($centralizedChatboxConfig['chat_storage']['debug'] == true) {
			saveLog('SOCIAL_INTEGRATION', '[SocialChatUtils::callSocialChatStorageApi] Request param', ['params' => $params, 'response' => $response, 'error' => $err]);
		}

		if ($err) {
			return false;
		}

		return json_decode($response, true);
	}
}