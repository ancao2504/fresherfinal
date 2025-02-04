<?php

/*
	Webhook HanetAICameraConnector
	Author: Hieu Nguyen
	Date: 2021-04-02
	Purpose: to handle HTTP call back from Hanet AI Camera platform
*/

require_once('include/utils/HanetAICameraUtils.php');

class HanetAICameraConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CPAICameraIntegration_Logic_Helper::checkConfig();

		// Get data from webhook
		$request = HanetAICameraUtils::getRequest();
		$data = $request->getAll();

		saveLog('AICAMERA_INTEGRATION', '[Hanet] Webhook data', $data);

		// Modified by Vu Mai on 2022-12-13 to only handle event checkin when data type is log
		if ($data['data_type'] != 'log') {
			saveLog('AICAMERA_INTEGRATION', 'Skip this event as data type is not expected checkin log');
			exit();
		}
		// End Vu Mai
		
		if (!empty($data['aliasID'])) {
			if ($data['personType'] === '0') {
				// Handle event employee checkin
				HanetAICameraUtils::handleEventEmployeeCheckin($data);
			}

			if ($data['personType'] === '1') {
				// Handle event customer checkin
				HanetAICameraUtils::handleEventCustomerCheckin($data);
			}
		}
		else {
			if ($data['personType'] === '2') {
				// Handle event unknown person checkin
				HanetAICameraUtils::handleEventUnknownPersonCheckin($data);
			}
		}
		
		echo 'Listening!';
	}
}