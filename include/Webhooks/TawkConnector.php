<?php

/*
	Webhook TawkConnector
	Author: Hieu Nguyen
	Date: 2021-02-22
	Purpose: to handle HTTP call back from Tawk.to platform
*/

class TawkConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		// Get data from webhook
		$rawData = file_get_contents('php://input');
		$data = json_decode($rawData, true);
		saveLog('CHATBOT_INTEGRATION', '[Tawk.to] Webhook data', $data);

		if (empty($rawData)) {
			echo 'Listening!';
			exit;
		}

		// Check signature
		if (!$this->verifySignature($rawData, $_SERVER['HTTP_X_TAWK_SIGNATURE'])) {
			saveLog('CHATBOT_INTEGRATION', '[Tawk.to] Signature invalid!', $_SERVER['HTTP_X_TAWK_SIGNATURE']);
			echo 'Signature invalid!';
			exit;
		}

		// Handle webhook request
		if ($data['event'] == 'chat:end') {
			CPChatBotIntegration_TawkLogic_Helper::handleChatEndEvent($data);
		}

		if ($data['event'] == 'ticket:create') {
			CPChatBotIntegration_TawkLogic_Helper::handleTicketCreateEvent($data);
		}
		
		echo 'OK';
	}

	function verifySignature($body, $signature) {
		$chatBotConfig = CPChatBotIntegration_Config_Helper::getConfig();
		$digest = hash_hmac('sha1', $body, $chatBotConfig['params']['secret_key']);
		return $signature === $digest ;
	}
}