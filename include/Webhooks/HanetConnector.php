<?php

/*
    Webhook HanetConnector
    Author: Hieu Nguyen
    Date: 2021-04-02
    Purpose: to handle HTTP call back from Hanet platform
*/

require_once('include/utils/HanetUtils.php');

class HanetConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        if (!CPAICameraIntegration_Logic_Helper::isAICameraIntegrationSupported()) return;

        // Get data from webhook
        $request = HanetUtils::getRequest();
        $data = $request->getAll();

        saveLog('AICAMERA_INTEGRATION', '[Hanet] Webhook data', $data);

        if (!empty($data['aliasID'])) {
            if ($data['personType'] === '0') {
                // Handle event employee checkin
                HanetUtils::handleEventEmployeeCheckin($data);
            }

            if ($data['personType'] === '1') {
                // Handle event customer checkin
                HanetUtils::handleEventCustomerCheckin($data);
            }
        }
        else {
            if ($data['personType'] === '2') {
                // Handle event unknown person checkin
                HanetUtils::handleEventUnknownPersonCheckin($data);
            }
        }
        
        echo 'Listening!';
	}
}