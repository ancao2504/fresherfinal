<?php

/*
	Class Vtiger_BlockAndField_Helper
	Author: Hieu Nguyen
	Date: 2019-03-01
	Purpose: provide util functions to sync block and fields between register files and database
*/

require_once('include/utils/FileUtils.php');
require_once('libraries/ArrayUtils/ArrayUtils.php');

class Vtiger_BlockAndField_Helper {

	const FULL_BLOCK_DEF_ATTRIBUTES_NUM = 9;    // Full block array contains 9 attritubes
	const FULL_FIELD_DEF_ATTRIBUTES_NUM = 26;   // Full field array contains 26 attritubes

	// Util function to get file type for saving register file
	public static function getFileTypeForSaving() {
		global $developerTeam;
		if ($developerTeam == 'R&D') return 'base';
		if ($developerTeam == 'DEV') return 'dev';
		return 'cus';
	}

	// Sync all blocks and fields from db into register file. Use when creating NEW module only!
	static function syncToRegisterFile(array $moduleDef, $fileType) {
		$blocksAndFields = self::getBlocksAndFields($moduleDef['id']);

		// Convert into file format
		$content = [
			'editViewBlocks' => $blocksAndFields['editview_blocks'],
			'detailViewBlocks' => $blocksAndFields['detailview_blocks'],
			'fields' => $blocksAndFields['fields']
		];

		self::writeRegisterFile($moduleDef['name'], $content, $fileType);
	}

	// Sync a single block from db into register file. Use when creating NEW block only!
	static function syncBlockToRegisterFile($module, $blockName, $view = 'EditView') {
		global $adb;

		$tableName = ($view == 'EditView') ? 'vtiger_editview_blocks' : 'vtiger_blocks';
		$sql = "SELECT * FROM {$tableName} WHERE tabid = ? AND blocklabel = ?";
		$result = $adb->pquery($sql, [$module->id, $blockName]);

		$blockDef = $adb->fetchByAssoc($result);
		$blockDef = self::trimBlockDef($blockDef);

		self::writeBlockToRegisterFile($module->name, $blockName, $blockDef, $view);
	}

	// Sync a single field from db into register file. Use when creating NEW field only!
	static function syncFieldToRegisterFile($module, $fieldName) {
		global $adb;

		$sql = "SELECT * FROM vtiger_field WHERE tabid = ? AND fieldname = ?";
		$result = $adb->pquery($sql, [$module->id, $fieldName]);
		$fieldDef = $adb->fetchByAssoc($result);

		$fieldDef['columntype'] = self::getColumnType($fieldDef['tablename'], $fieldDef['columnname']);
		$fieldDef = self::attachBlocksName($fieldDef);
		$fieldDef = self::trimFieldDef($fieldDef);

		self::writeFieldToRegisterFile($module->name, $fieldName, $fieldDef);
	}

	// Save updated block's attributes into register file. Use when update block order in Layout Editor
	static function saveBlockAttributesToRegisterFile($moduleName, $blockName, array $changedAttributes, $blockType = 'EditView') {
		self::writeBlockToRegisterFile($moduleName, $blockName, $changedAttributes, $blockType);
	}

	// Save updated block's attributes into register file. Use when update field attribute and order in Layout Editor
	static function saveFieldAttributesToRegisterFile($moduleName, $fieldName, array $changedAttributes) {
		self::writeFieldToRegisterFile($moduleName, $fieldName, $changedAttributes);
	}

	// Remove a block from register file. Use when DELETE a block in Layout Editor
	static function removeBlockFromRegisterFile($moduleName, $blockName, $blockType = 'EditView') {
		$fileType = self::getFileTypeForSaving();
		$content = self::readRegisterFile($moduleName, $fileType);
		// TODO: dev or customer file may not exist
		if (empty($content)) return;

		if ($blockType == 'EditView') {
			unset($content['editViewBlocks'][$blockName]);
		}
		else {
			unset($content['detailViewBlocks'][$blockName]);
		}

		self::writeRegisterFile($moduleName, $content, $fileType);
	}

