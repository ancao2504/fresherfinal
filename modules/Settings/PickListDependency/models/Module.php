<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~modules/PickList/DependentPickListUtils.php');

class Settings_PickListDependency_Module_Model extends Settings_Vtiger_Module_Model {

	var $baseTable = 'vtiger_picklist_dependency';
	var $baseIndex = 'id';
	var $name = 'PickListDependency';

	/**
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return 'index.php?module=PickListDependency&parent=Settings&view=List';
	}

	/**
	 * Function to get the url for Adding Dependency
	 * @return <string> - url
	 */
	public function getCreateRecordUrl() {
		return "javascript:Settings_PickListDependency_Js.triggerAdd(event)";
	}

	public function isPagingSupported() {
		return false;
	}

	public static function getAvailablePicklists($module) {
		return Vtiger_DependencyPicklist::getAvailablePicklists($module);
	}

	// Modified by Hieu Nguyen on 2021-08-18 to hide hidden modules from Picklist Dependency
	public static function getPicklistSupportedModules() {
		global $adb, $hiddenModules;
		$forbiddenModules = getForbiddenFeatures('module');
		$unsupportedModules = array_merge($hiddenModules, $forbiddenModules ?? []);

		$query = "SELECT DISTINCT vtiger_field.tabid, vtiger_tab.name, vtiger_tab.tablabel AS label
			FROM vtiger_field
			INNER JOIN vtiger_tab ON (vtiger_tab.tabid = vtiger_field.tabid)
			WHERE vtiger_field.uitype IN (15, 16)
				AND vtiger_tab.presence != 1
				AND vtiger_tab.name NOT IN ('". join("', '", $unsupportedModules) ."')
				AND vtiger_field.tabid != 29
				AND vtiger_field.displaytype = 1
			GROUP BY vtiger_field.tabid HAVING COUNT(*) > 1
			ORDER BY vtiger_tab.tabid ASC";
		$result = $adb->pquery($query, []);
		$modulesModels = [];
		
		while ($row = $adb->fetchByAssoc($result)) {
			$moduleName = $row['name'];
			$moduleLabel = $row['label'];

			$instance = new Vtiger_Module_Model();
			$instance->name = $moduleName;
			$instance->label = $moduleLabel;
			$modulesModels[] = $instance;
		}

		return $modulesModels;
	}
}
