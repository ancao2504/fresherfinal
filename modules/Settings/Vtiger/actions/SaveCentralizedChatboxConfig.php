<?php

/*
	File: SaveCentralizeChatboxConfig.php
	Author: Vu Mai
	Date: 2022-07-29
	Purpose: Centralize Chatbox config ajax handler
*/

require_once('include/utils/CustomConfigUtils.php');

class Settings_Vtiger_SaveCentralizedChatboxConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('toggleConfig');
		$this->exposeMethod('saveConfig');
	}

	function validateRequest(Vtiger_Request $request) { 
		$request->validateWriteAccess(); 
	}

	function process(Vtiger_Request $request) {
		checkAccessForbiddenFeature('CentralizedChatbox');
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
			'centralizedChatboxConfig.enable' => $enable,
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function saveConfig(Vtiger_Request $request) {
		$config = $request->get('config');
		if (empty($config)) return;

		// Save custom config
		$customConfig = [
			'centralizedChatboxConfig.chat_bridge.server_name' => $config['chat_bridge']['server_name'],
			'centralizedChatboxConfig.chat_bridge.server_port' => $config['chat_bridge']['default_port'],
			'centralizedChatboxConfig.chat_bridge.server_ssl' => $config['chat_bridge']['default_port_ssl'] == 'on' ? true : false,
			'centralizedChatboxConfig.chat_bridge.server_backend_port' => $config['chat_bridge']['backend_port'],
			'centralizedChatboxConfig.chat_bridge.server_backend_ssl' => $config['chat_bridge']['backend_port_ssl'] == 'on' ? true : false,
			'centralizedChatboxConfig.chat_bridge.access_domain' => $config['chat_bridge']['access_domain'],
			'centralizedChatboxConfig.chat_bridge.private_key' => $config['chat_bridge']['private_key'],
			'centralizedChatboxConfig.chat_storage.service_url' => $config['chat_storage']['service_url'],
			'centralizedChatboxConfig.chat_storage.access_token' => $config['chat_storage']['access_token'],
			'centralizedChatboxConfig.chat_admins' => explode(',', $config['chat_admins']), // Convert to array
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}