<?php

/*
	View GoogleIntegrationConfig
	Author: Hieu Nguyen
	Date: 2022-06-14
	Purpose: show UI for user to update parameters to integration with Google
*/

class Settings_Vtiger_GoogleIntegrationConfig_View extends Settings_Vtiger_BaseConfig_View {

	function getPageTitle(Vtiger_Request $request) {
		$qualifiedName = $request->getModule(false);
		return vtranslate('LBL_GOOGLE_INTEGRATION_CONFIG', $qualifiedName);
	}

	public function process(Vtiger_Request $request) {
		global $googleConfig;
		checkAccessForbiddenFeature('GoogleIntegration');
		$qualifiedName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $qualifiedName);
		$viewer->assign('CONFIG', $googleConfig);
		$viewer->display('modules/Settings/Vtiger/tpls/GoogleIntegrationConfig.tpl');
	}
};