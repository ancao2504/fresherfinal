<?php

/*
	Webhook HanaConnector
	Author: Hieu Nguyen
	Date: 2019-07-03
	Purpose: to handle HTTP call back from Hana platform
*/

require_once('include/utils/HanaUtils.php');

class HanaConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		// Get data from webhook
		$request = HanaUtils::getRequest();
		$data = $request->getAll();

		HanaUtils::saveLog('[Hana] Webhook data', null, $data);

		if (!empty($data['event_name']) && !empty($data['application']) && !empty(HanaUtils::getHanaBotInfo($data['application']['id']))) {
			// Handle Hana's update customer info signal
			if ($data['event_name'] == 'update_customer_info') {
				CPChatBotIntegration_HanaLogic_Helper::syncCustomerFromHana($data);
			}

			// Handle Hana's reflink signal
			if ($data['event_name'] == 'ref_link') {
				CPChatBotIntegration_HanaEvent_Helper::handleReflinkEvent($data);
			}
		}
		
		echo 'Listening!';
	}
}