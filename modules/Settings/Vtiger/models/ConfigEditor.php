<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// Renamed from ConfigModule.php and refactored by Hieu Nguyen on 2022-06-13
class Settings_Vtiger_ConfigEditor_Model extends Settings_Vtiger_Module_Model {

	var $fileName = 'config.inc.php';
	var $fileContent = null;

	/**
	 * Function to get editable fields
	 * @return <Array> list of field names
	 	'config_name' => array(
			'label' => 'LBL_FIELD_LABEL',
			'fieldType' => 'input',							// Supported type: input, picklist, multi_picklist, custom_picklist
			'validation' => 'data-rule-required="true"',	// Any validation rule for input attribute
			'value' => $GLOBALS['config_name'],				// Get config value using $GLOBALS array
			'valueUnit' => '',								// Ex: MB, Days, USD, ...
			'isCustom' => true,								// true: stored in config_override.cus.php; false: store in default file (config.inc.php)
		)
	 */
	public function getEditableFields() {
		$editableFields = array(
			// 'HELPDESK_SUPPORT_EMAIL_ID' => array(
			// 	'label' => 'LBL_HELPDESK_SUPPORT_EMAILID',
			// 	'fieldType' => 'input',
			// 	'validation' => 'data-rule-required="true" data-rule-email="true"',
			// 	'value' => $GLOBALS['HELPDESK_SUPPORT_EMAIL_ID'],
			// 	'valueUnit' => '',
			// ),
			// 'HELPDESK_SUPPORT_NAME' => array(
			// 	'label' => 'LBL_HELPDESK_SUPPORT_NAME',
			// 	'fieldType' => 'input',
			// 	'validation' => 'data-rule-required="true"',
			// 	'value' => $GLOBALS['HELPDESK_SUPPORT_NAME'],
			// 	'valueUnit' => '',
			// ),
			'default_module' => array(
				'label' => 'LBL_DEFAULT_MODULE',
				'fieldType' => 'module_list',
				'validation' => 'data-rule-required="true"',
				'value' => $GLOBALS['default_module'],
				'valueUnit' => '',
			),
			// 'listview_max_textlength' => array(
			// 	'label' => 'LBL_MAX_TEXT_LENGTH_IN_LISTVIEW',
			// 	'fieldType' => 'input',
			// 	'validation' => 'data-rule-required="true" data-rule-range=[1,100] data-rule-positive="true" data-rule-wholeNumber="true"',
			//  'value' => $GLOBALS['listview_max_textlength'],
			// 	'valueUnit' => '',
			// ),
			'minilist_widget_max_columns' => array(
				'label' => 'LBL_MINILIST_WIDGET_MAX_COLUMNS',
				'fieldType' => 'input',
				'validation' => 'data-rule-required="true" data-rule-range=[1,12] data-rule-positive="true" data-rule-wholeNumber="true"',
				'value' => $GLOBALS['minilist_widget_max_columns'],
				'valueUnit' => '',
			),
			'list_max_entries_per_page'	=> array(
				'label' => 'LBL_MAX_ENTRIES_PER_PAGE_IN_LISTVIEW',
				'fieldType' => 'input',
				'validation' => 'data-rule-required="true" data-rule-range=[1,100] data-rule-positive="true" data-rule-wholeNumber="true"',
				'value' => $GLOBALS['list_max_entries_per_page'],
				'valueUnit' => '',
			),
			'separator1' => array(
				'fieldType' => 'separator',
			),
			'upload_maxsize' => array(
				'label' => 'LBL_MAX_UPLOAD_SIZE',
				'fieldType' => 'input',
				'validation' => 'data-rule-required="true" data-rule-range=[1,256] data-rule-positive="true" data-rule-wholeNumber="true"',
				'value' => round(number_format($GLOBALS['upload_maxsize'] / 1048576, 2)),
				'valueUnit' => 'MB',
			),
			'allowed_upload_file_exts' => array(
				'label' => 'LBL_ALLOWED_UPLOAD_FILE_EXTS',
				'fieldType' => 'custom_picklist',
				'validation' => 'data-rule-required="true"',
				'values' => $GLOBALS['validationConfig']['allowed_upload_file_exts'],
				'valueUnit' => '',
				'isCustom' => true,
			),
			// Define new editable setting here!
		);

		return $editableFields;
	}

	/**
	 * Function to get picklist values
	 * @param <String> $fieldName
	 * @return <Array> list of module names
	 */
	public function getPicklistValues($fieldName) {
		global $adb;

		if ($fieldName == 'default_module') {
			$restrictedModules = ['Webmails', 'Emails', 'Integration', 'Dashboard', 'ModComments'];

			$sql = "SELECT name FROM vtiger_tab
				WHERE presence IN (0) AND isentitytype = 1 AND name NOT LIKE '%Log'
					AND name NOT IN (". generateQuestionMarks($restrictedModules) .")";
			$result = $adb->pquery($sql, [$restrictedModules]);
			$modules = ['Home' => 'Home'];

			while ($row = $adb->fetchByAssoc($result)) {
				$modules[$row['name']] = vtranslate('SINGLE_' . $row['name'], $row['name']);
			}

			return $modules;
		}

		// Define new picklist values here

		return ['true', 'false'];
	}

