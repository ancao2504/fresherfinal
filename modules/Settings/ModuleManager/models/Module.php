<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_ModuleManager_Module_Model extends Vtiger_Module_Model {
	
	/**
     * Modified By Kelvin Thang
     * Date: 2019-01-14
     * @return array
     */
	public static function getNonVisibleModulesList() {
		$nonVisibleModulesList = array(
            'ModTracker',
            'Users',
            'Mobile',
            'Integration',
            'WSAPP',
            'ModComments',
            'Dashboard',
            'ConfigEditor',
            'CronTasks',
            'Import',
            'Tooltip',
            'CustomerPortal',
            'Home',
            'VtigerBackup',
            'FieldFormulas',
            // 'EmailTemplates',    // Commented out this line by Hieu Nguyen on 2021-04-15 to restore module Email Templates
            'ExtensionStore',

            // Added by Hieu Nguyen on 2020-12-10
            'Rss',
            'Google',
            'Webforms',
            // End Hieu Nguyen
        );

        // Modified by Hieu Nguyen on 2021-08-18 to hide custom modules from Module Managers
		global $hiddenModulesForModuleManager;
		$forbiddenModules = getForbiddenFeatures('module');
		$nonVisibleModulesList = array_merge($nonVisibleModulesList, $hiddenModulesForModuleManager, $forbiddenModules ?? []);
		// End Hieu Nguyen

        return $nonVisibleModulesList;
	}

	// Added by Hieu Nguyen on 2018-08-14
	public static function moduleExists($moduleName) {
		$db = PearDatabase::getInstance();

		$sql = "SELECT 1 FROM vtiger_tab WHERE UPPER(name) = UPPER(TRIM('{$moduleName}'))";
		$exists = $db->getOne($sql);

		return $exists;
	}
	// End Hieu Nguyen

	// Added by Hieu Nguyen on 2018-08-16
	public static function initNewModule($moduleName) {
		$db = PearDatabase::getInstance();
		$module = Vtiger_Module::getInstance($moduleName);
		$moduleId = $module->getId();

		// Copy blocks from edit view to detail view
		$sql = "INSERT INTO vtiger_blocks
  			SELECT * FROM vtiger_editview_blocks WHERE tabid = {$moduleId}";
		$db->pquery($sql);

		// Copy max detail view block sequence number
		$sql = "UPDATE vtiger_blocks_seq 
            SET id = (SELECT MAX(blockid) FROM vtiger_blocks)";
		$db->pquery($sql);

		// Copy all field layout from edit view to detail view
        $query = "UPDATE vtiger_field SET block = editview_block, sequence = editview_sequence, presence = editview_presence WHERE tabid = ?";
        $db->pquery($query, [$moduleId]);

        // Set the right block for fields in detail view (matching by block label)
        $query = "UPDATE vtiger_field AS f 
            INNER JOIN vtiger_editview_blocks AS evb ON (evb.blockid = f.editview_block AND evb.tabid = f.tabid)
            INNER JOIN vtiger_blocks AS dvb ON (dvb.blocklabel = evb.blocklabel AND dvb.tabid = f.tabid)
            SET f.block = dvb.blockid WHERE f.tabid = ?";
        $db->pquery($query, [$moduleId]);
	}
	// End Hieu Nguyen

	/**
	 * Function to get the url of new module import
	 */
	public static function getNewModuleImportUrl() {
		$importURL = '';
		$extensionStore = Vtiger_Module_Model::getInstance('ExtensionStore');
		if($extensionStore && $extensionStore->isActive()) {
			$importURL = Settings_ExtensionStore_Module_Model::getInstance()->getDefaultUrl();
		} else {
			$importURL = 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport';
		}
		return $importURL;
	}

	/**
	 * Function to get the url of Extension store
	 */
	public static function getExtensionStoreUrl() {
		return 'index.php?module=ExtensionStore&parent=Settings&view=ExtensionImport&mode=index';
	}

	/**
	 * Function to get the url of new module import 
	 */
	public static function getUserModuleFileImportUrl() {
		return 'index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1'; 
	}

	/**
	 * Function to disable a module 
	 * @param type $moduleName - name of the module
	 */
	public function disableModule($moduleName) {
		//Handling events after disable module
		vtlib_toggleModuleAccess($moduleName, false);
	}

	/**
	 * Function to enable the module
	 * @param type $moduleName -- name of the module
	 */
	public function enableModule($moduleName) {
		//Handling events after enable module
		vtlib_toggleModuleAccess($moduleName, true);
	}


	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getAll() {
		 return parent::getAll(array(0,1), self::getNonVisibleModulesList());
	}

	/**
	 * Function which will get count of modules
	 * @param <Boolean> $onlyActive - if true get count of only active modules else all the modules
	 * @return <integer> number of modules
	 */
	public static function getModulesCount($onlyActive = false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT * FROM vtiger_tab';
		$params = array();
		if($onlyActive) {
			$presence = array(0);
			$nonVisibleModules = self::getNonVisibleModulesList();
			$query .= ' WHERE presence IN ('. generateQuestionMarks($presence) .')';
			$query .= ' AND name NOT IN ('.generateQuestionMarks($nonVisibleModules).')';
			array_push($params, $presence,$nonVisibleModules);
		}
		$result = $db->pquery($query, $params);
		return $db->num_rows($result);
	}

	/**
	 * Function that returns all those modules that support Module Sequence Numbering
	 * @global PearDatabase $db - database connector
	 * @return <Array of Vtiger_Module_Model>
	 */
	public static function getModulesSupportingSequenceNumbering() {
		$db = PearDatabase::getInstance();
		$sql="SELECT tabid, name FROM vtiger_tab WHERE isentitytype = 1 AND presence = 0 AND tabid IN
			(SELECT DISTINCT tabid FROM vtiger_field WHERE uitype = '4')";
		$result = $db->pquery($sql, array());

		$moduleModels = array();
		for($i=0; $i<$db->num_rows($result); ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$moduleModels[$row['name']] = self::getInstanceFromArray($row);
		}
		return $moduleModels;
	}

	/**
	 * Function to get restricted modules list
	 * @return <Array> List module names
	 */
	public static function getActionsRestrictedModulesList() {
		return array('Home', 'Emails', 'Webmails');
	}
}
