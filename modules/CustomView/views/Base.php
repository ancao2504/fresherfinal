<?php

/*
	BaseCustomView
	Author: Hieu Nguyen
	Date: 2018-08-29
	Purpose: provide a base view for other custom views
*/

class CustomView_Base_View extends Vtiger_Index_View {

	var $isFullView = true;

	function __construct($isFullView = true) {
		parent::__construct();
		$this->isFullView = $isFullView;
	}

	function preProcessTplName(Vtiger_Request $request) {
		// Use custom pre process template
	    return 'CustomViewPreProcess.tpl';
	}

	function getHeaderScripts(Vtiger_Request $request) {
		// Load js file that have the same name as the view
		$moduleName = $request->getModule();
		$viewName = $request->get('view');
		$headerScriptInstances = parent::getHeaderScripts($request);
		$jsFileNames = [
			"~modules/CustomView/resources/BaseController.js",
			"~modules/{$moduleName}/resources/{$viewName}.js",
			"~modules/Vtiger/resources/{$viewName}.js",
		];

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	function getHeaderCss(Vtiger_Request $request) {
		// Load css file that have the same name as the view
		$moduleName = $request->getModule();
		$viewName = $request->get('view');
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			"~modules/{$moduleName}/resources/{$viewName}.css",
			"~modules/Vtiger/resources/{$viewName}.css",
		];

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}

	function preProcess(Vtiger_Request $request) {
		if($this->isFullView) {
			parent::preProcess($request);
		}
		
		// Assign essential info into the view
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
	
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('USER_MODEL', $currentUserModel);
	}

	function process(Vtiger_Request $request) {
		// Do nothing
	}
    
    function postProcess(Vtiger_Request $request) {
		if($this->isFullView) {
			// Load custom post process template
			$viewer = $this->getViewer($request);
			$viewer->view('CustomViewPostProcess.tpl');
			parent::postProcess($request);
		}
	}
}