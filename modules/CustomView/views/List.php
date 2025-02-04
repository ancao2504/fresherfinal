<?php

/*
	CustomView_List_View
	Author: Hieu Nguyen
	Date: 2018-08-29
	Purpose: reuse the ListView in this module to handle custom views mechanism
*/

class CustomView_List_View extends Vtiger_List_View {

	var $targetModuleModel = null;

	function __construct() {
		parent::__construct();
	}

	private function getPureParam($request, $paramName) {
		$pureParam = preg_replace('/[^a-zA-Z0-9]/', '', $request->get($paramName));
		
		return $pureParam;
	}

	private function getCustomViewFile($request) {
		$targetModuleName = $this->getPureParam($request, 'targetModule');
		$customViewName = $this->getPureParam($request, 'customView');
		$customViewFile = 'modules/'. $targetModuleName .'/views/' . $customViewName . '.php';

		return $customViewFile;
	}

	// Get template for module sidebar
	function preProcessTplName(Vtiger_Request $request) {
		$customViewName = $this->getPureParam($request, 'customView');
		$customViewFile = $this->getCustomViewFile($request);

		if(file_exists($customViewFile)) {
			require_once($customViewFile);
			
			$customView = new $customViewName(null);
			return $customView->preProcessTplName($request);
		}
		else {
			return 'ListViewPreProcess.tpl';
		}
	}

	// Hack to set target module model into the pre process template
	function preProcess(Vtiger_Request $request, $display = true) {
		$targetModuleName = $request->get('targetModule');
		$this->targetModuleModel = Vtiger_Module_Model::getInstance($targetModuleName);

		if($this->targetModuleModel == null) {
			die('Target Module name not found!');
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('TARGET_MODULE_MODEL', $this->targetModuleModel);
		
		parent::preProcess($request, $display);
	}

	// Navigate user to the right custom view
	function process(Vtiger_Request $request) {
		$customViewName = $this->getPureParam($request, 'customView');
		$customViewFile = $this->getCustomViewFile($request);

		if(file_exists($customViewFile)) {
			require_once($customViewFile);
			
			$customView = new $customViewName($this->targetModuleModel);
			$customView->checkPermission($request);
			$customView->preProcess($request);
			$customView->process($request);
			$customView->postProcess($request);
		}
		else {
			echo 'CustomView not found!';
		}
	}
}