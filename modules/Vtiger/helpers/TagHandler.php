<?php

/*
	TagHandler Helper
	Author: vu Mai
	Date: 2022-10-18
	Purpose: to handle events for customer tag changed
*/

require_once('modules/PBXManager/BaseConnector.php');

class Vtiger_TagHandler_Helper {
	
	public static function handleCustomerTagsChanged ($customerId) {
		global $current_user, $callCenterConfig;
		if ($callCenterConfig['enable'] == false) return;

		// Send saved log event to client dashboard
		$msg = array(
			'state' => 'DATA_CHANGED',
			'receiver_id' => $current_user->id,
			'data_type' => 'LINKED_TAG',
			'customer_id' => $customerId,
		);

		PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
	}
}