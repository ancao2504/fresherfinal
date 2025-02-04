<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_DashBoard_Model extends Vtiger_Base_Model {

	var $dashboardTabLimit = 10;
	/**
	 * Function to get Module instance
	 * @return <Vtiger_Module_Model>
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the module instance
	 * @param <Vtiger_Module_Model> $moduleInstance - module model
	 * @return Vtiger_DetailView_Model>
	 */
	public function setModule($moduleInstance) {
		$this->module = $moduleInstance;
		return $this;
	}

	/**
	 *  Function to get the module name
	 *  @return <String> - name of the module
	 */
	public function getModuleName(){
		return $this->getModule()->get('name');
	}

	/**
	 * Function returns the list of Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getSelectableDashboard() {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$currentUserPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$dashBoardTabId = $this->get('tabid');
		$dashBoardTabInfo = $this->getTabInfo($dashBoardTabId);
		$moduleTabIdList = array($moduleModel->getId());

		if(!empty($dashBoardTabInfo['appname'])) {
			$allVisibleModules = Settings_MenuEditor_Module_Model::getAllVisibleModules();
			$appVisibleModules = $allVisibleModules[$dashBoardTabInfo['appname']];
			if(is_array($appVisibleModules)) {
				$moduleTabIdList = array();
				foreach($appVisibleModules as $moduleInstance) {
					$moduleTabIdList[] = $moduleInstance->getId();
				}
			}
		}

		$sql = 'SELECT * FROM vtiger_links WHERE linktype = ? AND tabid IN ('. generateQuestionMarks($moduleTabIdList) .') AND linkid NOT IN (SELECT linkid FROM vtiger_module_dashboard_widgets WHERE userid = ? and dashboardtabid=? )';
		$params = array('DASHBOARDWIDGET');
		$params = array_merge($params, $moduleTabIdList);
		$params = array_merge($params, array($currentUser->getId(), $dashBoardTabId));

		$sql .= ' UNION SELECT * FROM vtiger_links WHERE linklabel in (?,?)';
		$params[] = 'Mini List';
		$params[] = 'Notebook';
		$result = $db->pquery($sql, $params);

		$widgets = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);

			if($row['linklabel'] == 'Tag Cloud') {
				$isTagCloudExists = getTagCloudView($currentUser->getId());
				if($isTagCloudExists == 'false') {
					continue;
				}
			}
			if($this->checkModulePermission($row)) {
				$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
			}
		}

		return $widgets;
	}

	/**
	 * Function returns List of User's selected Dashboard Widgets
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDashboards($moduleDashboard) {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$currentUserPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$sql = "SELECT vtiger_links.*, vtiger_module_dashboard_widgets.userid, vtiger_module_dashboard_widgets.filterid, vtiger_module_dashboard_widgets.data, vtiger_module_dashboard_widgets.id as widgetid, vtiger_module_dashboard_widgets.position as position, vtiger_module_dashboard_widgets.size as size, vtiger_links.linkid as id FROM vtiger_links ".
				" INNER JOIN vtiger_module_dashboard_widgets ON vtiger_links.linkid=vtiger_module_dashboard_widgets.linkid".
				" WHERE vtiger_module_dashboard_widgets.userid = ? AND linktype = ? AND tabid = ?";
		$params = array($currentUser->getId(), 'DASHBOARDWIDGET', $moduleModel->getId());

		// Added for Vtiger7
		if($this->get("tabid")){
			$sql .= " AND dashboardtabid = ?";
			array_push($params, $this->get("tabid"));
		}

		$result = $db->pquery($sql, $params);

		$widgets = array();

		for($i=0, $len=$db->num_rows($result); $i<$len; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$data = json_decode(decode_html($row['data']), true);
			$sourceModule = $data['module'];
			if(!empty($sourceModule) && !vtlib_isModuleActive($sourceModule)) {
				continue;
			}
			$row['linkid'] = $row['id'];
			if($this->checkModulePermission($row)) {
				$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
			}
		}

		foreach ($widgets as $index => $widget) {
			$label = $widget->get('linklabel');
			if($label == 'Tag Cloud') {
				$isTagCloudExists = getTagCloudView($currentUser->getId());
				if($isTagCloudExists === 'false')  unset($widgets[$index]);
			}
		}

		//For chart reports as widgets
		$sql = "SELECT * FROM vtiger_module_dashboard_widgets WHERE userid = ? AND linkid = ? AND reportid IS NOT NULL";  // Modified by Hieu Nguyen on 2021-01-05 to get all widget data
		$params = array($currentUser->getId(),0);

		// Added for Vtiger7
		if($this->get("tabid")){
			$sql .= " AND dashboardtabid = ?";
			array_push($params, $this->get("tabid"));
		}

		$result = $db->pquery($sql, $params);
		for($i=0, $len=$db->num_rows($result); $i<$len; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$chartReportModel = Reports_Record_Model::getInstanceById($row['reportid']);
			if($moduleDashboard == 'Home' || $moduleDashboard == $chartReportModel->getPrimaryModule()){
                // Added by Hieu Nguyen on 2020-03-30 to support custom report chart widget
                if ($chartReportModel->get('reporttype') == 'tabular') {
                    $widgets[] = Vtiger_Widget_Model::getInstanceForCustomChartWidget($row['id'], $currentUser->getId());
                    continue;
                }
                // End Hieu Nguyen

                // Modified by Hieu Nguyen on 2021-01-05 to fix issue dashlet of chart report can not resize or remove
				$sourceModuleId = getTabid($chartReportModel->getPrimaryModule());

				if ($sourceModuleId && $currentUserPrivilegeModel->hasModulePermission($sourceModuleId)) {
					$widgets[] = Vtiger_Widget_Model::getInstanceFromValues($row);
				}
                // End Hieu Nguyen
			}
		}
		return $widgets;
	}

    // Modified by Hieu Nguyen on 2020-10-12
	public function getActiveTabs() {
        global $adb, $current_user;
        static $activeTabs;
        if (!empty($activeTabs)) return $activeTabs;

		$sql = "SELECT id, name_en, name_vn, sequence, isdefault, appname, modulename 
            FROM vtiger_dashboard_tabs WHERE userid = ?";
        $params = [$current_user->id];

        if (!empty($_SESSION['editing_dashboard_id'])) {
            $sql .= " AND dashboard_template_id = ?";
            $params[] = $_SESSION['editing_dashboard_id'];
        }

        if (!$_SESSION['dashboard_edit_mode']) {
            $sql .= " AND type != 'template'";
        }

        $sql .= " ORDER BY sequence ASC";
		$result = $adb->pquery($sql, $params);
		$activeTabs = [];

		while ($row = $adb->fetchByAssoc($result)) {
            $row['tabname'] = ($current_user->language == 'vn_vn') ? $row['name_vn'] : $row['name_en'];
			$activeTabs[] = $row;
		}

		return $activeTabs;
	}

	/**
	 * To get first tab of the user
	 * Purpose : If user added a widget in Vtiger6 then we need add that widget for first tab
	 * @param type $userId
	 * @return type
	 */
    // Modified by Hieu Nguyen on 2020-10-12
	public function getUserDefaultTab($userId) {
		global $adb;
        static $defaultTab;
        if (!empty($defaultTab)) return $defaultTab;

		$sql = "SELECT id, name_en, name_vn, appname, modulename 
            FROM vtiger_dashboard_tabs WHERE userid = ? AND isdefault = 1";
        $params = [$userId];

        if (!empty($_SESSION['editing_dashboard_id'])) {
            $sql .= " AND dashboard_template_id = ?";
            $params[] = $_SESSION['editing_dashboard_id'];
        }

        if (!$_SESSION['dashboard_edit_mode']) {
            $sql .= " AND type != 'template'";
        }

        $sql .= " ORDER BY sequence ASC";
		$result = $adb->pquery($sql, $params);
        if (!$result) return [];

        $defaultTab = $adb->fetchByAssoc($result);
        if (!$defaultTab) return [];

		return $defaultTab;
	}

    // Modified by Hieu nguyen on 2020-10-12 to save tab name in both English and Vietnamese
	public function addTab($tabNameEn, $tabNameVn) {
		global $adb, $current_user;
		$sequence = $adb->getOne("SELECT MAX(sequence)+1 AS sequence FROM vtiger_dashboard_tabs", []);
        $dashboardTemplateId = $_SESSION['editing_dashboard_id'];
        $type = ($_SESSION['dashboard_edit_mode'] == true) ? 'template' : '';

		$query = "INSERT INTO vtiger_dashboard_tabs(name_en, name_vn, userid, sequence, dashboard_template_id, type) VALUES(?, ?, ?, ?, ?, ?)";
		$params = [$tabNameEn, $tabNameVn, $current_user->id, $sequence, $dashboardTemplateId, $type];
        $adb->pquery($query, $params);

        $tabName = ($current_user->language == 'vn_vn') ? $tabNameVn : $tabNameEn;
		$tabData = [
            'tabid' => $adb->getLastInsertID(),
            'tabname' => $tabName,
            'sequence' => $sequence
        ];

		return $tabData;
	}

    // Modified by Hieu Nguyen on 2020-10-12 to make this action more scure
	public function deleteTab($tabId) {
		global $adb, $current_user;
		$query = "DELETE FROM vtiger_dashboard_tabs WHERE userid = ? AND id = ?";
        $params = [$current_user->id, $tabId];
		$adb->pquery($query, $params);
		return true;
	}

    // Modified by Hieu Nguyen on 2020-10-12
	public function renameTab($tabId, $tabNameEn, $tabNameVn) {
		global $adb, $current_user;
		$query = "UPDATE vtiger_dashboard_tabs SET name_en = ?, name_vn = ? WHERE userid = ? AND id = ?";
        $params = [$tabNameEn, $tabNameVn, $current_user->id, $tabId];
		$adb->pquery($query, $params);
		return true;
	}
    
    // Modified by Hieu Nguyen on 2020-10-12
	public function checkTabExist($tabNameEn, $tabNameVn, $tabId = '') {
		global $adb, $current_user;
		$sql = "SELECT COUNT(id) FROM vtiger_dashboard_tabs WHERE userid = ? AND (name_en = ? OR name_vn = ?)";
        $params = [$current_user->id, $tabNameEn, $tabNameVn];

        if (!empty($tabId)) {
            $sql .= " AND id != ?";
            $params[] = $tabId;
        }

        if (!empty($_SESSION['editing_dashboard_id'])) {
            $sql .= " AND dashboard_template_id = ?";
            $params[] = $_SESSION['editing_dashboard_id'];
        }

        if (!$_SESSION['dashboard_edit_mode']) {
            $sql .= " AND type != 'template'";
        }

		$numRows = $adb->getOne($sql, $params);

		return ($numRows > 0);
	}

    // Modified by Hieu Nguyen on 2020-10-14
	public function checkTabsLimitExceeded() {
		global $adb, $current_user;
		$sql = "SELECT COUNT(id) FROM vtiger_dashboard_tabs WHERE userid = ?";
        $params = [$current_user->id];

        if (!empty($_SESSION['editing_dashboard_id'])) {
            $sql .= " AND dashboard_template_id = ?";
            $params[] = $_SESSION['editing_dashboard_id'];
        }

        if (!$_SESSION['dashboard_edit_mode']) {
            $sql .= " AND type != 'template'";
        }

		$count = $adb->getOne($sql, $params);

		if ($count >= $this->dashboardTabLimit) {
			return true;
		}

		return false;
	}

	public function updateTabSequence($sequence){
		$db = PearDatabase::getInstance();

		$query = "UPDATE vtiger_dashboard_tabs SET sequence = ? WHERE id=?";
		foreach($sequence as $tabId => $seq){
			$db->pquery($query, array($seq,$tabId));
		}
		return true;
	}

	public function getTabInfo($tabId) {
		$db = PearDatabase::getInstance();

		$query = "SELECT * FROM vtiger_dashboard_tabs WHERE id=? ";
		$params = array($tabId);
		$result = $db->pquery($query, $params);
		if ($db->num_rows($result) <= 0) {
			return false;
		} else {
			return $db->fetchByAssoc($result, 0);
		}
	}

	/**
	 * Function to get the default widgets(Deprecated)
	 * @return <Array of Vtiger_Widget_Model>
	 */
	public function getDefaultWidgets() {
		//TODO: Need to review this API is needed?
		$moduleModel = $this->getModule();
		$widgets = array();

		return $widgets;
	}


	/**
	 * Function to get the instance
	 * @param <String> $moduleName - module name
	 * @return <Vtiger_DashBoard_Model>
	 */
	public static function getInstance($moduleName) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'DashBoard', $moduleName);
		$instance = new $modelClassName();

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $instance->setModule($moduleModel);
	}

	/**
	 * Function to get the module and check if the module has permission from the query data
	 * @param <array> $resultData - Result Data From Query
	 * @return <boolean>
	 */
	public function checkModulePermission($resultData) {
		$currentUserPrivilegeModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$linkUrl = $resultData['linkurl'];
		$linkLabel = $resultData['linklabel'];
		$filterId = $resultData['filterid'];
		$data = decode_html($resultData['data']);
		$module = $this->getModuleNameFromLink($linkUrl, $linkLabel);

		if($module == 'Home' && !empty($filterId) && !empty($data)) {
			$filterData = Zend_Json::decode($data);
			$module = $filterData['module'];
		}

		return ($currentUserPrivilegeModel->hasModulePermission(getTabid($module)) && !Vtiger_Runtime::isRestricted('modules', $module));
	}

	/**
	 * Function to get the module name of a widget using linkurl
	 * @param <string> $linkUrl
	 * @param <string> $linkLabel
	 * @return <string> $module - Module Name
	 */
	public function getModuleNameFromLink($linkUrl, $linkLabel) {
		$urlParts = parse_url($linkUrl);
		parse_str($urlParts['query'], $params);
		$module = $params['module'];

		if($linkLabel == 'Overdue Activities' || $linkLabel == 'Upcoming Activities') {
			$module = 'Calendar';
		}

		return $module;
	}

}
