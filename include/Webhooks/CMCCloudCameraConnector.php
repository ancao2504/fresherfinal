<?php

/*
	Webhook CMCCloudCameraConnector
	Author: Hieu Nguyen
	Date: 2021-06-08
	Purpose: to handle HTTP call back from CMCCloudCamera platform
*/

require_once('include/utils/CMCCloudCameraUtils.php');

class CMCCloudCameraConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		CPAICameraIntegration_Logic_Helper::checkConfig();

		// Get data from webhook
		$request = CMCCloudCameraUtils::getRequest();
		$data = $request->getAll();

		saveLog('AICAMERA_INTEGRATION', '[CMCCloudCamera] Webhook data', $data);

		if (!empty($data['personId'])) {
			CMCCloudCameraUtils::handleEventEmployeeCheckin($data);
		}
		
		echo 'Listening!';
	}
}