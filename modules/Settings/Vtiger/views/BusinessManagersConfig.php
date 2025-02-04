<?php

/*
	File: BusinessManagersConfig.php
	Auhtor: Vu Mai
	Date: 2022-08-01
	Purpose: Process Business Managers Config view 
*/

class Settings_Vtiger_BusinessManagersConfig_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct($isFullView = true);
	}

	function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_BUSINESS_MANAGERS_CONFIG', $moduleName);
	}

	function process(Vtiger_Request $request) {
		global $businessManagersConfig;
		$moduleName = $request->getModule(false);

		// Render view
		$view = $this->getViewer($request);
		$view->assign('MODULE_NAME', $moduleName);
		$view->assign('CONFIG', $businessManagersConfig);
		$view->display('modules/Settings/Vtiger/tpls/BusinessManagersConfig.tpl');
	}
}