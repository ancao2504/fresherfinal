<?php

/*
	Webhook OTTCallback
	Author: Hieu Nguyen
	Date: 2020-10-29
	Purpose: to handle HTTP call back from OTT Provider
*/

require_once('include/utils/WebhookUtils.php');

class OTTCallback extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		// Get data from webhook
		$request = WebhookUtils::getRequest();
		$data = $request->getAll();

		saveLog('SMS', '[OTTCallback] Webhook data', $data);

		// Handle request
		if (!empty($data['channel']) && !empty($data['provider'])) {
			$activeGateway = CPOTTIntegration_Gateway_Model::getActiveGateway($data['channel']);

			if (empty($activeGateway)) {
				die('No active provider found!');
			}

			if ($data['provider'] == $activeGateway->getName()) {
				$result = $activeGateway->handleCallback($data);
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