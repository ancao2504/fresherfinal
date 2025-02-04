<?php

/*
	Class Settings_MenuEditor_Structure_Model
	Author: Hieu Nguyen
	Date: 2023-01-18
	Purpose: provide util function to get menu structure
*/

class Settings_MenuEditor_Structure_Model {

	// Modified by Vu Mai on 2023-02-02
	static function initCustomMenu() {
		global $adb, $chatBotConfig;
		$sqlCountMainMenus = "SELECT COUNT(id) FROM vtiger_main_menus";
		$mainMenusCount = $adb->getOne($sqlCountMainMenus, []);
		if ($mainMenusCount > 0) return;

		// Init main menus
		$colorList = ['#F17030', '#FFA600', '#35BF8E', '#9E579D' ,'#08B8B4', '#3D84A8', '#F47C7C', '#FFE501', '#9E579D', '#008ECF', '#DE425B', '#CD853F', '#FF6347', '#CD5C5C', '#FF69B4', '#DA70D6', '#9370DB', '#9ACD32', '#BDB76B'];
		$menusIconsMap = Vtiger_MenuStructure_Model::getAppIcons();
		$mainMenuIds = [];
		$index = 0;

		foreach ($menusIconsMap as $menuKey => $icon) {
			$info = [
				'name_vn' => vtranslate("LBL_{$menuKey}", 'Vtiger', 'vn_vn'),
				'name_en' => vtranslate("LBL_{$menuKey}", 'Vtiger', 'en_us'),
				'color' => $colorList[$index++],
				'icon' => 'fal ' . end(explode(' ', $icon)),
				'layout' => '2_columns',
			];

			$id = Settings_MenuEditor_Data_Model::insertNewMainMenu($info);
			$mainMenuIds[$menuKey] = $id;
		}

		// Insert modules into each coresponding main menu
		foreach ($mainMenuIds as $menuKey => $mainMenuId) {
			$sql = "SELECT t.name AS module_name FROM vtiger_tab AS t 
				INNER JOIN vtiger_app2tab AS a2t ON (a2t.tabid = t.tabid AND a2t.visible = 1 AND a2t.appname = ?)
				ORDER BY a2t.sequence";
			$result = $adb->pquery($sql, [$menuKey]);

			while ($row = $adb->fetchByAssoc($result)) {
				$info = [
					'type' => 'module',
					'value' => $row['module_name'],
					'main_menu_id' => $mainMenuId,
				];

				Settings_MenuEditor_Data_Model::insertNewMenuItem($info);
			}
		}

		// Process insert item to Chatbot main menu
		$sqlGetChatbotMainMenuId = "SELECT id FROM vtiger_main_menus WHERE name_en = 'CHATBOT'";
		$chatbotMainMenuId = $adb->getOne($sqlGetChatbotMainMenuId, []);
		$chatbotConfigSetting = Settings_Vtiger_Config_Model::loadConfig('chatbot_integration_config', true);
		$chatbotSubMenusItemTemplate = [
			[
				'name_vn' => 'Live Chat',
				'name_en' => 'Live Chat',
				'icon' => 'fal fa-comments',
			],
			[
				'name_vn' => 'Cấu hình Tự động hóa',
				'name_en' => 'Config Automation',
				'icon' => 'fal fa-folder-gear',
			],
			[
				'name_vn' => 'Huấn luyện Chatbot',
				'name_en' => 'Train Chatbot',
				'icon' => 'fal fa-robot',
			],
		];

		if ($chatbotConfigSetting['active_provider'] == 'Hana') {
			$chatbotSubMenu = [];
			$index = 0;

			foreach ($chatbotConfigSetting['chatbots'] as $bot) {
				$index++;

				$info = [
					'name_vn' => $bot['bot_name'],
					'name_en' => $bot['bot_name'],
					'main_menu_id' => $chatbotMainMenuId,
					'sequence' => $index,
				];

				$menuGroupId = Settings_MenuEditor_Data_Model::insertNewMenuGroup($info);

				foreach ($chatbotSubMenusItemTemplate as $item) {
					$info = [
						'type' => 'web_url',
						'value' => [
							'name_vn' => $item['name_vn'],
							'name_en' => $item['name_en'],
							'url' => 'index.php?module=CPChatBotIntegration&view=Iframe' . '&iframe_url=' .urlencode(base64_encode($chatBotConfig['hana']['chat_bot_iframe_url'] . $bot['bot_id'] . '/chats')) . '&custom_title=' . urlencode(base64_encode($item['name_vn'])),
							'open_in_new_tab' => 'false',
							'icon' => $item['icon'],
						],
						'main_menu_id' => $chatbotMainMenuId,
						'menu_group_id' => $menuGroupId,
					];

					$chatbotSubMenu[] = $info;
				}
			}
		}

		// Delete item
		$adb->pquery("DELETE FROM vtiger_menu_items WHERE main_menu_id = ?", [$chatbotMainMenuId]);

		// Insert item
		if (!empty($chatbotSubMenu)) {
			foreach ($chatbotSubMenu as $item) {
				Settings_MenuEditor_Data_Model::insertNewMenuItem($item);
			}
		}

		// Process insert item to Report main menu
		$sqlGetReportMainMenuId = "SELECT id FROM vtiger_main_menus WHERE name_en = 'REPORTS'";
		$reportMainMenuId = $adb->getOne($sqlGetReportMainMenuId, []);
		$reportsSubMenus = [
			[
				'name_vn' => 'Tất cả các báo cáo',
				'name_en' => 'All Reports',
				'icon' => 'fal fa-chart-bar',
			],
			[
				
				'name_vn' => 'Báo cáo bán hàng',
				'name_en' => 'Sales Reports',
				'icon' => 'fal fa-sack-dollar',
		
			],
			[
				'name_vn' => 'Báo cáo công nợ',
				'name_en' => 'Debit Reports',
				'icon' => 'fal fa-coin',
			],
			[
				
				'name_vn' => 'Báo cáo CSKH',
				'name_en' => 'Customer Care Reports',
				'icon' => 'fal fa-headset',
			],
			[
				'name_vn' => 'Báo cáo cuộc gọi',
				'name_en' => 'Call Center Reports',
				'icon' => 'fal fa-phone-office',
			],
			[
				'name_vn' => 'Báo cáo hoạt động',
				'name_en' => 'Activity Reports',
				'icon' => 'fal fa-calendar',
			],
			[
				'name_vn' => 'Báo cáo khách hàng',
				'name_en' => 'Customer Reports',
				'icon' => 'fal fa-user',
			],
			[
				'name_vn' => 'Báo cáo marketing',
				'name_en' => 'Marketing Reports',
				'icon' => 'fal fa-bullhorn',
			],
			[
				'name_vn' => 'Báo cáo quản trị',
				'name_en' => 'Management Reports',
				'icon' => 'fal fa-user-cog',
			],
			[
				'name_vn' => 'Báo cáo sản phẩm',
				'name_en' => 'Product Reports',
				'icon' => 'fal fa-box',
			],
			[
				'name_vn' => 'Báo cáo vận hành',
				'name_en' => 'Operation Reports',
				'icon' => 'fal fa-cogs',
			],
		];

		// Delete item
		$adb->pquery("DELETE FROM vtiger_menu_items WHERE main_menu_id = ?", [$reportMainMenuId]);

		// Insert item
		foreach ($reportsSubMenus as $item) {
			$sql = "SELECT folderid FROM vtiger_reportfolder WHERE foldername = ?";
			$folderId = $adb->getOne($sql, [$item['name_vn']]);


			if ($item['name_en'] == 'All Reports' || !empty($folderId)) {
				if (empty($folderId)) {
					$folderId = 'All';
				}

				$info = [
					'type' => 'web_url',
					'value' => [
						'name_vn' => $item['name_vn'],
						'name_en' => $item['name_en'],
						'url' => 'index.php?module=Reports&parent=&page=&view=List&viewname=' . $folderId . '&orderby=&sortorder=&tag_params=[]&nolistcache=0&list_headers=&tag=',
						'open_in_new_tab' => 'false',
						'icon' => $item['icon'],
					],
					'main_menu_id' => $reportMainMenuId,
					'menu_group_id' => null,
				];

				Settings_MenuEditor_Data_Model::insertNewMenuItem($info);
			}
		}

		// Process insert item to Config main menu
		$sqlGetConfigMainMenuId = "SELECT id FROM vtiger_main_menus WHERE name_en = 'CONFIG'";
		$configMainMenuId = $adb->getOne($sqlGetConfigMainMenuId, []);
		$configsSubMenus = [
			[
				'name_vn' => 'Tùy chọn cá nhân',
				'name_en' => 'Personal Settings',
				'url' => 'index.php?module=Users&view=PreferenceDetail&parent=Settings',
				'icon' => 'fal fa-user-gear',
			],
			[
				'name_vn' => 'Cấu hình Telesales',
				'name_en' => 'Telesales Config',
				'url' => 'index.php?module=CPTelesales&view=Config',
				'icon' => 'fal fa-user-headset',
			],
			[
				'name_vn' => 'Zalo Official Account',
				'name_en' => 'Zalo Official Account',
				'url' => 'index.php?module=CPSocialIntegration&view=ZaloOAConfig',
				'icon' => 'fa-thin fa-square-z',
			],
		];

		// Delete item
		$adb->pquery("DELETE FROM vtiger_menu_items WHERE main_menu_id = ?", [$configMainMenuId]);

		// Insert item
		foreach ($configsSubMenus as $item) {
			$info = [
				'type' => 'web_url',
				'value' => [
					'name_vn' => $item['name_vn'],
					'name_en' => $item['name_en'],
					'url' => $item['url'],
					'open_in_new_tab' => 'false',
					'icon' => $item['icon'],
				],
				'main_menu_id' => $configMainMenuId,
				'menu_group_id' => null,
			];

			Settings_MenuEditor_Data_Model::insertNewMenuItem($info);
		}
	}

