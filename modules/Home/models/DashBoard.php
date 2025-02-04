<?php

/**
 * Name: Dashboard.php
 * Author: Phu Vo
 * Date: 2020.10.12
 */

class Home_DashBoard_Model extends Vtiger_DashBoard_Model {
    static $module = 'Home';

    public static function getDashboardPermissions() {
        return [
            '' => '',
            'Read Only' => vtranslate('LBL_DASHBOARD_READ_ONLY', self::$module),
            'Full Access' => vtranslate('LBL_DASHBOARD_FULL_ACCESS', self::$module),
        ];
    }

    public static function getDashboardStatuses() {
        return [
            '' => '',
            'Active' => vtranslate('LBL_DASHBOARD_ACTIVE', self::$module),
            'Inactive' => vtranslate('LBL_DASHBOARD_INACTIVE', self::$module),
        ];
    }

    public static function getLanguageNameFieldMapping() {
        return [
            'en_us' => 'name_en',
            'vn_vn' => 'name_vn',
        ];
    }

    public static function getLanguageNameField() {
        global $current_user;

        $nameFieldMapping = Home_DashBoard_Model::getLanguageNameFieldMapping();
        $nameField = $nameFieldMapping[$current_user->language];

        return $nameField;
    }

    public static function saveDashboardTemplate(&$data) {
        global $adb;

        $templateId = $data['id'];
        $sql = null;
        $queryParams = [];

        if (!is_array($data['roles'])) $data['roles'] = [$data['roles']];
        $data['roles'] = Vtiger_Multipicklist_UIType::encodeValues($data['roles']);

        if (empty($templateId)) { // Create new
            $sql = "INSERT INTO vtiger_dashboard_templates (name, status, roles, permission) VALUES (?, ?, ?, ?)";
            $queryParams = [$data['name'], $data['status'], $data['roles'], $data['permission']];
        } else { // Update existed
            $sql = "UPDATE vtiger_dashboard_templates SET name = ?, status = ?, roles = ?, permission = ? WHERE id = ?";
            $queryParams = [$data['name'], $data['status'], $data['roles'], $data['permission'], $templateId];
        }

        $adb->pquery($sql, $queryParams);

        if (empty($templateId)) {
            $sql = "SELECT * FROM vtiger_dashboard_templates WHERE id = (SELECT LAST_INSERT_ID())";
            $result =  $adb->pquery($sql);
            $data = decodeUTF8($adb->fetchByAssoc($result));
        }

        return $data;
    }

    public static function deleteDashboardTemplate($templateId) {
        global $adb;

        if (empty($templateId)) return;

        // Delete template tab and widget
        $sql = "DELETE FROM vtiger_module_dashboard_widgets WHERE dashboardtabid > 0 AND dashboardtabid IN (SELECT id FROM vtiger_dashboard_tabs WHERE dashboard_template_id = ?)";
        $adb->pquery($sql, [$templateId]);

        $sql = "DELETE FROM vtiger_dashboard_tabs WHERE dashboard_template_id > 0 AND dashboard_template_id = ?";
        $adb->pquery($sql, [$templateId]);

        // Delete template itself
        $sql = "DELETE FROM vtiger_dashboard_templates WHERE id > 0 AND id = ?";
        $adb->pquery($sql, [$templateId]);
    }

    public static function getDashboardTemplates(&$totalCount = null, $filters = [], $paging = []) {
        global $adb;

        $sql = "SELECT * FROM vtiger_dashboard_templates WHERE 1 = 1 ";
        $totalSql = "SELECT COUNT(id) FROM vtiger_dashboard_templates WHERE 1 = 1 ";
        $conditionSql = "";
        $pagingSql = "";
        $queryParams = [];

        // Filtering
        if (!empty($filters['name'])) {
            $conditionSql .= " AND name LIKE ? ";
            $queryParams[] = "%{$filters['name']}%";
        }

        // Paging
        if (!empty($paging)) {
            if (!empty($paging['offset'])) $pagingSql .= " LIMIT {$paging['offset']}";
            if (!empty($paging['limit'])) $pagingSql .= " , {$paging['limit']} ";
        }

        // Sorting
        $pagingSql .= " ORDER BY name ASC ";

        // Build query
        $sql = $sql . $conditionSql . $pagingSql;
        $totalSql = $totalSql . $conditionSql;

        $result = $adb->pquery($sql, $queryParams);
        $totalCount = $adb->getOne($sql, $queryParams);
        $data = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            if (!empty($row['roles'])) $row['roles'] =  Vtiger_Multipicklist_UIType::decodeValues($row['roles']);

            $data[] = $row;
        }

