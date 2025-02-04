<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

// Modified by Hieu Nguyen on 2023-01-18
class Settings_MenuEditor_EditAjax_View extends Settings_Vtiger_Index_View {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getSelectedMainMenuEditView');
		$this->exposeMethod('getEditMainMenuModal');
		$this->exposeMethod('getEditMenuGroupModal');
		$this->exposeMethod('getEditModulesMenuItemModal');
		$this->exposeMethod('getEditWebUrlMenuItemModal');
		$this->exposeMethod('getEditReportMenuItemModal');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function getSelectedMainMenuEditView(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MAIN_MENU_INFO', Settings_MenuEditor_Data_Model::getMainMenuInfo($mainMenuId));
		$viewer->display('modules/Settings/MenuEditor/tpls/MainMenuEditView.tpl');
	}

	function getEditMainMenuModal(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_ADD_MAIN_MENU_TITLE', $qualifiedModuleName));

		if (!empty($mainMenuId)) {
			$mainMenuInfo = Settings_MenuEditor_Data_Model::getMainMenuInfo($mainMenuId);

			if (!empty($mainMenuInfo)) {
				$viewer->assign('MAIN_MENU_INFO', $mainMenuInfo);
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_EDIT_MAIN_MENU_TITLE', $qualifiedModuleName));
			}
		}
		
		$viewer->display('modules/Settings/MenuEditor/tpls/EditMainMenuModal.tpl');
	}

	function getEditMenuGroupModal(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');
		$menuGroupId = $request->get('menu_group_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_ADD_MENU_GROUP_TITLE', $qualifiedModuleName));
		$viewer->assign('MAIN_MENU_ID', $mainMenuId);

		if (!empty($menuGroupId)) {
			$menuGroupInfo = Settings_MenuEditor_Data_Model::getMenuGroupInfo($menuGroupId);

			if (!empty($menuGroupInfo)) {
				$viewer->assign('MENU_GROUP_INFO', $menuGroupInfo);
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_EDIT_MENU_GROUP_TITLE', $qualifiedModuleName));
			}
		}
		
		$viewer->display('modules/Settings/MenuEditor/tpls/EditMenuGroupModal.tpl');
	}

	function getEditModulesMenuItemModal(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');
		$menuGroupId = $request->get('menu_group_id');
		$selectedModules = Settings_MenuEditor_Data_Model::getSelectedModules($mainMenuId, $menuGroupId);

		// Add unsupported modules here
		$unsupportedModules = [
			'Home', 'Dashboard', 'Google', 'Import', 'CPNotifications', 'WSAPP', 'Mobile', 'Webforms', 
			'ModTracker', 'CPLogAPI', 'Users', 'CustomerPortal', 'ExtensionStore', 'ModComments', 'CPReportDebits',
		];

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_ADD_MODULES_TITLE', $qualifiedModuleName));
		$viewer->assign('MAIN_MENU_ID', $mainMenuId);
		$viewer->assign('MENU_GROUP_ID', $menuGroupId);
		$viewer->assign('ALL_MODULES', Vtiger_Module_Model::getAll([0, 2], $unsupportedModules));
		$viewer->assign('SELECTED_MODULES', $selectedModules);
		$viewer->display('modules/Settings/MenuEditor/tpls/EditModulesMenuItemModal.tpl');
	}

	function getEditWebUrlMenuItemModal(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');
		$menuGroupId = $request->get('menu_group_id');
		$menuItemId = $request->get('menu_item_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_ADD_WEB_URL_MENU_ITEM_TITLE', $qualifiedModuleName));
		$viewer->assign('MAIN_MENU_ID', $mainMenuId);
		$viewer->assign('MENU_GROUP_ID', $menuGroupId);

		if (!empty($menuItemId)) {
			$menuItemInfo = Settings_MenuEditor_Data_Model::getMenuItemInfo($menuItemId);

			if (!empty($menuItemInfo)) {
				$viewer->assign('MENU_ITEM_INFO', $menuItemInfo);
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_EDIT_WEB_URL_MENU_ITEM_TITLE', $qualifiedModuleName));
			}
		}

		$viewer->display('modules/Settings/MenuEditor/tpls/EditWebUrlMenuItemModal.tpl');
	}

	function getEditReportMenuItemModal(Vtiger_Request $request) {
		require_once('modules/Reports/Reports.php');
		$qualifiedModuleName = $request->getModule(false);
		$mainMenuId = $request->get('main_menu_id');
		$menuGroupId = $request->get('menu_group_id');
		$menuItemId = $request->get('menu_item_id');

		$viewer = $this->getViewer($request);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_ADD_REPORT_MENU_ITEM_TITLE', $qualifiedModuleName));
		$viewer->assign('MAIN_MENU_ID', $mainMenuId);
		$viewer->assign('MENU_GROUP_ID', $menuGroupId);
		$viewer->assign('ALL_REPORTS', Reports::sgetRptsforFldr('All'));

		if (!empty($menuItemId)) {
			$menuItemInfo = Settings_MenuEditor_Data_Model::getMenuItemInfo($menuItemId);

			if (!empty($menuItemInfo)) {
				$viewer->assign('MENU_ITEM_INFO', $menuItemInfo);
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_MODAL_EDIT_REPORT_MENU_ITEM_TITLE', $qualifiedModuleName));
			}
		}

		$viewer->display('modules/Settings/MenuEditor/tpls/EditReportMenuItemModal.tpl');
	}
}