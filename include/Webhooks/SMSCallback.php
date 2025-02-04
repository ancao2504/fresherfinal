<?php

/*
	Webhook SMSCallback
	Author: Hieu Nguyen
	Date: 2020-10-29
	Purpose: to handle HTTP call back from SMS Provider
*/

require_once('include/utils/WebhookUtils.php');
require_once('modules/SMSNotifier/SMSNotifier.php');

class SMSCallback extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		// Get data from webhook
		$request = WebhookUtils::getRequest();
		$data = $request->getAll();

		saveLog('SMS', '[SMSCallback] Webhook data', $data);

		// Handle request
		if (!empty($data['provider'])) {
			$activeProvider = SMSNotifierManager::getActiveProviderInstance();

			if (empty($activeProvider)) {
				die('No active provider found!');
			}

			if ($data['provider'] == $activeProvider->getName()) {
				$result = $activeProvider->handleCallback($data);
				echo $result ? 'OK' : 'ERR';
				exit;
			}
			else {
				die("Provider {$data['provider']} is not active!");
			}
		}
		
		echo 'Listening!';
	}
}