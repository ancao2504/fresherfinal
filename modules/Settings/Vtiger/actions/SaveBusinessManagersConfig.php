<?php

/*
	File: SaveCentralizeChatboxConfig.php
	Author: Vu Mai
	Date: 2022-08-01
	Purpose: Business managers config ajax handler
*/

require_once('include/utils/CustomConfigUtils.php');

class Settings_Vtiger_SaveBusinessManagersConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('saveConfig');
	}

	function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess(); 
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function saveConfig(Vtiger_Request $request) {
		$config = $request->get('config');
		if (empty($config)) return;

		// Save custom config
		$customConfig = [
			'businessManagersConfig.facebook_integration' => explode(',', $config['facebook_integration_managers']),
			'businessManagersConfig.zalo_integration' => explode(',', $config['zalo_integration_managers']),
			'businessManagersConfig.telesales_campaign' => explode(',', $config['telesales_campaign_managers']),
			'businessManagersConfig.leads_distribution' => explode(',', $config['leads_distribution_managers']),
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}