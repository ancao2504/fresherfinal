<?php

/*
	File: SaveCallCenterConfig.php
	Author: PhuVo
	Date: 2019.07.30
	Purpose: CallCenter config ajax handler
	Modified by Vu Mai 2022-07-20
*/

require_once('include/utils/CustomConfigUtils.php');

class Settings_Vtiger_SaveCallCenterConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('toggleConfig');
		$this->exposeMethod('saveConfig');
		$this->exposeMethod('saveConnection');	// Added by Hieu Nguyen on 2022-07-29
		$this->exposeMethod('disconnect');		// Added by Hieu Nguyen on 2022-07-29
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
			'callCenterConfig.enable' => $enable,
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function saveConfig(Vtiger_Request $request) {
		$config = $request->get('config');
		$inboundConfig = $request->get('inbound_config');
		$outboundConfig = $request->get('outbound_config');
		if (empty($config)) return;

		// Save general settings
		$generalConfig = $config['general'];
		$amiVersion = $generalConfig['ami_version'];
		unset($generalConfig['ami_version']);
		Settings_Vtiger_Config_Model::saveConfig('callcenter_config', $generalConfig);

		// Save custom config
		$bridgeSettings = $config['bridge'];
		$customConfig = [
			'callCenterConfig.ami_version' => $amiVersion,
			'callCenterConfig.bridge.server_name' => $bridgeSettings['server_name'],
			'callCenterConfig.bridge.server_port' => $bridgeSettings['default_port'],
			'callCenterConfig.bridge.server_ssl' => $bridgeSettings['default_port_ssl'] == 'on' ? true : false,
			'callCenterConfig.bridge.server_backend_port' => $bridgeSettings['backend_port'],
			'callCenterConfig.bridge.server_backend_ssl' => $bridgeSettings['backend_port_ssl'] == 'on' ? true : false,
			'callCenterConfig.bridge.access_domain' => $bridgeSettings['access_domain'],
			'callCenterConfig.bridge.private_key' => $bridgeSettings['private_key'],
			'callCenterConfig.inbound_routing' => $inboundConfig,
			'callCenterConfig.outbound_routing' => $outboundConfig,
			'callCenterConfig.click2call_users_can_use_all_hotlines' => explode(',', $config['click2call_users_can_use_all_hotlines']),	// Convert to array
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	// Implemented by Hieu Nguyen on 2022-07-29
	public function saveConnection(Vtiger_Request $request) {
		$config = $request->get('config');
		$gatewayConfig = PBXManager_Config_Helper::getGatewayConfig();
		$gatewayConfig['active_gateway'] = $config['provider'];
		$gatewayConfig['params'] = $config['params'];
		Settings_Vtiger_Config_Model::saveConfig('callcenter_gateway', $gatewayConfig);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}

	// Implemented by Hieu Nguyen on 2022-07-29
	public function disconnect(Vtiger_Request $request) {
		Settings_Vtiger_Config_Model::saveConfig('callcenter_gateway', []);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}
}