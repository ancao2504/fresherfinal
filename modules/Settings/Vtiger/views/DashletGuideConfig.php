<?php

/*
	View: DashletGuideConfig
	Author: Hieu Nguyen
	Date: 2022-03-10
	Purpose: Config user guide for all dashlets
*/

class Settings_Vtiger_DashletGuideConfig_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct($isFullView = true);
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_DASHLET_GUIDE_CONFIG', $moduleName);
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$allWidgets = Home_DashBoard_Model::getWidgets();

		// Load config        
		$config = Settings_Vtiger_Config_Model::loadConfig('dashlet_guide');

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ALL_WIDGETS', $allWidgets);
		$viewer->display('modules/Settings/Vtiger/tpls/DashletGuideConfig.tpl');
	} 
}