	// Remove a field from register file. Use when DELETE a field in Layout Editor
	static function removeFieldFromRegisterFile($moduleName, $fieldName) {
		$fileType = self::getFileTypeForSaving();
		$content = self::readRegisterFile($moduleName, $fileType);
		// TODO: dev or customer file may not exist
		if (empty($content)) return;

		unset($content['fields'][$fieldName]);
		self::writeRegisterFile($moduleName, $content, $fileType);
	}

	// Sync all from register files into db
	static function syncToDatabase(array $moduleDef) {
		$moduleName = $moduleDef['name'];
		$moduleId = $moduleDef['id'];
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$content = self::readRegisterFiles($moduleName);
		$blocksAndFieldsMap = self::getBlocksAndFieldsMap($moduleId);

		// Sync EditView Blocks
		foreach ($content['editViewBlocks'] as $blockDef) {
			// Ignore block array that does not have enough attributes
			if (empty($blockDef['blocklabel']) || count(array_keys($blockDef)) < Vtiger_BlockAndField_Helper::FULL_BLOCK_DEF_ATTRIBUTES_NUM) {
				continue;
			}

			if ($blockDef['blocklabel'] == 'LBL_BLOCK1') {
				$debug = 1;
			}

			$block = $blocksAndFieldsMap['editview_blocks'][$blockDef['blocklabel']];

			// Re-assign module id and tab id
			$blockDef['blockid'] = !empty($block) ? $block['blockid'] : ''; // New block will not exist in the map
			$blockDef['tabid'] = $moduleId;
			$blockDef['table_name'] = 'vtiger_editview_blocks';

			$blockId = self::syncBlockToDb($blockDef);

			// Insert new block into the map
			if (empty($block)) {
				$block = $blockDef;
				$block['blockid'] = $blockId;
				$blocksAndFieldsMap['editview_blocks'][$blockDef['blocklabel']] = $block;
			}
		}

		// Sync DetaiView Blocks
		foreach ($content['detailViewBlocks'] as $blockDef) {
			// Ignore block array that does not have enough attributes
			if (empty($blockDef['blocklabel']) || count(array_keys($blockDef)) < Vtiger_BlockAndField_Helper::FULL_BLOCK_DEF_ATTRIBUTES_NUM) {
				continue;
			}

			if ($blockDef['blocklabel'] == 'LBL_BLOCK1') {
				$debug = 1;
			}

			$block = $blocksAndFieldsMap['detailview_blocks'][$blockDef['blocklabel']];

			// Re-assign module id and tab id
			$blockDef['blockid'] = !empty($block) ? $block['blockid'] : ''; // New block will not exist in the map
			$blockDef['tabid'] = $moduleId;
			$blockDef['table_name'] = 'vtiger_blocks';

			$blockId = self::syncBlockToDb($blockDef);

			// Insert new block into the map
			if (empty($block)) {
				$block = $blockDef;
				$block['blockid'] = $blockId;
				$blocksAndFieldsMap['detailview_blocks'][$blockDef['blocklabel']] = $block;
			}
		}

		// Sync Fields
		$createdFields = [];

		foreach ($content['fields'] as $fieldDef) {
			// Ignore fields that will never display in form
			if (in_array($fieldDef['fieldname'], ['starred', 'tags', 'campaignrelstatus'])) {
				continue;
			}

			// Ignore field array that does not have enough attributes
			if (empty($fieldDef['fieldname']) || count(array_keys($fieldDef)) < Vtiger_BlockAndField_Helper::FULL_BLOCK_DEF_ATTRIBUTES_NUM) {
				continue;
			}

			if ($fieldDef['fieldname'] == 'field1') {
				$debug = 1;
			}

			$field = $blocksAndFieldsMap['fields'][$fieldDef['fieldname']];
			$editViewBlock = $blocksAndFieldsMap['editview_blocks'][$fieldDef['editview_block_name']] ?? [];
			$detailViewBlock = $blocksAndFieldsMap['detailview_blocks'][$fieldDef['detailview_block_name']] ?? [];
			
			if (!empty($editViewBlock) && empty($detailViewBlock)) {
				$debug = 1;
			}

			if (empty($editViewBlock) && empty($detailViewBlock)) {
				$debug = 1;
			}

			// Re-assign module id, field id and block id
			$fieldDef['tabid'] = $moduleId;
			$fieldDef['fieldid'] = !empty($field) ? $field['fieldid'] : ''; // New field will not exist in the map
			$fieldDef['editview_block'] = $editViewBlock['blockid'];
			$fieldDef['block'] = $detailViewBlock['blockid'];

			$result = self::syncFieldToDb($fieldDef, $detailViewBlock, $moduleModel);

			// Log created fields
			if ($result == 2) {
				$fieldName = $fieldDef['fieldname'];
				
				if (in_array($fieldDef['uitype'], [15, 16])) {
					$fieldName .= ' (picklist)';
				}

				if ($fieldDef['uitype'] == 33) {
					$fieldName .= ' (multi-picklist)';
				}
				
				$createdFields[] = $fieldName;
			}

			// Fix field access permission for all roles when this field is marked as required
			if (!empty($fieldDef['fieldid']) && strpos($fieldDef['typeofdata'], 'M') > 0) {
				Vtiger_BlockAndField_Helper::setFieldAccessibleForAllRoles($moduleId, $fieldDef['fieldid']);
			}
		}

		return $createdFields;
	}

