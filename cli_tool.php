<?php

/*
	Script: cli_tool.php
	Author: Hieu Nguyen
	Date: 2022-08-23
	Purpose: to provide useful commands for CLI interface
	Usage: for CLI only
*/

ini_set('display_errors', 'Off');
error_reporting(~E_ALL);
if (PHP_SAPI != 'cli') die('This script is for CLI only!!!');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/utils/CustomConfigUtils.php');

// Parse options
$shortOptions = 'c:d:';
$longOptions = ['mode:data:'];
$options = getopt($shortOptions, $longOptions);
$command = $options['c'] ?? $options['command'];
$data = $options['d'] ?? $options['data'];

// Print help text
if (empty($command)) {
	$helpText = " --------------------- CLI TOOL by Hieu Nguyen at ONLINECRM --------------------\n";
	$helpText .= "|Supported params:								|\n";
	$helpText .= "|  -c or -command\tProvide command to execute				|\n";
	$helpText .= "|  -d or -data\t\tProvide addition data (if any) to execute the command	|\n";
	$helpText .= " -------------------------------------------------------------------------------\n";
	echo $helpText;
	exit;
}

// Handle command
if ($command == 'get_version') {
	echo $currentVersion;
}
else if ($command == 'update_version') {
	$newVersion = $data;

	if (strlen($newVersion) != 19 || !preg_match('/\d{1}\.\d{1}\.\d{1}\.\d{8}\.\d{4}/', $newVersion)) {
		die("The givent version is not in the right format!\n");
	}

	$customConfig = ['currentVersion' => $newVersion];
	CustomConfigUtils::saveCustomConfigs($customConfig);
	echo 'Current version is now = ' . $newVersion . "\n";
}
else if ($command == 'get_config') {
	$configName = $data;

	if (empty($configName)) {
		die("Please provide config name to get. Ex: -d=loggerConfig\n");
	}

	$config = $GLOBALS[$configName];
	echo json_encode($config, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\n";
}
else if ($command == 'update_configs') {
	$customConfigs = json_decode($data, true);
	CustomConfigUtils::saveCustomConfigs($customConfigs);
	echo "Done!\n";
}
else if ($command == 'quick_repair') {
	try {
		$repairAction = new Vtiger_QuickRepair_Action();
		$request = new Vtiger_Request([]);

		ob_start();
		@$repairAction->process($request);
		ob_clean();
		echo "Finished!\n";
	}
	catch (Exception $e) {
		echo 'Error: ' . $e->getMessage() . "\n";
	}
}