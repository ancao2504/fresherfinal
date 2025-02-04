<?php

/*
	Action SaveOTTIntegrationConfig
	Author: Hieu Nguyen
	Date: 2022-06-22
	Purpose: to save settings submitted from OTT Integration Config form
*/

class Settings_Vtiger_SaveOTTIntegrationConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('saveConfig');
		$this->exposeMethod('disconnect');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}

	public function saveConfig(Vtiger_Request $request) {
		$channel = $request->get('channel');
		$gateway = $request->get('gateway');
		$config = $request->get('config');

		if (empty($channel) || empty($gateway) || empty($config)) {
			die('Bad request!');
		}

		$config['channel'] = $channel;
		$config['active_gateway'] = $gateway;
		CPOTTIntegration_Config_Helper::saveConfig($channel, $config);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}

	public function disconnect(Vtiger_Request $request) {
		$channel = $request->get('channel');
		
		if (empty($channel)) {
			die('Ba request!');
		}

		CPOTTIntegration_Config_Helper::saveConfig($channel, []);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}
}