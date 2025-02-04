<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
vimport('~~/modules/Reports/Reports.php');

class Vtiger_Report_Model extends Reports {

	static function getInstance($reportId = "") {
		$self = new self();
		return $self->Reports($reportId);
	}

	function Reports($reportId = "") {
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$currentUserRoleId = $currentUser->get('roleid');
		$subordinateRoles = getRoleSubordinates($currentUserRoleId);
		array_push($subordinateRoles, $currentUserRoleId);

		$this->initListOfModules();

		if($reportId != "") {
			// Lookup information in cache first
			$cachedInfo = VTCacheUtils::lookupReport_Info($userId, $reportId);
			$subOrdinateUsers = VTCacheUtils::lookupReport_SubordinateUsers($reportId);

			if($cachedInfo === false) {
				$ssql = "SELECT vtiger_reportmodules.*, vtiger_report.* FROM vtiger_report
							INNER JOIN vtiger_reportmodules ON vtiger_report.reportid = vtiger_reportmodules.reportmodulesid
							WHERE vtiger_report.reportid = ?";
				$params = array($reportId);

				require_once('include/utils/GetUserGroups.php');
				require('user_privileges/user_privileges_'.$userId.'.php');

				$userGroups = new GetUserGroups();
				$userGroups->getAllUserGroups($userId);
				$userGroupsList = $userGroups->user_groups;

				if(!empty($userGroupsList) && $currentUser->isAdminUser() == false) {
					$userGroupsQuery = " (shareid IN (".generateQuestionMarks($userGroupsList).") AND setype='groups') OR";
					foreach($userGroupsList as $group) {
						array_push($params, $group);
					}
				}

				$nonAdminQuery = " vtiger_report.reportid IN (SELECT reportid from vtiger_reportsharing
									WHERE $userGroupsQuery (shareid=? AND setype='users'))";
				if($currentUser->isAdminUser() == false) {
					$ssql .= " AND (($nonAdminQuery)
								OR vtiger_report.sharingtype = 'Public'
								OR vtiger_report.owner = ? OR vtiger_report.owner IN
									(SELECT vtiger_user2role.userid FROM vtiger_user2role
									INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
									INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
									WHERE vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%') 
								OR (vtiger_report.reportid IN (SELECT reportid FROM vtiger_report_shareusers WHERE userid = ?))";
					if(!empty($userGroupsList)) {
						$ssql .= " OR (vtiger_report.reportid IN (SELECT reportid FROM vtiger_report_sharegroups 
									WHERE groupid IN (".generateQuestionMarks($userGroupsList).")))";
					}
					$ssql .= " OR (vtiger_report.reportid IN (SELECT reportid FROM vtiger_report_sharerole WHERE roleid = ?))
							   OR (vtiger_report.reportid IN (SELECT reportid FROM vtiger_report_sharers 
								WHERE rsid IN (".generateQuestionMarks($subordinateRoles).")))
							  )";
					array_push($params, $userId, $userId, $userId);
					foreach($userGroupsList as $groups) {
						array_push($params, $groups);
					}
					array_push($params, $currentUserRoleId);
					foreach($subordinateRoles as $role) {
						array_push($params, $role);
					}
				}
				$result = $db->pquery($ssql, $params);

				// Modified by Hieu Nguyen on 2022-03-10 to make the code reusable
				if ($result && $db->num_rows($result)) {
					$row = $db->fetch_array($result);
					$this->setCache($reportId, $row);
				}
				// End Hieu Nguyen

				$subOrdinateUsers = Array();

				$subResult = $db->pquery("SELECT userid FROM vtiger_user2role
									INNER JOIN vtiger_users ON vtiger_users.id = vtiger_user2role.userid
									INNER JOIN vtiger_role ON vtiger_role.roleid = vtiger_user2role.roleid
									WHERE vtiger_role.parentrole LIKE '$current_user_parent_role_seq::%'", array());

				$numOfSubRows = $db->num_rows($subResult);

				for($i=0; $i<$numOfSubRows; $i++) {
					$subOrdinateUsers[] = $db->query_result($subResult, $i,'userid');
				}

				// Update subordinate user information for re-use
				VTCacheUtils::updateReport_SubordinateUsers($reportId, $subOrdinateUsers);

				// Re-look at cache to maintain code-consistency below
				$cachedInfo = VTCacheUtils::lookupReport_Info($userId, $reportId);
			}

			// Modified by Hieu Nguyen on 2022-03-10 to make the code reusable
			if ($cachedInfo) {
				$this->initRecordFromCache($subOrdinateUsers, $cachedInfo);
			}
			// End Hieu Nguyen
		}
		return $this;
	}

	function isEditable() {
		return $this->is_editable;
	}

	// Modified by Hieu Nguyen on 2022-09-15 to hide hidden modules and forbidden module from Report's module list
	function getModulesList() {
		global $hiddenModules;
		$unsupportedModules = array_merge($hiddenModules, getForbiddenFeatures('module'));

		foreach ($this->module_list as $moduleName => $translatedBlockNames) {
			if (!in_array($moduleName, $unsupportedModules) && isPermitted($moduleName, 'index') == 'yes') {
				$modules [$moduleName] = vtranslate($moduleName, $moduleName);
			}
		}

		asort($modules);
		return $modules;
	}

	// Added by Hieu Nguyen on 2022-09-15 to get supported related modules list
	function getRelatedModuleList() {
		global $hiddenModules;
		$unsupportedModules = array_merge($hiddenModules, getForbiddenFeatures('module'));

		foreach ($this->related_modules as $primaryModule => $relatedModules) {
			if (in_array($primaryModule, $unsupportedModules)) {
				unset($this->related_modules[$primaryModule]);
				continue;
			}

			$relatedModuleList = [];

			foreach ($relatedModules as $relatedModule) {
				if (!in_array($relatedModule, $unsupportedModules)) {
					$relatedModuleList[] = $relatedModule;
				}
			}

			$this->related_modules[$primaryModule] = $relatedModuleList;
		}

		return $this->related_modules;
	}
}