	static function getDisplayStructure() {
		global $adb, $current_user;
		$sql = "SELECT id, name_vn, name_en, color, icon, layout FROM vtiger_main_menus ORDER BY sequence";
		$result = $adb->pquery($sql, []);
		$mainMenus = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);
			$displayName = ($current_user->language == 'vn_vn') ? $row['name_vn'] : $row['name_en'];

			$mainMenus[] = [
				'name' => $displayName,
				'color' => $row['color'],
				'icon' => $row['icon'],
				'layout' => $row['layout'],
				'children' => self::getMainMenuDetailsForDisplay($row['id']),
				'items_menu_count' => Settings_MenuEditor_Data_Model::getMenuItemsCountByMainMenu($row['id']),	// Added by Vu Mai on 2022-02-06
			];
		}

		return $mainMenus;
	}

	// Added by Vu Mai on 2022-02-08 get item menu for modnavigator by main_menu_id and menu_group_id
	static function getModuleNavicatorStructure($mainMenuId, $menuGroupId) {
		if (empty($mainMenuId) && empty($menuGroupId)) return;

		if ($menuGroupId == 'uncategorized') {
			$menuItems = self::getMenuItemsForDisplay($mainMenuId, 'main_menu');
		}
		else {
			$menuItems = self::getMenuItemsForDisplay($menuGroupId, 'menu_group');
		}

		return $menuItems;
	}

	protected static function getMainMenuDetailsForDisplay($mainMenuId) {
		global $adb, $current_user;
		// Modified by Vu Mai on 2023-03-09. Don't get group menu no have item
		$sqlGetMenuGroups = "SELECT * FROM vtiger_menu_groups 
			WHERE main_menu_id = ?
			AND (SELECT COUNT(id) FROM vtiger_menu_items WHERE menu_group_id = vtiger_menu_groups.id)
			ORDER BY sequence";
		// End Vu Mai
		$result = $adb->pquery($sqlGetMenuGroups, [$mainMenuId]);
		$details = [];

		// Found groups belong to the main menu, fetch group name and its menu items
		if ($adb->num_rows($result) > 0) {
			while ($row = $adb->fetchByAssoc($result)) {
				$row = decodeUTF8($row);
				$displayName = ($current_user->language == 'vn_vn') ? $row['name_vn'] : $row['name_en'];
				$details[$displayName] = self::getMenuItemsForDisplay($row['id'], 'menu_group');
			}
		}
		// No group found, fetch all menu items belongs to the main menu
		else {
			$allMenuItems = self::getMenuItemsForDisplay($mainMenuId, 'main_menu');

			if (!empty($allMenuItems)) {
				$details['uncategorized'] = $allMenuItems;
			}
		}

		return $details;
	}

	protected static function getMenuItemsForDisplay($parentId, $parentType) {
		global $adb, $current_user, $moduleIcons;
		$parentField = ($parentType == 'main_menu') ? 'main_menu_id' : 'menu_group_id';
		$sqlGetMenuGroups = "SELECT * FROM vtiger_menu_items WHERE {$parentField} = ? ORDER BY sequence";
		$result = $adb->pquery($sqlGetMenuGroups, [$parentId]);
		$menuItems = [];

		// Modified by Vu Mai on 2022-02-08 to add menu_group_id and menu_item_id to param
		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);
			$menuGroupId = !empty($row['menu_group_id']) ? $row['menu_group_id'] : 'uncategorized';

			if ($row['type'] == 'module') {
				$moduleName = $row['value'];

				if (isForbiddenFeature("Module{$moduleName}") || isHiddenModule($moduleName)) {
					continue;
				}

				$displayName = vtranslate($moduleName, $moduleName, $current_user->language == 'vn_vn' ? 'vn_vn' : 'en_us');
				$row['name'] = $displayName;
				$row['icon'] = $moduleIcons[$moduleName] ?? $moduleIcons['Default'];
				$row['url'] = "index.php?module={$moduleName}&view=List&menu_id=" . $row['main_menu_id'] . '&menu_group_id=' . $menuGroupId . '&menu_item_id=' . $row['id'];
			
				if ($moduleName == 'CPKanban') {
					$row['url'] .= '&source_module=Calendar';
				}
			}
			else {
				$row['value'] = json_decode($row['value'], true);
				$displayName = ($current_user->language == 'vn_vn') ? $row['value']['name_vn'] : $row['value']['name_en'];
				$row['name'] = $displayName;
				unset($row['value']['name_vn']);
				unset($row['value']['name_en']);

				if ($row['type'] == 'report') {
					$row['value']['url'] = 'index.php?module=Reports&view=Detail&record=' . $row['value']['report_id'] . '&menu_id=' . $row['main_menu_id'] . '&menu_group_id=' . $menuGroupId . '&menu_item_id=' . $row['id'];
				}
			}

			unset($row['main_menu_id']);
			unset($row['menu_group_id']);
			unset($row['sequence']);
			$menuItems[] = $row;
		}
		// End Vu Mai

		return $menuItems;
	}
}