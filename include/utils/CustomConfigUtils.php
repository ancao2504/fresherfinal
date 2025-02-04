<?php

/*
	CustomConfigUtils
	Author: Hieu Nguyen
	Date: 2022-05-24
	Purpose: to save & load custom config without modifying the main config_override.php file
*/

require_once('vendor/minwork/array/src/Arr.php');
use Minwork\Helper\Arr;

class CustomConfigUtils {

	// Support nested array traverse using a simple path string. Ex: ['path.to.key' => 'new_value']
	static function saveCustomConfigs(array $configs) {
		require_once('include/utils/FileUtils.php');
		global $customConfigFile;
		$customConfigs = include($customConfigFile);

		if (empty($customConfigs) || !is_array($customConfigs)) {
			$customConfigs = [];
		}

		foreach ($configs as $configPath => $configValue) {
			// $customConfigs = Arr::set($customConfigs, $configPath, $configValue);
			$customConfigs[$configPath] = $configValue;
		}

		FileUtils::writeReturnArrayToFile($customConfigs, $customConfigFile, 'WARNING: DO NOT MODIFY THIS FILE!!!');
	}

	// Load and merge custom configs with default configs
	static function loadCustomConfigs() {
		require_once('libraries/ArrayUtils/ArrayUtils.php');
		global $customConfigFile;
		$customConfigs = include($customConfigFile);

		foreach ($customConfigs as $configPath => $configValue) {
			// $GLOBALS[$configName] = merge_deep_array([$GLOBALS[$configName], $configValue]);
			$GLOBALS = Arr::set($GLOBALS, $configPath, $configValue);
		}
	}
}