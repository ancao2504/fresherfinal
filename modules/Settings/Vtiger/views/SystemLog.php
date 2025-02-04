<?php

/*
	File: SystemLog.php
	Author: Hieu Nguyen
	Date: 2022-11-17
	Purpose: show all system logs for Admin
*/

class Settings_Vtiger_SystemLog_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		$this->exposeMethod('getMainView');
		$this->exposeMethod('getLogContent');
	}
	
	function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_SYSTEM_LOG', $moduleName);
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function getMainView(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$logsDir = 'logs/';
		$logFiles = glob($logsDir . '*.log');

		foreach ($logFiles as &$file) {
			$file = str_replace($logsDir, '', $file);
		}

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('LOG_FILES', $logFiles);
		$viewer->display('modules/Settings/Vtiger/tpls/SystemLog.tpl');
	}

	function getLogContent(Vtiger_Request $request) {
		$selectedFile = $request->get('selected_file');
		if (strpos($selectedFile, '../') !== false) return;	// Prevent hacking
		$logFile = 'logs/' . $selectedFile;
		if (!file_exists($logFile)) return;
		$logContent = file_get_contents($logFile);
		
		$response = new Vtiger_Response();
		$response->setResult(['log_content' => $logContent]);
		$response->emit();
	}
}