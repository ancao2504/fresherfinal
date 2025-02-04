<?php

/*
	DashboardLogic_Helper
	Author: Hieu Nguyen
	Date: 2020
	Purpose: to provide util functions dashboard management
*/

class Home_DashboardLogic_Helper {

    public static function isDashboardTabExists($tabId) {
        global $adb, $current_user;
        if (empty($tabId)) return;

        $sql = "SELECT 1 FROM vtiger_dashboard_tabs WHERE id = ? AND userid = ?";
        $result = $adb->getOne($sql, [$tabId, $current_user->id]);
        return !empty($result);
    }

    private static function hasActiveTemplateForRole($role) {
        global $adb;
        if (empty($role)) return;

        $sql = "SELECT 1 FROM vtiger_dashboard_templates WHERE status = 'Active' AND FIND_IN_SET(?, REPLACE(roles, ' |##| ', ',')) > 0";
        $result = $adb->getOne($sql, [$role]);
        return !empty($result);
    }

    private static function emptyUserDashboardData($userId) {
        global $adb;
        if (empty($userId)) return;
        $adb->pquery("DELETE FROM vtiger_dashboard_tabs WHERE userid = ? AND type != 'template'", [$userId]);
        $adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE userid = ? AND type != 'template'", [$userId]);
    }

    private static function applyTemplateToUser($userId, $role) {
        global $adb;
        if (empty($userId) || empty($role)) return;
        
        // Remove current dashboard data first
        self::emptyUserDashboardData($userId);

        // Add new dashboard tab from template that matches user's role
        $sql = "INSERT INTO vtiger_dashboard_tabs(name_en, name_vn, isdefault, sequence, appname, modulename, userid, dashboard_template_id, type)
            SELECT tab.name_en, tab.name_vn, tab.isdefault, tab.sequence, tab.appname, tab.modulename, ? AS userid, tab.id AS dashboard_template_id, 'copy' AS type 
            FROM vtiger_dashboard_tabs AS tab
            INNER JOIN vtiger_dashboard_templates AS tem ON (tem.id = tab.dashboard_template_id AND FIND_IN_SET(?, REPLACE(tem.roles, ' |##| ', ',')) > 0)";
        $adb->pquery($sql, [$userId, $role]);

        // Add new dashboard widgets that belong to the newly added dashboard tabs
        $sql = "INSERT INTO vtiger_module_dashboard_widgets(linkid, userid, filterid, name_en, name_vn, data, position, reportid, dashboardtabid, size, type)
            SELECT wid.linkid, tab.userid, wid.filterid, wid.name_en, wid.name_vn, wid.data, wid.position, wid.reportid, tab.id AS dashboardtabid, wid.size, 'copy' AS type
            FROM vtiger_module_dashboard_widgets AS wid
            INNER JOIN vtiger_dashboard_tabs AS tab ON (tab.userid = ? AND tab.type = 'copy' AND tab.dashboard_template_id = wid.dashboardtabid)";
        $adb->pquery($sql, [$userId]);
    }

    static function applyTemplateToSpecificUser($userId, $role) {
        if (empty($userId) || empty($role)) return;
        if (!self::hasActiveTemplateForRole($role)) return;

        self::applyTemplateToUser($userId, $role);
    }

    static function applyTemplateToUsers($templateId) {
        global $adb;
        if (empty($templateId)) return;
        
        // Get affected roles
        $sql = "SELECT roles FROM vtiger_dashboard_templates WHERE id = ? AND status = 'Active'";
        $affectedRolesString = $adb->getOne($sql, [$templateId]);
        if (empty($affectedRolesString)) return;
        
        $affectedRoles = Vtiger_Multipicklist_UIType::decodeValues($affectedRolesString);
        if (empty($affectedRoles)) return;

        // Get affected users
        $affectedUsers = getUserIdsFromRoles($affectedRoles);

        // Apply selected template to specific users
        foreach ($affectedUsers as $roleId => $userIds) {
            foreach ($userIds as $userId) {
                self::applyTemplateToUser($userId, $roleId);
            }
        }
    }

    static function emptyDashboardTabWidget($tabId, $userId) {
        global $adb;

        if (empty($tabId) || empty($userId)) return;
        return $adb->pquery("DELETE FROM vtiger_module_dashboard_widgets WHERE dashboardtabid = ? AND userid = ?", [$tabId, $userId]);
    }

    /** Implemented by Phu Vo 2020.10.12 */
    static function groupDashboardWidgetByCategories(array $widgets) {
        $nameField = "category_" . Home_DashBoard_Model::getLanguageNameField();
        $groupedWidgets = [];
        $result = [];

        $utiltyWidgets = [
            'id' => 'utilities',
            'name' => vtranslate('LBL_DASHBOARD_UTILITIES', 'Home'),
            'widgets' => [],
        ];

        $uncategoryWidgets = [
            'id' => 'uncategories',
            'name' => vtranslate('LBL_DASHBOARD_UNCATEGORIES', 'Home'),
            'widgets' => [],
        ];

        foreach ($widgets as $index => $widgetModal) {
            $categoryId = $widgetModal->get('category_id');

            if ($widgetModal->getName() == 'MiniList' || $widgetModal->getName() == 'Notebook') {
                $utiltyWidgets['widgets'][] = $widgetModal;
                continue;
            }

            if (empty($categoryId)) {
                $uncategoryWidgets['widgets'][] = $widgetModal;
            }

            if (!empty($categoryId) && empty($groupedWidgets[$categoryId])) {
                $groupedWidgets[$categoryId] = [
                    'id' => $categoryId,
                    'name' => $widgetModal->get($nameField),
                    'widgets' => [],
                ];
            }

            if (!empty($categoryId)) {
                $groupedWidgets[$categoryId]['widgets'][] = $widgetModal;
            }
        }

        $groupedWidgets = array_merge([$utiltyWidgets], [$uncategoryWidgets], $groupedWidgets);

        // Filter only not empty category
        foreach ($groupedWidgets as $category) {
            if (!empty($category['widgets'])) $result[] = $category;
        }

        return $result;
    }

