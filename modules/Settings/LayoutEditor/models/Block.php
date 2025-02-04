<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Block_Model extends Vtiger_Block_Model {

    public function isActionsAllowed() {
        if(strtolower($this->module->name) == 'events' && $this->get('label') == 'LBL_INVITE_USER_BLOCK') {
			return false;
		}
		return true;
	}

    /**
	 * Function to check whether adding custom field is allowed or not
	 * @return <Boolean> true/false
	 */
	public function isAddCustomFieldEnabled() {
	    // Modified by Hieu Nguyen on 2018-09-17 to load external config
        /*$actionNotSupportedModules = array_merge(getInventoryModules(), array('Calendar', 'Events', 'Faq', 'HelpDesk'));
		$blocksEliminatedArray = array(	'Calendar'		=> array('LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION'),
										'HelpDesk'		=> array('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'),
										'Faq'			=> array('LBL_COMMENT_INFORMATION'),
										'Invoice'		=> array('LBL_ITEM_DETAILS'),
										'Quotes'		=> array('LBL_ITEM_DETAILS'),
										'SalesOrder'	=> array('LBL_ITEM_DETAILS'),
										'PurchaseOrder'	=> array('LBL_ITEM_DETAILS'),
										'Events'		=> array('LBL_INVITE_USER_BLOCK'));
        if(in_array($this->module->name, $actionNotSupportedModules)) {
			if(!empty($blocksEliminatedArray[$this->module->name])) {
				if(in_array($this->get('label'), $blocksEliminatedArray[$this->module->name])) {
					return false;
				}
			} else {
				return false;
			}
		}*/

        global $layoutEditorConfig;
        $blocksEliminatedArray = $layoutEditorConfig['prevent_custom_field'];

        if(!empty($blocksEliminatedArray[$this->module->name])) {
            if(in_array($this->get('label'), $blocksEliminatedArray[$this->module->name])) {
                return false;
            }
        }
        // End Hieu Nguyen

        return true;
    }

	// Added by Hieu Nguyen on 2021-07-29 to check if block can be deleted by customer, dev or R&D
	public function isDeletable() {
		$registerFileType = Vtiger_BlockAndField_Helper::getFileTypeForSaving();
		$deletable = $this->isCustomized() && $this->isCreatedIn($registerFileType);
		return $deletable;
	}

	// Added by Hieu Nguyen on 2021-07-29 to check if block is original created in cus, dev or base register file
	public function isCreatedIn($registerFileType) {
		$fileContent = Vtiger_BlockAndField_Helper::readRegisterFile($this->module->name, $registerFileType);
		$blockType = ($_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editViewBlocks' : 'detailViewBlocks');
		$blockDef = $fileContent[$blockType][$this->label];
		return count(array_keys($blockDef)) >= Vtiger_BlockAndField_Helper::FULL_BLOCK_DEF_ATTRIBUTES_NUM;
	}

    public static function updateFieldSequenceNumber($blockFieldSequence, $moduleModel = false) {
        $fieldIdList = array();
        $db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_field SET ';
		
		// Modified by Hieu Nguyen on 2018-08-27
		$blockField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_block' : 'block';
		$sequenceField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_sequence' : 'sequence';

		$query .= " {$sequenceField} = CASE ";
		
        foreach($blockFieldSequence as $newFieldSequence ) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$block = $newFieldSequence['block'];
            $fieldIdList[] = $fieldId;

			$query .= ' WHEN fieldid='.$fieldId.' THEN '.$sequence;
        }

		$query .= " END, {$blockField} = CASE ";
		// End Hieu Nguyen

		foreach($blockFieldSequence as $newFieldSequence ) {
			$fieldId = $newFieldSequence['fieldid'];
			$sequence = $newFieldSequence['sequence'];
			$block = $newFieldSequence['block'];
			$query .= ' WHEN fieldid='.$fieldId.' THEN '.$block;
		}
		$query .=' END ';

        $query .= ' WHERE fieldid IN ('.generateQuestionMarks($fieldIdList).')';
        
        $db->pquery($query, array($fieldIdList));
        
        // Clearing cache
        Vtiger_Cache::flushModuleandBlockFieldsCache($moduleModel);
    }

    public static function getInstance($value, $moduleInstance = false) {
		$blockInstance = parent::getInstance($value, $moduleInstance);
		$blockModel = self::getInstanceFromBlockObject($blockInstance);
		return $blockModel;
	}

	/**
	 * Function to retrieve block instance from Vtiger_Block object
	 * @param Vtiger_Block $blockObject - vtlib block object
	 * @return Vtiger_Block_Model
	 */
	public static function getInstanceFromBlockObject(Vtiger_Block $blockObject) {
		$objectProperties = get_object_vars($blockObject);
		$blockModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$blockModel->$properName = $propertyValue;
		}
		return $blockModel;
	}

    /**
	 * Function to retrieve block instances for a module
	 * @param <type> $moduleModel - module instance
	 * @return <array> - list of Vtiger_Block_Model
	 */
	public static function getAllForModule($moduleModel) {
		$blockObjects = parent::getAllForModule($moduleModel);
		$blockModelList = array();

		if($blockObjects) {
			// Modified by Hieu Nguyen on 2018-08-17 to load label display in both english and vietnamese for editing
			require_once('include/utils/LangUtils.php');
			$modStringsEn = LangUtils::readModStrings($moduleModel->getName(), 'en_us');
			$modStringsVn = LangUtils::readModStrings($moduleModel->getName(), 'vn_vn');

			foreach($blockObjects as $blockObject) {
				$blockModel = self::getInstanceFromBlockObject($blockObject);
				$blockModel->labelDisplayEn = $modStringsEn['languageStrings'][$blockModel->label];
				$blockModel->labelDisplayVn = $modStringsVn['languageStrings'][$blockModel->label];

				$blockModelList[] = $blockModel;
			}
			// End Hieu Nguyen
		}
		return $blockModelList;
	}

	public function getLayoutBlockActiveFields() {
		$fields = $this->getFields();
		$activeFields = array();
		foreach($fields as $fieldName => $fieldModel) {
			if ($fieldModel->get('displaytype') != 3 && $fieldModel->getDisplayType() != 6 && $fieldModel->isActiveField() && ($fieldModel->get('uitype') != '83'
					|| ($fieldModel->get('uitype') == '83' && $fieldName == 'taxclass' && in_array($this->module->name, array('Products', 'Services'))))) {
				$activeFields[$fieldName] = $fieldModel;
			}
		}
		return $activeFields;
	}

	public function getCustomFieldsCount() {
		$customFieldsCount = 0;
		$blockFields = $this->getFields();
		foreach ($blockFields as $fieldName => $fieldModel) {
			if ($fieldModel && $fieldModel->isCustomField()) {
				$customFieldsCount++;
			}
		}
		return $customFieldsCount;
	}

	public function getFields() {
		if (!$this->fields) {
			$blockFields = parent::getFields();
			$this->fields = array();

			foreach ($blockFields as $fieldName => $fieldModel) {
				$fieldModel = Settings_LayoutEditor_Field_Model::getInstanceFromFieldObject($fieldModel);
				$this->fields[$fieldName] = $fieldModel;
			}
		}
		return $this->fields;
	}
}
