<?php

class ModuleName_List_View extends Vtiger_Index_View {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function preProcessTplName(Vtiger_Request $request) {
		return 'ListViewPreProcess.tpl';
	}

	function preProcess(Vtiger_Request $request, $display = true) {
		return parent::preProcess($request, $display);
	}

	function process(Vtiger_Request $request) {
		return '';
	}
}