	// Sync a single block to db
	private static function syncBlockToDb(array $blockDef) {
		$block = new Vtiger_Block();
		$block->initialize($blockDef);
		$block->blockTableName = $blockDef['table_name'];

		if (!$block->module) {
			return;
		}

		// Update exsiting block
		if ($block->id) {
			global $adb;

			$sql = "UPDATE {$blockDef['table_name']} SET sequence = ? WHERE blockid = ?";
			$params = [$blockDef['sequence'], $block->id];

			$adb->pquery($sql, $params);
		}
		// Create new block
		else {
			$block->id = $block->__create($block->module);
		}

		return $block->id;
	}

	// Sync a single field to db
	private static function syncFieldToDb(array $fieldDef, array $detailViewBlockDef, $moduleModel) {
		global $adb, $dbconfigoption;
		$field = new Vtiger_Field_Model();
		$field->initialize($fieldDef);

		// Re-apply meta data for EditView
		$field->editview_block_id = $fieldDef['editview_block'];
		$field->editview_sequence = $fieldDef['editview_sequence'];
		$field->editview_presence = $fieldDef['editview_presence'];

		$result = 0;

		// Update existing field
		if ($field->id) {
			$sql = "UPDATE vtiger_field
				SET block = ?, presence = ?, sequence = ?, editview_block = ?, editview_sequence = ?, editview_presence = ?, displaytype = ?, 
					typeofdata = ?, defaultvalue = ?, isunique = ?, readonly = ?, quickcreate = ?, masseditable = ?, summaryfield = ?, headerfield = ?
				WHERE fieldid = ?";
			$params = [
				$fieldDef['block'], $fieldDef['presence'], $fieldDef['sequence'], $fieldDef['editview_block'], $fieldDef['editview_sequence'], $fieldDef['editview_presence'], $fieldDef['displaytype'],
				$fieldDef['typeofdata'], $fieldDef['defaultvalue'], $fieldDef['isunique'], $fieldDef['readonly'], $fieldDef['quickcreate'], $fieldDef['masseditable'], $fieldDef['summaryfield'], $fieldDef['headerfield'],
				$field->id
			];

			$adb->pquery($sql, $params);
			$result = 1;
		}
		// Create new field
		else {
			$block = new Vtiger_Block();
			$block->initialize($detailViewBlockDef);

			// Assign field into the first block in DetailView in case the block in field def is not exists
			if (!$block->id) {
				$detailViewBlockDef = self::getFirstDetailViewBlock($moduleModel->id);
				$block->initialize($detailViewBlockDef);
			}

			$field->__create($block);
			$result = 2;
		}

		// Init picklist and multi-piclist field
		if (in_array($field->uitype, [15, 16, 33])) {
			$field->setPicklistValues('');

			// Create picklist sequence table if it is not exists
			$sequenceTableName = sprintf($dbconfigoption['seqname_format'], "vtiger_{$field->name}");

			if (!Vtiger_Utils::CheckTable($sequenceTableName)) {
				$adb->database->CreateSequence($sequenceTableName);
			}
		}
		
		// Clearing cache
		Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel, $field->getBlockId());

