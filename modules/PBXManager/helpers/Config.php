<?php

/*
	Config_Helper
	Author: Hieu Nguyen
	Date: 2022-07-29
	Purpose: to provide util functions for call center integration config
*/

class PBXManager_Config_Helper {

	static function isCallCenterEnabled() {
		global $callCenterConfig;
		if (!$callCenterConfig['enable']) return false;

		if (
			isForbiddenFeature('CloudCallCenterIntegration') &&
			isForbiddenFeature('PhysicalCallCenterIntegration')
		) {
			return false;
		}

		return true;
	}

	static function getCallCenterConfig() {
		static $config;
		if (!empty($config)) return $config;
		$config = Settings_Vtiger_Config_Model::loadConfig('callcenter_config', true) ?? [];
		return $config;
	}

	static function getGatewayConfig() {
		static $config;
		if (!empty($config)) return $config;
		$config = Settings_Vtiger_Config_Model::loadConfig('callcenter_gateway', true) ?? [];
		return $config;
	}
}