        return $data;
    }

    public static function getDashboardTemplateById($templateId) {
        global $adb;

        $sql = "SELECT * FROM vtiger_dashboard_templates WHERE id = ? LIMIT 1";
        $result = $adb->pquery($sql, [$templateId]);
        $result = $adb->fetchByAssoc($result);
        if (!empty($result['roles'])) $result['roles'] = Vtiger_Multipicklist_UIType::decodeValues($result['roles']);

        $result = decodeUTF8($result);

        return $result ?? [];
    }

    public static function saveWidgetCategory($data) {
        global $adb;

        $categoryId = $data['id'];
        $sql = '';
        $queryParams = [];

        if (empty($categoryId)) {
            $sql = "INSERT INTO vtiger_widget_categories (name_en, name_vn) VALUES (?, ?)";
            $queryParams = [$data['name_en'], $data['name_vn']];
        } else {
            $sql = "UPDATE vtiger_widget_categories SET name_en = ?, name_vn = ? WHERE id = ?";
            $queryParams = [$data['name_en'], $data['name_vn'], $categoryId];
        }

        $adb->pquery($sql, $queryParams);

        if (empty($categoryId)) {
            $result = $adb->pquery('SELECT * FROM vtiger_widget_categories WHERE id = (SELECT LAST_INSERT_ID())');
            $data = $adb->fetchByAssoc($result);
        }

        $data = decodeUTF8($data);

        return $data;
    }

    public static function deleteCategoryAndRelatedWidgets($categoryId) {
        global $adb;

        $adb->pquery("DELETE FROM vtiger_widget_categories_widgets WHERE category_id > 0 AND category_id = ?", [$categoryId]);
        $adb->pquery("DELETE FROM vtiger_widget_categories WHERE id > 0 AND id = ?", [$categoryId]);
    }

    public static function removeWidgetFromCategoryById($widgetId, $categoryId) {
        global $adb;

        // Support remove all
        if ($widgetId == 'all') {
            $adb->pquery("DELETE FROM vtiger_widget_categories_widgets WHERE category_id = ?", [$categoryId]);
        }
        else {
            $adb->pquery("DELETE FROM vtiger_widget_categories_widgets WHERE widget_id = ? AND category_id = ?", [$widgetId, $categoryId]);
        }
    }

    public static function getWidgetCategoryById($categoryId) {
        global $adb;

        $sql = "SELECT * FROM vtiger_widget_categories WHERE id = ? LIMIT 1";
        $result = $adb->pquery($sql, [$categoryId]);
        $result = $adb->fetchByAssoc($result);

        return $result;
    }

    public static function addWidgetToCategory($categoryId, $widgetId, $relationType) {
        global $adb;

        $sql = "INSERT INTO vtiger_widget_categories_widgets (category_id, widget_id, type) VALUES (?, ?, ?)";
        $adb->pquery($sql, [$categoryId, $widgetId, $relationType]);
    }

    public static function getWidgetCategories(&$totalCount = 0, $filters = [], $paging = []) {
        global  $adb;

        $nameField = Home_DashBoard_Model::getLanguageNameField();

        $sql = "SELECT * FROM vtiger_widget_categories WHERE 1 = 1 ";
        $totalSql = "SELECT COUNT(id) FROM vtiger_widget_categories WHERE 1 = 1 ";
        $conditionSql = "";
        $pagingSql = "";
        $queryParams = [];

        // Filtering
        if (!empty($filters['name_en'])) {
            $conditionSql .= " AND name_en LIKE ? ";
            $queryParams[] = "%{$filters['name_en']}%";
        }
        if (!empty($filters['name_vn'])) {
            $conditionSql .= " AND name_vn LIKE ? ";
            $queryParams[] = "%{$filters['name_vn']}%";
        }

        // Paging
        if (!empty($paging)) {
            $pagingSql .= " LIMIT {$paging['offset']}, {$paging['limit']} ";
        }

        // Sorting
        if (!empty($filters['name_en'])) {
            $pagingSql .= " ORDER BY name_en ASC ";
        } else if (!empty($filters['name_vn'])) {
            $pagingSql .= " ORDER BY name_vn ASC ";
        }

        // Build query
        $sql = $sql . $conditionSql . $pagingSql;
        $totalSql = $totalSql . $conditionSql;

        $result = $adb->pquery($sql, $queryParams);
        $totalCount = $adb->getOne($sql, $queryParams);
        $data = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['name'] = $row[$nameField];
            $data[] = $row;
        }

        return $data;
    }

    // Refactored by Hieu Nguyen on 2022-10-10
    public static function getWidgets(&$totalCount = 0, $filters = [], $paging = [], $excludeCategoryId = null) {
        global $adb, $hiddenModules;
        $unsupportedModules = array_merge($hiddenModules, getForbiddenFeatures('module'));

        $translateLabel = [
            'Default' => 'LBL_DEFAULT',
            'Report' => 'LBL_REPORT',
        ];

        $excludeCategorySql = "";
        $queryParamsSqlGetWidgets = [];
        $queryParamsSqlGetReports = [];

        if ($excludeCategoryId) {
            $excludeCategorySql = "AND vtiger_widget_categories_widgets.category_id = ?";
            $queryParamsSqlGetWidgets[] = $excludeCategoryId;
            $queryParamsSqlGetReports[] = $excludeCategoryId;
        }

        $sqlGetWidgets = "SELECT DISTINCT vtiger_links.linkid AS id,
                vtiger_links.linklabel AS name,
                CASE WHEN vtiger_links.linklabel = 'Mini List' THEN 'Mini List' ELSE 'Default' END AS type,
                vtiger_tab.name AS module,
                vtiger_widget_categories_widgets.category_id,
                'Widget' AS category_type,
                vtiger_links.primary_module
            FROM vtiger_links
            INNER JOIN vtiger_tab ON (vtiger_tab.tabid = vtiger_links.tabid)
            LEFT JOIN vtiger_widget_categories_widgets ON (vtiger_widget_categories_widgets.widget_id = vtiger_links.linkid {$excludeCategorySql} AND vtiger_widget_categories_widgets.type = 'Widget')
            WHERE vtiger_links.linktype = 'DASHBOARDWIDGET' AND vtiger_links.linklabel NOT IN ('Mini List', 'Notebook') ";

        $sqlGetReports = "SELECT DISTINCT vtiger_report.reportid AS id,
                vtiger_report.reportname AS name,
                'Report' AS type,
                'Reports' AS module,
                vtiger_widget_categories_widgets.category_id,
                'Report' AS category_type,
                vtiger_reportmodules.primarymodule AS primary_module
            FROM vtiger_report
            INNER JOIN vtiger_reportfolder ON (vtiger_reportfolder.folderid = vtiger_report.folderid)
            INNER JOIN vtiger_reportmodules ON (vtiger_reportmodules.reportmodulesid = vtiger_report.reportid)
            LEFT JOIN vtiger_widget_categories_widgets ON (vtiger_widget_categories_widgets.widget_id = vtiger_report.reportid {$excludeCategorySql} AND vtiger_widget_categories_widgets.type = 'Report')
            WHERE (vtiger_report.reporttype = 'chart' OR vtiger_report.has_chart = 1) ";

        $conditionSqlGetWidgets = "";
        $conditionSqlGetReports = "";
        $pagingSql = "";

        // Include only module Home widget
        $homeModuleModel = Vtiger_Module_Model::getInstance('Home');
        $sqlGetWidgets .= "AND vtiger_links.tabid = ? ";
        $queryParamsSqlGetWidgets[] = $homeModuleModel->getId();

        // Filtering
        if (!empty($filters['name'])) {
            $conditionSqlGetWidgets .= "AND vtiger_links.linklabel LIKE ? ";
            $queryParamsSqlGetWidgets[] = "%{$filters['name']}%";
            $conditionSqlGetReports .= "AND vtiger_report.reportname LIKE ? ";
            $queryParamsSqlGetReports[] = "%{$filters['name']}%";
        }

        if (!empty($filters['category_id'])) {
            $conditionSqlGetWidgets .= "AND category_id = ? ";
            $queryParamsSqlGetWidgets[] = $filters['category_id'];
            $conditionSqlGetReports .= "AND category_id = ? ";
            $queryParamsSqlGetReports[] = $filters['category_id'];
        }

        if (!empty($unsupportedModules)) {
            $unsupportedModulesStr = join("', '", $unsupportedModules);
            $conditionSqlGetWidgets .= "AND vtiger_links.primary_module NOT IN ('". $unsupportedModulesStr ."') ";
            $conditionSqlGetReports .= "AND vtiger_reportmodules.primarymodule NOT IN ('". $unsupportedModulesStr ."') ";
        }

        if (!isForbiddenFeature('PinChartToDashboard')) {
            $unsupportedFolders = getForbiddenReportFolders();

            if (!empty($unsupportedFolders)) {
                $conditionSqlGetReports .= "AND vtiger_reportfolder.code NOT IN ('". join("', '", $unsupportedFolders) ."') ";
            }
        }

        // Paging
        if (!empty($paging)) {
            $pagingSql .= " LIMIT {$paging['offset']}, {$paging['limit']}";
        }

        // Build query
        $sqlGetWidgets = $sqlGetWidgets . $conditionSqlGetWidgets ;
        $sqlGetReports = $sqlGetReports . $conditionSqlGetReports ;

        if (!isForbiddenFeature('PinChartToDashboard')) {
            $sql = "SELECT widget.* FROM ({$sqlGetWidgets} UNION ALL {$sqlGetReports}) AS widget WHERE 1 = 1 {$pagingSql}";
            $totalSql = "SELECT COUNT(widget.id) FROM ({$sqlGetWidgets} UNION ALL {$sqlGetReports}) AS widget WHERE 1 = 1";
            $queryParams = array_merge($queryParamsSqlGetWidgets, $queryParamsSqlGetReports);
        }
        else {
            $sql = "SELECT widget.* FROM ({$sqlGetWidgets}) AS widget WHERE 1 = 1 {$pagingSql}";
            $totalSql = "SELECT COUNT(widget.id) FROM ({$sqlGetWidgets}) AS widget WHERE 1 = 1";
            $queryParams = $queryParamsSqlGetWidgets;
        }

        $result = $adb->pquery($sql, $queryParams);
        $totalCount = $adb->getOne($totalSql, $queryParams);
        $data = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            if (!empty($row['category_id'] && $row['category_id'] == $excludeCategoryId)) {
                $totalSql -= 1;
                continue;
            }

            $row['name'] = vtranslate($row['name'], $row['module']);
            $row['primary_module'] = vtranslate($row['primary_module'], $row['primary_module']);
            $row['type'] = !empty($translateLabel[$row['type']]) ? vtranslate($translateLabel[$row['type']], 'Home') : $row['type'];
            $data[] = $row;
        }

        return $data;
    }

    public static function checkDuplicateDashboardTemplate($keyword, $excludeTemplateId = null) {
        global $adb;

        $sql = "SELECT 1 FROM vtiger_dashboard_templates WHERE name = ? ";
        $queryParams = [$keyword];

        if (!empty($excludeTemplateId)) {
            $sql .= "AND id <> ? ";
            $queryParams[] = $excludeTemplateId;
        }

        $sql .= "LIMIT 1";

        return $adb->getOne($sql, $queryParams);
    }

    public static function checkDuplicateRolesInDashboardTemplate($roles, $excludeCategoryId = null) {
        global $adb;

        $duplicatedRoles = [];

        if (empty($roles)) return $duplicatedRoles;
        if (!is_array($roles)) $roles = [$roles];

        $sql = "SELECT DISTINCT roles FROM vtiger_dashboard_templates WHERE (1 = 0 ";
        $queryParams = [];

        foreach ($roles as $index => $role) {
            $sql .= " OR FIND_IN_SET(?, REPLACE(roles, '\ |##|\ ', ',')) > 0 ";
            $queryParams[] = $role;
        }

        $sql .= ")";

        if (!empty($excludeCategoryId)) {
            $sql .= " AND id <> ?";
            $queryParams[] = $excludeCategoryId;
        }

        $result = $adb->pquery($sql, $queryParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $roleIds = Vtiger_Multipicklist_UIType::decodeValues($row['roles']);

            foreach ($roleIds as $roleId) {
                if (in_array($roleId, $roles) && !in_array($roleId, $duplicatedRoles)) {
                    $duplicatedRoles[] = $roleId;
                }
            }
        }

        return $duplicatedRoles;
    }

    // Refactored by Hieu Nguyen on 2022-10-10
	public function getSelectableWidgets() {
		global $adb, $current_user, $hiddenModules;
		$homeModuleModel = $this->getModule();
        $unsupportedModules = array_merge($hiddenModules, getForbiddenFeatures('module'));
        $currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModels = [];

        // Get native widgets
		$sqlGetWidgets = "SELECT DISTINCT
                vtiger_links.*,
				vtiger_widget_categories_widgets.category_id,
				vtiger_widget_categories.name_en AS category_name_en,
				vtiger_widget_categories.name_vn AS category_name_vn,
				vtiger_tab.name AS module
			FROM vtiger_links
			INNER JOIN vtiger_tab ON (vtiger_tab.tabid = vtiger_links.tabid)
			LEFT JOIN vtiger_widget_categories_widgets ON (vtiger_widget_categories_widgets.widget_id = vtiger_links.linkid AND vtiger_widget_categories_widgets.type = 'Widget')
			LEFT JOIN vtiger_widget_categories ON (vtiger_widget_categories_widgets.category_id = vtiger_widget_categories.id)
			WHERE vtiger_links.linktype = 'DASHBOARDWIDGET' AND vtiger_links.tabid = ? ";

        if (!empty($unsupportedModules)) {
            $sqlGetWidgets .= "AND vtiger_links.primary_module NOT IN ('". join("', '", $unsupportedModules) ."') ";
        }

        $sqlGetWidgets .= "OR vtiger_links.linklabel IN ('Mini List', 'Notebook') ";

		$params = [$homeModuleModel->getId()];
		$result = $adb->pquery($sqlGetWidgets, $params);
        $widgets = [];

		while ($row = $adb->fetchByAssoc($result)) {
			if ($row['linklabel'] == 'Tag Cloud') {
                $isTagCloudExists = getTagCloudView($current_user->id);
				if ($isTagCloudExists == 'false') continue;
			}

            // Get primary module model
            if (!isset($moduleModels[$row['primary_module']])) {
                $moduleModels[$row['primary_module']] = Vtiger_Module_Model::getInstance($row['primary_module'] ?? 'Home');
                if (empty($moduleModels[$row['primary_module']])) continue;
            }

            // Check permission for primary module
            $moduleModel = $moduleModels[$row['primary_module']];

            if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
                continue;
            }

            // Check permission for module in widget url
			if (!$this->checkModulePermission($row)) {
				continue;
			}

            $widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
		}

        // Get chart report widgets
        if (!isForbiddenFeature('PinChartToDashboard')) {
		    $unsupportedFolders = getForbiddenReportFolders();

            $sqlGetReports = "SELECT DISTINCT
                    vtiger_report.*,
                    vtiger_widget_categories_widgets.category_id,
                    vtiger_widget_categories.name_en AS category_name_en,
                    vtiger_widget_categories.name_vn AS category_name_vn,
                    'Reports' AS module,
                    vtiger_reportmodules.primarymodule AS primary_module
                FROM vtiger_report
                INNER JOIN vtiger_reportfolder ON (vtiger_reportfolder.folderid = vtiger_report.folderid)
                INNER JOIN vtiger_reportmodules ON (vtiger_reportmodules.reportmodulesid = vtiger_report.reportid)
                LEFT JOIN vtiger_widget_categories_widgets ON (vtiger_widget_categories_widgets.widget_id = vtiger_report.reportid AND vtiger_widget_categories_widgets.type = 'Report')
                LEFT JOIN vtiger_widget_categories ON (vtiger_widget_categories_widgets.category_id = vtiger_widget_categories.id)
                WHERE (vtiger_report.reporttype = 'chart' OR vtiger_report.has_chart = 1) ";

            if (!empty($unsupportedModules)) {
                $sqlGetReports .= "AND vtiger_reportmodules.primarymodule NOT IN ('". join("', '", $unsupportedModules) ."') ";
            }

            if (!empty($unsupportedFolders)) {
                $sqlGetReports .= "AND vtiger_reportfolder.code NOT IN ('". join("', '", $unsupportedFolders) ."') ";
            }
            
            $params = [];
            $result = $adb->pquery($sqlGetReports, $params);

            while ($row = $adb->fetchByAssoc($result)) {
                // Get primary module model
                if (!isset($moduleModels[$row['primary_module']])) {
                    $moduleModels[$row['primary_module']] = Vtiger_Module_Model::getInstance($row['primary_module']);
                    if (empty($moduleModels[$row['primary_module']])) continue;
                }

                // Check permission for primary module
                $moduleModel = $moduleModels[$row['primary_module']];

                if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
                    continue;
                }
                
                $widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
            }
        }

        return $widgets;
	}
}