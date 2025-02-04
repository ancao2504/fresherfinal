<?php

/*
	Class Settings_MenuEditor_Data_Model
	Author: Hieu Nguyen
	Date: 2023-01-18
	Purpose: provide util function to handle data for Menu Editor
*/

class Settings_MenuEditor_Data_Model {

	static function getAllMainMenus() {
		global $adb, $current_user;
		$sql = "SELECT * FROM vtiger_main_menus ORDER BY sequence";
		$result = $adb->pquery($sql, []);
		$mainMenus = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);
			$displayName = ($current_user->language == 'vn_vn') ? $row['name_vn'] : $row['name_en'];
			$row['name'] = $displayName;
			$mainMenus[] = $row;
		}

		return $mainMenus;
	}

	static function getMainMenuInfo($mainMenuId) {
		global $adb;
		$sql = "SELECT * FROM vtiger_main_menus WHERE id = ?";
		$result = $adb->pquery($sql, [$mainMenuId]);

		if ($result) {
			$info = $adb->fetchByAssoc($result);
			return decodeUTF8($info);
		}

		return [];
	}

	static function getMenuGroupInfo($menuGroupId) {
		global $adb;
		$sql = "SELECT * FROM vtiger_menu_groups WHERE id = ?";
		$result = $adb->pquery($sql, [$menuGroupId]);

		if ($result) {
			$info = $adb->fetchByAssoc($result);
			return decodeUTF8($info);
		}

		return [];
	}

	static function getMenuGroupsByMainMenu($mainMenuId) {
		global $adb, $current_user;
		$sql = "SELECT * FROM vtiger_menu_groups WHERE main_menu_id = ? ORDER BY sequence";
		$result = $adb->pquery($sql, [$mainMenuId]);
		$menuGroups = [];

		// Found groups belong to the main menu, fetch group name and its menu items
		if ($adb->num_rows($result) > 0) {
			while ($row = $adb->fetchByAssoc($result)) {
				$row = decodeUTF8($row);
				$displayName = ($current_user->language == 'vn_vn') ? $row['name_vn'] : $row['name_en'];
				$row['name'] = $displayName;
				$menuGroups[] = $row;
			}
		}
		// No group found, fetch all menu items belongs to the main menu
		else {
			$menuGroups[] = [
				'id' => 'uncategorized',
				'name' => vtranslate('LBL_UNCATEGORIZED', 'Settings:MenuEditor', $current_user->language == 'vn_vn' ? 'vn_vn' : 'en_us'),
			];
		}

		return $menuGroups;
	}

	// Added by Vu Mai on 2023-02-02 to get menu items amount belongs to the main menu
	static function getMenuItemsCountByMainMenu($mainMenuId) {
		global $adb;

		$sql = "SELECT COUNT(id) FROM vtiger_menu_items WHERE main_menu_id = ?";
		$params = [$mainMenuId];
		$result = $adb->getOne($sql, $params);
		
		return $result;
	}

	static function getMenuItemsByMenuGroup($mainMenuId, $menuGroupId) {
		global $adb, $current_user, $moduleIcons;

		if ($menuGroupId == 'uncategorized') {
			$sql = "SELECT * FROM vtiger_menu_items WHERE main_menu_id = ? ORDER BY sequence";
			$params = [$mainMenuId];
		}
		else {
			$sql = "SELECT * FROM vtiger_menu_items WHERE main_menu_id = ? AND menu_group_id = ? ORDER BY sequence";
			$params = [$mainMenuId, $menuGroupId];
		}

		$result = $adb->pquery($sql, $params);
		$menuItems = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);

			if ($row['type'] == 'module') {
				$moduleName = $row['value'];

				if (isForbiddenFeature("Module{$moduleName}") || isHiddenModule($moduleName)) {
					continue;
				}

				$displayName = vtranslate($moduleName, $moduleName, $current_user->language == 'vn_vn' ? 'vn_vn' : 'en_us');
				$row['name'] = $displayName;
				$row['icon'] = $moduleIcons[$moduleName] ?? $moduleIcons['Default'];
			}
			else {
				$row['value'] = json_decode($row['value'], true);
				$displayName = ($current_user->language == 'vn_vn') ? $row['value']['name_vn'] : $row['value']['name_en'];
				$row['name'] = $displayName;
			}

			$menuItems[] = $row;
		}

		return $menuItems;
	}

	static function getMenuItemInfo($menuItemId) {
		global $adb;
		$sql = "SELECT * FROM vtiger_menu_items WHERE id = ?";
		$result = $adb->pquery($sql, [$menuItemId]);

		if ($result) {
			$info = $adb->fetchByAssoc($result);
			$info = decodeUTF8($info);

			if ($info['type'] != 'module') {
				$info['value'] = json_decode($info['value'], true);
			}

			return $info;
		}

		return [];
	}

	static function getSelectedModules($mainMenuId, $menuGroupId) {
		$menuItems = self::getMenuItemsByMenuGroup($mainMenuId, $menuGroupId);
		$selectedModules = [];

		foreach ($menuItems as $menuItem) {
			if ($menuItem['type'] == 'module') {
				$selectedModules[] = $menuItem['value'];
			}
		}

		return $selectedModules;
	}

	static function checkExistMainMenuName($name, $lang, $mainMenuId = '') {
		global $adb;
		$nameField = ($lang == 'vn') ? 'name_vn' : 'name_en';
		$sql = "SELECT 1 FROM vtiger_main_menus WHERE LOWER({$nameField}) = LOWER(?)";	// Modified by Vu Mai on 2023-02-21 to lower text to compare because main menu name is uppercase when display
		$params = [$name];

		if (!empty($mainMenuId)) {
			$sql .= " AND id != ?";
			$params[] = $mainMenuId;
		}

		$result = $adb->getOne($sql, $params);
		return $result == '1';
	}

	static function checkExistMenuGroupName($name, $lang, $mainMenuId, $menuGroupId = '') {
		global $adb;
		$nameField = ($lang == 'vn') ? 'name_vn' : 'name_en';
		$sql = "SELECT 1 FROM vtiger_menu_groups WHERE {$nameField} = ? AND main_menu_id = ?";
		$params = [$name, $mainMenuId];

		if (!empty($menuGroupId)) {
			$sql .= " AND id != ?";
			$params[] = $menuGroupId;
		}

		$result = $adb->getOne($sql, $params);
		return $result == '1';
	}

	static function checkExistMenuItemName($name, $lang, $menuGroupId, $menuItemId = '') {
		global $adb;
		$nameField = ($lang == 'vn') ? 'name_vn' : 'name_en';
		$sql = "SELECT 1 FROM vtiger_menu_items WHERE value LIKE ? AND menu_group_id = ?";
		$params = ['%"'. $nameField .'":"'. $name .'"%', $menuGroupId];

		if (!empty($menuItemId)) {
			$sql .= " AND id != ?";
			$params[] = $menuItemId;
		}

		$result = $adb->getOne($sql, $params);
		return $result == '1';
	}

	static function insertNewMainMenu(array $info) {
		global $adb;

		// Get next sequence number
		$sqlGetNextSeq = "SELECT MAX(sequence) + 1 FROM vtiger_main_menus";
		$nextSeq = $adb->getOne($sqlGetNextSeq, []);

		// Insert new row
		$sqlInsert = "INSERT INTO vtiger_main_menus(name_vn, name_en, color, icon, layout, sequence) VALUES (?, ?, ?, ?, ?, ?)";
		$params = [$info['name_vn'], $info['name_en'], $info['color'], $info['icon'], '2_columns', $nextSeq ?? 1];
		$adb->pquery($sqlInsert, $params);

		// Return new row id
		return $adb->getLastInsertID('vtiger_main_menus');
	}

	static function updateMainMenuInfo($mainMenuId, array $info) {
		global $adb;
		$sqlUpdate = "UPDATE vtiger_main_menus SET name_vn = ?, name_en = ?, color = ?, icon = ? WHERE id = ?";
		$params = [$info['name_vn'], $info['name_en'], $info['color'], $info['icon'], $mainMenuId];
		$adb->pquery($sqlUpdate, $params);
	}

	static function updateMainMenuLayout($mainMenuId, $layout) {
		global $adb;
		if (!in_array($layout, ['1_column', '2_columns', '3_columns'])) return;
		$adb->pquery('UPDATE vtiger_main_menus SET layout = ? WHERE id = ?', [$layout, $mainMenuId]);
	}

	static function deleteMainMenu($mainMenuId) {
		global $adb;
		$adb->pquery('DELETE FROM vtiger_main_menus WHERE id = ?', [$mainMenuId]);
		$adb->pquery('DELETE FROM vtiger_menu_groups WHERE main_menu_id = ?', [$mainMenuId]);
		$adb->pquery('DELETE FROM vtiger_menu_items WHERE main_menu_id = ?', [$mainMenuId]);
	}

	static function insertNewMenuGroup(array $info) {
		global $adb;

		// Get next sequence number
		$sqlGetNextSeq = "SELECT MAX(sequence) + 1 FROM vtiger_menu_groups WHERE main_menu_id = ?";
		$nextSeq = $adb->getOne($sqlGetNextSeq, [$info['main_menu_id']]);

		// Insert new row
		$sqlInsert = "INSERT INTO vtiger_menu_groups(name_vn, name_en, main_menu_id, sequence) VALUES (?, ?, ?, ?)";
		$params = [$info['name_vn'], $info['name_en'], $info['main_menu_id'], $nextSeq];
		$adb->pquery($sqlInsert, $params);

		// Return new row id
		return $adb->getLastInsertID('vtiger_menu_groups');
	}

	static function updateMenuGroup($menuGroupId, array $info) {
		global $adb;
		$sqlUpdate = "UPDATE vtiger_menu_groups SET name_vn = ?, name_en = ? WHERE id = ?";
		$params = [$info['name_vn'], $info['name_en'], $menuGroupId];
		$result = $adb->pquery($sqlUpdate, $params);
	}

	static function deleteMenuGroup($menuGroupId) {
		global $adb;
		$adb->pquery('DELETE FROM vtiger_menu_groups WHERE id = ?', [$menuGroupId]);
		$adb->pquery('DELETE FROM vtiger_menu_items WHERE menu_group_id = ?', [$menuGroupId]);
	}

	static function insertNewMenuItem(array $info) {
		global $adb;

		// Get next sequence number
		$sqlGetNextSeq = "SELECT MAX(sequence) + 1 FROM vtiger_menu_items WHERE main_menu_id = ?";
		$params = [$info['main_menu_id']];

		if (!empty($info['menu_group_id'])) {
			$sqlGetNextSeq .= " AND menu_group_id = ?";
			$params[] = $info['menu_group_id'];
		}
		
		$nextSeq = $adb->getOne($sqlGetNextSeq, $params);

		// Insert new row
		$sqlInsert = "INSERT INTO vtiger_menu_items(type, value, main_menu_id, menu_group_id, sequence) VALUES (?, ?, ?, ?, ?)";
		$params = [$info['type'], $info['type'] == 'module' ? $info['value'] : json_encode($info['value'], JSON_UNESCAPED_UNICODE), $info['main_menu_id'], $info['menu_group_id'], $nextSeq ?? 1]; // Modified By Vu Mai on 2023-03-13 to fix utf8 error format when encode json
		$adb->pquery($sqlInsert, $params);

		// Return new row id
		return $adb->getLastInsertID('vtiger_menu_items');
	}

	static function updateMenuItemInfo($menuItemId, array $info) {
		global $adb;
		$sqlUpdate = "UPDATE vtiger_menu_items SET value = ? WHERE id = ?";
		$params = [$info['type'] == 'module' ? $info['value'] : json_encode($info['value']), $menuItemId];
		$result = $adb->pquery($sqlUpdate, $params);
	}

	static function saveSelectedModulesMenuItem($mainMenuId, $menuGroupId, array $selectedModules) {
		global $adb;
		$selectedModules = escapeStringForSql($adb, $selectedModules);
		$currentModules = self::getSelectedModules($mainMenuId, $menuGroupId);

		// Delete removed menu items
		if (!empty($currentModules)) {
			$removedModules = array_diff($currentModules, $selectedModules);
			
			if (!empty($removedModules)) {
				$sql = "DELETE FROM vtiger_menu_items WHERE main_menu_id = ? AND menu_group_id = ? AND value IN ('". join("', '", $removedModules) ."')";
				$adb->pquery($sql, [$mainMenuId, $menuGroupId]);
			}
		}

		// Insert new meu items
		foreach ($selectedModules as $moduleName) {
			if (!in_array($moduleName, $currentModules)) {
				$info = [
					'type' => 'module',
					'value' => $moduleName,
					'main_menu_id' => $mainMenuId,
					'menu_group_id' => $menuGroupId,
				];

				self::insertNewMenuItem($info);
			}
		}
	}

	static function deleteMenuItem($menuItemId) {
		global $adb;
		$adb->pquery('DELETE FROM vtiger_menu_items WHERE id = ?', [$menuItemId]);
	}

	static function updateSequence($type, array $sequenceInfo) {
		global $adb;
		if (!in_array($type, ['main_menu', 'menu_group', 'menu_item'])) return;
		
		foreach ($sequenceInfo as $id => $sequence) {
			if ($type == 'main_menu') {
				$adb->pquery('UPDATE vtiger_main_menus SET sequence = ? WHERE id = ?', [$sequence, $id]);
			}
			else if ($type == 'menu_group') {
				$adb->pquery('UPDATE vtiger_menu_groups SET sequence = ? WHERE id = ?', [$sequence, $id]);
			}
			else if ($type == 'menu_item') {
				$adb->pquery('UPDATE vtiger_menu_items SET sequence = ? WHERE id = ?', [$sequence, $id]);
			}
		}
	}
}