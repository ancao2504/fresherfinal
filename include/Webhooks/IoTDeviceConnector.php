<?php

/*
    Webhook IoTDeviceConnector
    Author: Hieu Nguyen
    Date: 2022-06-02
    Purpose: to handle callback from IoT devices
*/

require_once('include/utils/WebhookUtils.php');

class IoTDeviceConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        // Get data from webhook
        $request = WebhookUtils::getRequest();
        $data = $request->getAllPurified();

		// Save log
        saveLog('IOT', '[IoTDeviceConnector::process] Webhook data', $data);
        
		// TODO: send notification

		echo 'Listening!';
	}
}