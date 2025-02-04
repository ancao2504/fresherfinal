<?php

/*
	BaseConfig_View
	Author: Hieu Nguyen
	Date: 2018-11-23
	Purpose: provide a base view for other custom config views
*/

class Settings_Vtiger_BaseConfig_View extends Settings_Vtiger_Index_View {

    function __construct() {
        
    }

	public function getPageTitle(Vtiger_Request $request) {
		return '';
	}

	public function process(Vtiger_Request $request) {
		return '';
	}

	public function getHeaderScripts(Vtiger_Request $request) {
        // Load js file that have the same name as the view
        $viewName = $request->get('view');

		$jsFileNames = array(
            "~modules/CustomView/resources/BaseController.js",
			"~modules/Settings/Vtiger/resources/{$viewName}.js",
		);

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = parent::getHeaderScripts($request);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        
		return $headerScriptInstances;
    }
    
    function getHeaderCss(Vtiger_Request $request) {
		// Load css file that have the same name as the view
		$viewName = $request->get('view');
		$cssFileNames = array("~modules/Settings/Vtiger/resources/{$viewName}.css");

        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
        $headerCssInstances = parent::getHeaderCss($request);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}
}