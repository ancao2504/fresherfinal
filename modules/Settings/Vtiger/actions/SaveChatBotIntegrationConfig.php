<?php

/*
	File: SaveChatbotIntegrationConfig.php
	Author: PhuVo
	Date: 2019.03.22
	Purpose: handle saving chatbot config
*/

require_once('include/utils/CustomConfigUtils.php');

// Modified by Hieu Nguyen on 2022-07-18
class Settings_Vtiger_SaveChatbotIntegrationConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('toggleConfig');
		$this->exposeMethod('saveConfig');
		$this->exposeMethod('disconnect');
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

	function toggleConfig(Vtiger_Request $request) {
		$data = $request->getAllPurified();
		$enable = filter_var($data['enable'], FILTER_VALIDATE_BOOLEAN);

		$customConfig = [
			'chatBotConfig.enable' => $enable,
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$responce = new Vtiger_Response();
		$responce->setResult(['success' => true]);
		$responce->emit();
	}

	function saveConfig(Vtiger_Request $request) {
		$data = $request->getAll();
		$config = $data['config'];
		$chatbotConfig = Settings_Vtiger_Config_Model::loadConfig('chatbot_integration_config', true) ?? [];
		$chatbotConfig['active_provider'] = $config['provider'];

		// Override config fields
		$chatbotConfig['params'] = $config['params'];

		// Only override chatbot list when it is updated by user
		if ($config['chatbots_updated'] == 'true') {
			$chatbotConfig['chatbots'] = json_decode($config['chatbot_infos'], true) ?? [];
		}

		Settings_Vtiger_Config_Model::saveConfig('chatbot_integration_config', $chatbotConfig);

		// Respond
		$responce = new Vtiger_Response();
		$responce->setResult(['success' => true]);
		$responce->emit();
	}

	public function disconnect(Vtiger_Request $request) {
		Settings_Vtiger_Config_Model::saveConfig('chatbot_integration_config', []);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}
}