		return $result;
	}

	// Get the first DetailView block which can be considered as the main default block in DetailView
	private static function getFirstDetailViewBlock($moduleId) {
		global $adb;
		$sql = "SELECT * FROM vtiger_blocks WHERE tabid = ? LIMIT 1";
		$result = $adb->pquery($sql, [$moduleId]);
		$blockInfo = $adb->fetchByAssoc($result);
		if (!empty($blockInfo)) return $blockInfo;

		return [];
	}

	// Read a specific register file to write new element to arrays
	public static function readRegisterFile($moduleName, $fileType = 'base') {
		if (!in_array($fileType, ['base', 'dev', 'cus'])) return;
		$content = [
			'editViewBlocks' => [],
			'detailViewBlocks' => [],
			'fields' => []
		];

		$file = self::getRegisterFile($moduleName, $fileType);

		if (file_exists($file)) {
			include($file);

			if (empty($editViewBlocks)) $editViewBlocks = [];
			if (empty($detailViewBlocks)) $detailViewBlocks = [];
			if (empty($fields)) $fields = [];

			$content = [
				'editViewBlocks' => $editViewBlocks, 
				'detailViewBlocks' => $detailViewBlocks, 
				'fields' => $fields
			];
		}

		return $content;
	}

	// Read all register files and merge them together to sync to database
	public static function readRegisterFiles($moduleName) {
		$content = [
			'editViewBlocks' => [],
			'detailViewBlocks' => [],
			'fields' => [],
			'rawContent' => ['base' => [], 'dev' => [], 'cus' => [], 'global' => []]
		];

		$editViewBlocks = $detailViewBlocks = $fields = [];
		
		// Read base file
		$file = self::getRegisterFile($moduleName);

		if (file_exists($file)) {
			include($file);
			if (empty($editViewBlocks)) $editViewBlocks = [];
			if (empty($detailViewBlocks)) $detailViewBlocks = [];
			if (empty($fields)) $fields = [];

			$content['editViewBlocks'] = $editViewBlocks;
			$content['detailViewBlocks'] = $detailViewBlocks;
			$content['fields'] = $fields;

			$content['rawContent']['base'] = [
				'editViewBlocks' => $editViewBlocks,
				'detailViewBlocks' => $detailViewBlocks,
				'fields' => $fields
			];
		}

		// Read developer's file
		$file = self::getRegisterFile($moduleName, 'dev');

		if (file_exists($file)) {
			include($file);
			if (empty($editViewBlocks)) $editViewBlocks = [];
			if (empty($detailViewBlocks)) $detailViewBlocks = [];
			if (empty($fields)) $fields = [];

			$content['editViewBlocks'] = merge_deep_array([$content['editViewBlocks'], $editViewBlocks]);
			$content['detailViewBlocks'] = merge_deep_array([$content['detailViewBlocks'], $detailViewBlocks]);
			$content['fields'] = merge_deep_array([$content['fields'], $fields]);

			$content['rawContent']['dev'] = [
				'editViewBlocks' => $editViewBlocks,
				'detailViewBlocks' => $detailViewBlocks,
				'fields' => $fields
			];
		}

		// Read customer's file
		$file = self::getRegisterFile($moduleName, 'cus');

		if (file_exists($file)) {
			include($file);
			if (empty($editViewBlocks)) $editViewBlocks = [];
			if (empty($detailViewBlocks)) $detailViewBlocks = [];
			if (empty($fields)) $fields = [];

			$content['editViewBlocks'] = merge_deep_array([$content['editViewBlocks'], $editViewBlocks]);
			$content['detailViewBlocks'] = merge_deep_array([$content['detailViewBlocks'], $detailViewBlocks]);
			$content['fields'] = merge_deep_array([$content['fields'], $fields]);

			$content['rawContent']['cus'] = [
				'editViewBlocks' => $editViewBlocks,
				'detailViewBlocks' => $detailViewBlocks,
				'fields' => $fields
			];
		}

		// Read global config file
		$file = self::getGlobalConfigFile($moduleName);

		if (file_exists($file)) {
			include($file);
			if (empty($editViewBlocks)) $editViewBlocks = [];
			if (empty($detailViewBlocks)) $detailViewBlocks = [];
			if (empty($fields)) $fields = [];

			$content['editViewBlocks'] = merge_deep_array([$content['editViewBlocks'], $editViewBlocks]);
			$content['detailViewBlocks'] = merge_deep_array([$content['detailViewBlocks'], $detailViewBlocks]);
			$content['fields'] = merge_deep_array([$content['fields'], $fields]);

			$content['rawContent']['global'] = [
				'editViewBlocks' => $editViewBlocks,
				'detailViewBlocks' => $detailViewBlocks,
				'fields' => $fields
			];
		}

		return $content;
	}

	// Write a block into register file. Block array can be full or contains updated attributes only
	private static function writeBlockToRegisterFile($moduleName, $blockName, array $blockDef = [], $blockType = 'EditView') {
		$fileType = self::getFileTypeForSaving();
		$content = self::readRegisterFile($moduleName, $fileType);

		if ($blockType == 'EditView') {
			if (empty($content['editViewBlocks'][$blockName])) {
				$content['editViewBlocks'][$blockName] = $blockDef;
			}
			else {
				$content['editViewBlocks'][$blockName] = array_merge($content['editViewBlocks'][$blockName], $blockDef);
			}
		}
		else {
			if (empty($content['detailViewBlocks'][$blockName])) {
				$content['detailViewBlocks'][$blockName] = $blockDef;
			}
			else {
				$content['detailViewBlocks'][$blockName] = array_merge($content['detailViewBlocks'][$blockName], $blockDef);
			}
		}
		
		self::writeRegisterFile($moduleName, $content, $fileType);
	}

	// Write a field into register file. Field array can be full or contains updated attributes only
	private static function writeFieldToRegisterFile($moduleName, $fieldName, array $fieldDef = []) {
		$fileType = self::getFileTypeForSaving();
		$content = self::readRegisterFile($moduleName, $fileType);

		if (empty($content['fields'][$fieldName])) {
			$content['fields'][$fieldName] = $fieldDef;
		}
		else {
			$content['fields'][$fieldName] = array_merge($content['fields'][$fieldName], $fieldDef);
		}
		
		self::writeRegisterFile($moduleName, $content, $fileType);
	}

	private static function writeRegisterFile($moduleName, array $content, $fileType) {
		if (!in_array($fileType, ['base', 'dev', 'cus'])) return;
		$file = self::getRegisterFile($moduleName, $fileType);
		$message = '';

		if ($fileType == 'cus') {
			$message = "\n\tTHIS FILE IS FOR CUSTOMER TO UPDATE FROM LAYOUT EDITOR. YOU MUST BACKUP THIS FILE TO YOUR PROJECT REPO AND DO NOT MODIFY THIS FILE MANUALLY!!!";
		}
		else {
			$message = "\n\tTHIS FILE IS FOR DEVELOPER TO UPDATE FROM LAYOUT EDITOR. YOU CAN MODIFY THIS FILE FOR CUSTOMIZING BUT REMEMBER THAT ALL COMMENTS WILL BE REMOVED!!!";
		}

		FileUtils::writeArrayToFile($content, $file, $message);
	}

	static function getRegisterFile($moduleName, $fileType = 'base') {
		if (!in_array($fileType, ['base', 'dev', 'cus'])) return;
		$file = "modules/{$moduleName}/BlocksAndFieldsRegister". ($fileType != 'base' ? ".{$fileType}" : '') .'.php';
		return $file;
	}

	static function getGlobalConfigFile($moduleName) {
		return "modules/{$moduleName}/GlobalBlocksAndFieldsConfig.php";
	}

	static function getEntityModules() {
		global $adb;

		$sql = "SELECT tabid AS id, name FROM vtiger_tab WHERE isentitytype = 1 OR name = 'Users'";
		$result = $adb->pquery($sql);
		$entityModules = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$entityModules[$row['name']] = $row;
		}

		return $entityModules;
	}

	// Return a map by name
	private static function getBlocksAndFieldsMap($moduleId) {
		global $adb;

		$editViewBlocksMap = [];
		$detailViewBlocksMap = [];
		$fieldsMap = [];

		// Get EditView Blocks
		$sql = "SELECT * FROM vtiger_editview_blocks WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$editViewBlocksMap[$row['blocklabel']] = $row;
		}

		// Get DetailView Blocks
		$sql = "SELECT * FROM vtiger_blocks WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$detailViewBlocksMap[$row['blocklabel']] = $row;
		}

		// Get Fields
		$sql = "SELECT * FROM vtiger_field WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$fieldsMap[$row['fieldname']] = $row;
		}

		$map = [
			'editview_blocks' => $editViewBlocksMap, 
			'detailview_blocks' => $detailViewBlocksMap,
			'fields' => $fieldsMap
		];

		return $map;
	}

	private static function getBlocksAndFields($moduleId) {
		global $adb;

		$editViewBlocksMap = [];
		$detailViewBlocksMap = [];
		$blocksAndFields = [
			'editview_blocks' => [],
			'detailview_blocks' => [],
			'fields' => [],
		];

		// Get EditView Blocks
		$sql = "SELECT * FROM vtiger_editview_blocks WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$editViewBlocksMap[$row['blockid']] = $row;
			$blocksAndFields['editview_blocks'][$row['blocklabel']] = self::trimBlockDef($row);
		}

		// Get DetailView Blocks
		$sql = "SELECT * FROM vtiger_blocks WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			$detailViewBlocksMap[$row['blockid']] = $row;
			$blocksAndFields['detailview_blocks'][$row['blocklabel']] = self::trimBlockDef($row);
		}

		// Get Fields
		$sql = "SELECT * FROM vtiger_field WHERE tabid = ?";
		$result = $adb->pquery($sql, [$moduleId]);

		while ($row = $adb->fetchByAssoc($result)) {
			// Save column type to be able to re-create the correct column type when repair
			$row['columntype'] = self::getColumnType($row['tablename'], $row['columnname']);

			$editViewBlock = $editViewBlocksMap[$row['editview_block']];
			$detailViewBlock = $detailViewBlocksMap[$row['block']];
			$row['editview_block_name'] = $editViewBlock['blocklabel'];
			$row['detailview_block_name'] = $detailViewBlock['blocklabel'];
			$blocksAndFields['fields'][$row['fieldname']] = self::trimFieldDef($row);
		}

		return $blocksAndFields;
	}

	private static function getColumnType($tableName, $columnName) {
		global $adb;
		$sql = "SHOW COLUMNS FROM {$tableName} WHERE FIELD = '{$columnName}'";
		$result = $adb->pquery($sql, []);
		$columnInfo = $adb->fetchByAssoc($result);

		return $columnInfo['type'];
	}

	// Attach edit view and detail view blocks name into the field def before writing into register file
	private static function attachBlocksName($fieldDef) {
		global $adb;

		$sql = "SELECT blocklabel FROM vtiger_editview_blocks WHERE blockid = ?";
		$fieldDef['editview_block_name'] = $adb->getOne($sql, [$fieldDef['editview_block']]);

		$sql = "SELECT blocklabel FROM vtiger_editview_blocks WHERE blockid = ?";
		$fieldDef['detailview_block_name'] = $adb->getOne($sql, [$fieldDef['block']]);

		return $fieldDef;
	}

	// Remove unnecessary block data before writing into register file
	private static function trimBlockDef(array $blockDef) {
		unset($blockDef['tabid']);
		unset($blockDef['blockid']);

		return $blockDef;
	}

	// Remove unnecessary field data before writing into register file
	private static function trimFieldDef(array $fieldDef) {
		unset($fieldDef['tabid']);
		unset($fieldDef['fieldid']);
		unset($fieldDef['block']);
		unset($fieldDef['editview_block']);

		return $fieldDef;
	}

	// Set field as visible and editable for all roles
	static function setFieldAccessibleForAllRoles($moduleId, $fieldId) {
		global $adb;
		$sql = "UPDATE vtiger_profile2field SET visible = 0, readonly = 0 WHERE tabid = ? AND fieldid = ?";
		$adb->pquery($sql, [$moduleId, $fieldId]);
	}
}