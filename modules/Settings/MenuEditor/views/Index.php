<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

// Modified by Hieu Nguyen on 2023-01-18
class Settings_MenuEditor_Index_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getMainMenuList');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$this->renderMainView($request);
	}

	public function renderMainView(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->display('modules/Settings/MenuEditor/tpls/Index.tpl');
	}

	public function getMainMenuList(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$selectedMainMenuId = $request->get('selected_main_menu_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('QUALIFIED_MODULE_NAME', $qualifiedModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('SELECTED_MAIN_MENU_ID', $selectedMainMenuId);
		$viewer->display('modules/Settings/MenuEditor/tpls/MainMenuList.tpl');
	}
}