    /** Implemented by Phu Vo on 2020.10.20 */
    static function copyTemplateLayout($sourceTemplateId, $destinationTemplateId) {
        global $adb, $current_user;
        
        if (empty($sourceTemplateId) || empty($destinationTemplateId)) return;

        $sql = "INSERT INTO vtiger_dashboard_tabs (name_en, name_vn, isdefault, sequence, appname, modulename, userid, dashboard_template_id, type)
            SELECT tab.name_en, tab.name_vn, tab.isdefault, tab.sequence, tab.appname, tab.modulename, ? AS userid, ? AS dashboard_template_id, 'template' AS type
            FROM vtiger_dashboard_tabs AS tab
            INNER JOIN vtiger_dashboard_templates AS tem ON (tem.id = tab.dashboard_template_id)
            WHERE tem.id = ?";
        $adb->pquery($sql, [$current_user->id, $destinationTemplateId, $sourceTemplateId]);

        $sql = "INSERT INTO vtiger_module_dashboard_widgets (linkid, userid, filterid, name_en, name_vn, data, position, reportid, dashboardtabid, size, type)
            SELECT wid.linkid, ? AS userid, wid.filterid, wid.name_en, wid.name_vn, wid.data, wid.position, wid.reportid, des_tab.id AS dashboardtabid, wid.size, wid.type AS type
            FROM vtiger_module_dashboard_widgets AS wid
            INNER JOIN vtiger_dashboard_tabs AS src_tab ON (src_tab.id = wid.dashboardtabid)
            INNER JOIN vtiger_dashboard_tabs AS des_tab ON (des_tab.dashboard_template_id = ? AND des_tab.name_en = src_tab.name_en AND des_tab.name_vn = src_tab.name_vn)
            WHERE src_tab.dashboard_template_id = ?";
        $adb->pquery($sql, [$current_user->id, $destinationTemplateId, $sourceTemplateId]);
    }

    /** Implemented by Phu Vo on 2020.10.26 */
    static function canEditDashboard() {
        global $adb;

        if ($_SESSION['dashboard_edit_mode'] == true) return true;

        $currentUser = Users_Record_Model::getCurrentUserModel();

        // Get user template and check permission from there
        $userRole = $currentUser->getRole();
        $sql = "SELECT permission FROM vtiger_dashboard_templates WHERE FIND_IN_SET(?, REPLACE(roles, '\ |##|\ ', ',')) > 0 LIMIT 1";
        $permission = $adb->getOne($sql, [$userRole]);

        if ($permission == 'Full Access') return true;
        if (empty($permission)) return true;

        return false;
    }

    /** Implemented by Phu Vo on 2020.10.26 */
    static function addReportWidget($reportInfo, $tabId) {
        global $current_user, $adb;

        if (empty($reportInfo['data'])) $reportInfo['data'] = [];
        if (empty($reportInfo['size'])) $reportInfo['size'] = [];

        if (empty($tabId)) $tabId = 1;
        $sql = 'SELECT id FROM vtiger_module_dashboard_widgets WHERE reportid = ? AND userid = ? AND dashboardtabid=?';
        $params = [$reportInfo['id'], $current_user->id, $tabId];

        $result = $adb->pquery($sql, $params);

        if (!$adb->num_rows($result) || !empty($reportInfo['data'])) {
            $params = [$reportInfo['id'], $current_user->id, 0, $reportInfo['name_en'], $reportInfo['name_vn'], $tabId, json_encode($reportInfo['data']), json_encode($reportInfo['size'])];
            $sql = "INSERT INTO vtiger_module_dashboard_widgets (reportid, userid, linkid, name_en, name_vn, dashboardtabid, data, size) VALUES (" . generateQuestionMarks($params) . ")";
            $adb->pquery($sql, $params);
        }
    }

    /** Implemented by Phu Vo on 2020.12.02 */
    static function getTabsByDashboard($dashboardId) {
        global $adb;

        $tabs = [];
        $nameField = Home_DashBoard_Model::getLanguageNameField();

        $sql = "SELECT * FROM vtiger_dashboard_tabs WHERE dashboard_template_id = ?";
        $result = $adb->pquery($sql, [$dashboardId]);
        
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['name'] = $row[$nameField];
            $tabs[] = $row;
        }

        return $tabs;
    }

    /** Implemented by Phu Vo on 2020.12.02 */
    static function getWidgetsByTab($tabId) {
        global $adb;

        $widgets = [];
        $nameField = Home_DashBoard_Model::getLanguageNameField();

        $sql = "SELECT * FROM vtiger_module_dashboard_widgets WHERE dashboardtabid = ?";
        $result = $adb->pquery($sql, [$tabId]);
        
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['name'] = $row[$nameField];
            $widgets[] = $row;
        }

        return $widgets;
    }
}