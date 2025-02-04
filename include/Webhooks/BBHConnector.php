<?php

/*
	Webhook BBHConnector
	Author: Hieu Nguyen
	Date: 2019-09-17
	Purpose: to handle HTTP call back from BBH platform
*/

require_once('include/utils/BBHUtils.php');

class BBHConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		// Get data from webhook
		$request = BBHUtils::getRequest();
		$data = $request->getAll();

		BBHUtils::saveLog('[BBH] Webhook data', null, $data);

		if (!empty($data['page_id']) && !empty(BBHUtils::getBBHBotInfo($data['page_id']))) {
			// Handle BBH's update customer info signal
			if ($data['event_name'] == 'customer_info') {
				CPChatBotIntegration_BBHLogic_Helper::syncCustomerFromBBH($data);
			}
		}
		
		echo 'Listening!';
	}
}