<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

// Modified by Hieu Nguyen on 2023-01-19
Class Settings_MenuEditor_SaveAjax_Action extends Settings_Vtiger_IndexAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('saveMainMenuInfo');
		$this->exposeMethod('deleteMainMenu');
		$this->exposeMethod('updateMainMenuLayout');
		$this->exposeMethod('saveMenuGroupInfo');
		$this->exposeMethod('deleteMenuGroup');
		$this->exposeMethod('saveMenuItemInfo');
		$this->exposeMethod('deleteMenuItem');
		$this->exposeMethod('updateSequence');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');

		if (!empty($mode)) {
			Vtiger_AdminAudit_Helper::saveLog('MenuEditor', "Do action {$mode}", $request);	// Save audit log
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function saveMainMenuInfo(Vtiger_Request $request) {
		$mainMenuId = $request->get('main_menu_id');
		$info = [];
		$info['main_menu_id'] = $mainMenuId;
		$info['name_vn'] = decodeUTF8($request->get('name_vn'));	// Modified by Vu Mai on 2023-02-21 to decode UTF8 name_vn before check exist and save
		$info['name_en'] = $request->get('name_en');
		$info['color'] = $request->get('color');
		$info['icon'] = $request->get('icon');
		
		$response = new Vtiger_Response();

		if (Settings_MenuEditor_Data_Model::checkExistMainMenuName($info['name_vn'], 'vn', $mainMenuId)) {
			$response->setResult(['success' => false, 'message' => 'NAME_VN_EXIST']);
			$response->emit();
			exit;
		}

		if (Settings_MenuEditor_Data_Model::checkExistMainMenuName($info['name_en'], 'en', $mainMenuId)) {
			$response->setResult(['success' => false, 'message' => 'NAME_EN_EXIST']);
			$response->emit();
			exit;
		}

		if (empty($mainMenuId)) {
			Settings_MenuEditor_Data_Model::insertNewMainMenu($info);
		}
		else {
			Settings_MenuEditor_Data_Model::updateMainMenuInfo($mainMenuId, $info);
		}

		$response->setResult(['success' => true]);
		$response->emit();
	}

	function deleteMainMenu(Vtiger_Request $request) {
		$mainMenuId = $request->get('main_menu_id');
		Settings_MenuEditor_Data_Model::deleteMainMenu($mainMenuId);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function updateMainMenuLayout(Vtiger_Request $request) {
		$mainMenuId = $request->get('main_menu_id');
		$selectedLayout = $request->get('selected_layout');
		Settings_MenuEditor_Data_Model::updateMainMenuLayout($mainMenuId, $selectedLayout);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function saveMenuGroupInfo(Vtiger_Request $request) {
		$menuGroupId = $request->get('menu_group_id');
		$info = [];
		$info['main_menu_id'] = $request->get('main_menu_id');
		$info['name_vn'] = $request->get('name_vn');
		$info['name_en'] = $request->get('name_en');
		
		$response = new Vtiger_Response();

		if (Settings_MenuEditor_Data_Model::checkExistMenuGroupName($info['name_vn'], 'vn', $info['main_menu_id'], $menuGroupId)) {
			$response->setResult(['success' => false, 'message' => 'NAME_VN_EXIST']);
			$response->emit();
			exit;
		}

		if (Settings_MenuEditor_Data_Model::checkExistMenuGroupName($info['name_en'], 'en', $info['main_menu_id'], $menuGroupId)) {
			$response->setResult(['success' => false, 'message' => 'NAME_EN_EXIST']);
			$response->emit();
			exit;
		}

		if (empty($menuGroupId)) {
			Settings_MenuEditor_Data_Model::insertNewMenuGroup($info);
		}
		else {
			Settings_MenuEditor_Data_Model::updateMenuGroup($menuGroupId, $info);
		}

		$response->setResult(['success' => true]);
		$response->emit();
	}

	function deleteMenuGroup(Vtiger_Request $request) {
		$menuGroupId = $request->get('menu_group_id');
		Settings_MenuEditor_Data_Model::deleteMenuGroup($menuGroupId);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function saveMenuItemInfo(Vtiger_Request $request) {
		$mainMenuId = $request->get('main_menu_id');
		$menuGroupId = $request->get('menu_group_id');
		$menuItemType = $request->get('menu_item_type');
		$menuItemInfo = $request->get('menu_item_info');

		$response = new Vtiger_Response();

		// Save multiple selected modules
		if ($menuItemType == 'modules') {
			Settings_MenuEditor_Data_Model::saveSelectedModulesMenuItem($mainMenuId, $menuGroupId, $menuItemInfo);
		}
		// Save a single menu item info
		else {
			$menuItemId = $request->get('menu_item_id');

			if (Settings_MenuEditor_Data_Model::checkExistMenuItemName($menuItemInfo['name_vn'], 'vn', $menuGroupId, $menuItemId)) {
				$response->setResult(['success' => false, 'message' => 'NAME_VN_EXIST']);
				$response->emit();
				exit;
			}

			if (Settings_MenuEditor_Data_Model::checkExistMenuItemName($menuItemInfo['name_en'], 'en', $menuGroupId, $menuItemId)) {
				$response->setResult(['success' => false, 'message' => 'NAME_EN_EXIST']);
				$response->emit();
				exit;
			}

			$infoToSave = [
				'type' => $menuItemType,
				'value' => $menuItemInfo,
			];

			if (empty($menuItemId)) {
				$infoToSave['main_menu_id'] = $mainMenuId;
				$infoToSave['menu_group_id'] = $menuGroupId;
				Settings_MenuEditor_Data_Model::insertNewMenuItem($infoToSave);
			}
			else {
				Settings_MenuEditor_Data_Model::updateMenuItemInfo($menuItemId, $infoToSave);
			}
		}

		$response->setResult(['success' => true]);
		$response->emit();
	}

	function deleteMenuItem(Vtiger_Request $request) {
		$menuItemId = $request->get('menu_item_id');
		Settings_MenuEditor_Data_Model::deleteMenuItem($menuItemId);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

	function updateSequence(Vtiger_Request $request) {
		$type = $request->get('type');
		$sequenceInfo = $request->get('sequence_info');
		Settings_MenuEditor_Data_Model::updateSequence($type, $sequenceInfo);

		$response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}