	/**
	 * Function to validate the field values
	 * @param <Array> $updatedFields
	 * @return <String> True/Error message
	 */
	public function validateFieldValues($updatedFields){
		if (array_key_exists('HELPDESK_SUPPORT_EMAIL_ID', $updatedFields) && !filter_var($updatedFields['HELPDESK_SUPPORT_EMAIL_ID'], FILTER_VALIDATE_EMAIL)) {
			return 'LBL_INVALID_EMAILID';
		}
		else if (array_key_exists('HELPDESK_SUPPORT_NAME', $updatedFields) && preg_match ('/[\'";?><]/', $updatedFields['HELPDESK_SUPPORT_NAME'])) {
			return 'LBL_INVALID_SUPPORT_NAME';
		}
		else if (array_key_exists('default_module', $updatedFields) && !preg_match ('/[a-zA-z0-9]/', $updatedFields['default_module'])) {
			return 'LBL_INVALID_MODULE';
		}
		else if (
			(array_key_exists('upload_maxsize', $updatedFields) && !filter_var(ltrim($updatedFields['upload_maxsize'], '0'), FILTER_VALIDATE_INT))
			|| (array_key_exists('list_max_entries_per_page', $updatedFields) &&  !filter_var(ltrim($updatedFields['list_max_entries_per_page'], '0'), FILTER_VALIDATE_INT))
			|| (array_key_exists('listview_max_textlength', $updatedFields) && !filter_var(ltrim($updatedFields['listview_max_textlength'], '0'), FILTER_VALIDATE_INT))
		) {
			return 'LBL_INVALID_NUMBER';
		}

		// Define more validation rules here!

		return true;
	}

	private function saveCustomConfigs(array $updatedFields, array $editableFields) {
		require_once('include/utils/CustomConfigUtils.php');
		$customConfigs = [];

		foreach ($editableFields as $fieldName => $fieldInfo) {
			$fieldValue = $updatedFields[$fieldName];

			if ($fieldName == 'allowed_upload_file_exts') {
				$customConfigs['validationConfig.allowed_upload_file_exts'] = $fieldValue;
			}

			// More custom config handler here
		}

		if (!empty($customConfigs)) {
			CustomConfigUtils::saveCustomConfigs($customConfigs);
		}
	}

	/**
	 * Function to read config file
	 * @return <Array> The data of config file
	 */
	public function readFile() {
		if (!$this->fileContent) {
			$this->fileContent = file_get_contents($this->fileName);
		}

		return $this->fileContent;
	}
	
	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem() {
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('Configuration Editor');
		return $menuItem;
	}

	/**
	 * Function to save the data
	 */
	public function save() {
		$fileContent = $this->fileContent;
		$updatedFields = $this->get('updatedFields');
		$validationInfo = $this->validateFieldValues($updatedFields);

		if ($validationInfo === true) {
			$editableFields = $this->getEditableFields();

			// Save non-custom configs
			foreach ($updatedFields as $fieldName => $fieldValue) {
				$fieldInfo = $editableFields[$fieldName];
				if (empty($fieldInfo)) continue;		// Do not save non-editable config
				if ($fieldInfo['isCustom']) continue;	// Do not save custom config here

				$patternString = "\$%s = '%s';";

				if ($fieldName == 'upload_maxsize') {
					$fieldValue = $fieldValue * 1048576; // (1024 * 1024)
					$patternString = "\$%s = %s;";
				}

				if ($fieldName == 'list_max_entries_per_page' || $fieldName == 'listview_max_textlength') {
					$fieldValue = intval($fieldValue);
				}

				$pattern = '/\$' . $fieldName . '[\s]+=([^;]+);/';
				$replacement = sprintf($patternString, $fieldName, ltrim($fieldValue, '0'));
				$fileContent = preg_replace($pattern, $replacement, $fileContent);
			}

			$filePointer = fopen($this->fileName, 'w');
			fwrite($filePointer, $fileContent);
			fclose($filePointer);

			// Save custom configs
			$this->saveCustomConfigs($updatedFields, $editableFields);
		}

		return $validationInfo;
	}

	/**
	 * Function to get the instance of Config module model
	 * @return <Settings_Vtiger_ConfigModule_Model> $moduleModel
	 */
	public static function getInstance() {
		$moduleModel = new self();
		$moduleModel->readFile();
		return $moduleModel;
	}
}