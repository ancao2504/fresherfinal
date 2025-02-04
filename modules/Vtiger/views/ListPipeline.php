<?php

/*
	View ConfigEditor
	Author: Hieu Nguyen
	Date: 2022-06-13
	Purpose: to render the Config Editor form
*/

// Inherited from ConfigEditorDetail.php
class Settings_Vtiger_ListPipeline_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$qualifiedName = $request->getModule(false);
		$configEditorModel = Settings_Vtiger_ConfigEditor_Model::getInstance();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODEL', $configEditorModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedName);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->display('modules/Settings/Vtiger/tpls/ListPipeline.tpl');
	}
	function getPageTitle(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		return "Pipeline";
	}

	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.Settings.{$moduleName}.resources.SettingPipeline",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		var_dump($jsScriptInstances);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}