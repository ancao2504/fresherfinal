<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Field_Action extends Settings_Vtiger_Index_Action {

    function __construct() {
		parent::__construct();
        $this->view = 'DetailView'; // Added by Hieu Nguyen on 2019-03-01
        $this->exposeMethod('add');
        $this->exposeMethod('save');
        $this->exposeMethod('delete');
        $this->exposeMethod('updateFieldSequence');   // Modified by Hieu Nguyen on 2021-08-02
        $this->exposeMethod('unHide');
		$this->exposeMethod('updateDuplicateHandling');
    }

    public function add(Vtiger_Request $request) {
        $type = $request->get('fieldType');
        $moduleName = $request->get('sourceModule');
        $blockId = $request->get('blockid');
        $moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
        $response = new Vtiger_Response();
        try{
            $fieldModel = $moduleModel->addField($type,$blockId,$request->getAll());
            $fieldInfo = $fieldModel->getFieldInfo();
            $responseData = array_merge(array('id'=>$fieldModel->getId(), 'blockid'=>$blockId, 'customField'=>$fieldModel->isCustomField()),$fieldInfo);

			$defaultValue = $fieldModel->get('defaultvalue');
			$responseData['fieldDefaultValueRaw'] = $defaultValue;
			if (isset($defaultValue)) {
				if ($defaultValue && $fieldInfo['type'] == 'date') {
					$defaultValue = DateTimeField::convertToUserFormat($defaultValue);
				} else if (!$defaultValue) {
					$defaultValue = $fieldModel->getDisplayValue($defaultValue);
				} else if (is_array($defaultValue)) {
					foreach ($defaultValue as $key => $value) {
						$defaultValue[$key] = $fieldModel->getDisplayValue($value);
					}
					$defaultValue = Zend_Json::encode($defaultValue);
				}
			}
			$responseData['fieldDefaultValue'] = $defaultValue;

            // Added by Hieu Nguyen on 2021-06-14 to track for label changes
            require_once('include/utils/LangUtils.php');
            $labelKey = trim($request->get('fieldLabel'));
            $labelDisplayEn = trim($request->get('fieldLabelDisplayEn'));
            $labelDisplayVn = trim($request->get('fieldLabelDisplayVn'));

            $languageStrings = [$labelKey => $labelDisplayEn];
            LangUtils::writeModStrings($languageStrings, [], $moduleName, 'en_us');

            $languageStrings = [$labelKey => $labelDisplayVn];
            LangUtils::writeModStrings($languageStrings, [], $moduleName, 'vn_vn');

            global $current_user;
            $responseData['labelDisplay'] = ($current_user->language == 'vn_vn') ? $labelDisplayVn : $labelDisplayEn;
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2019-03-01
            Vtiger_BlockAndField_Helper::syncFieldToRegisterFile($moduleModel, $fieldModel->name);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2021-08-02 to save audit log
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Add Field {$fieldModel->name}", $fieldModel);
            // End Hieu Nguyen

            $response->setResult($responseData);
        }catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }

    // Modified by Hieu Nguyen on 2021-08-02 to handle saving register file and audit log
    public function save(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
        $fieldId = $request->get('fieldid');
        $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
        $oldFieldInstance = clone $fieldInstance;   // For tracking
        
        // Modified by Hieu Nguyen on 2018-08-03 to allow updating field label
        $fieldLabel = trim($request->get('fieldLabel'));
        $mandatory = $request->get('mandatory', null);
        $presence = $request->get('presence', null);
        $quickCreate = $request->get('quickcreate', null);
        $summaryField = $request->get('summaryfield', null);
        $massEditable = $request->get('masseditable', null);
        $headerField = $request->get('headerfield', null);

		if (!empty($fieldLabel)) {
			$fieldInstance->set('label', $fieldLabel);
        }
        // End Hieu Nguyen

        // Modified by Hieu Nguyen on 2021-07-08 to track changed attributes
        $changedAttributes = [];
        $curMandatory = explode('~', $fieldInstance->get('typeofdata')[1]);

		if (!empty($mandatory) && strtoupper($mandatory) != $curMandatory) {
            $fieldInstance->updateTypeofDataFromMandatory($mandatory);
            $changedAttributes['typeofdata'] = $fieldInstance->get('typeofdata');
        }

        if ($presence != null && $presence != $fieldInstance->get('presence')) {
            $presenceField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_presence' : 'presence';
            $changedAttributes[$presenceField] = $presence;
            $fieldInstance->set('presence', $presence);
        }
        
        if ($quickCreate != null && $quickCreate != $fieldInstance->get('quickcreate')) {
            $changedAttributes['quickcreate'] = $quickCreate;
            $fieldInstance->set('quickcreate', $quickCreate);
        }
        
        if ($summaryField != null && $summaryField != $fieldInstance->get('summaryfield')) {
            $changedAttributes['summaryfield'] = $summaryField;
            $fieldInstance->set('summaryfield', $summaryField);
        }
        
        if ($headerField != null && $headerField != $fieldInstance->get('headerfield')) {
            $changedAttributes['headerfield'] = $headerField;
            $fieldInstance->set('headerfield', $headerField);
        }
        
        if ($massEditable != null && $massEditable != $fieldInstance->get('masseditable')) {
            $changedAttributes['masseditable'] = $massEditable;
            $fieldInstance->set('masseditable', $massEditable);
        }
        
        if ($request->get('fieldDefaultValue', null) != null) {
            $defaultValue = decode_html($request->get('fieldDefaultValue'));

            if (isset($defaultValue) && $defaultValue != $fieldInstance->get('defaultvalue')) {
                $changedAttributes['defaultvalue'] = $defaultValue;
                $fieldInstance->set('defaultvalue', $defaultValue);
            }
        }
        // End Hieu Nguyen

		$response = new Vtiger_Response();

        try {
            $fieldInstance->save();
			$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
			$fieldInfo = $fieldInstance->getFieldInfo();
			$fieldInfo['id'] = $fieldInstance->getId();

			if (isset($defaultValue)) {
			    $fieldInfo['fieldDefaultValueRaw'] = $defaultValue;

				if ($defaultValue && $fieldInfo['type'] == 'date') {
					$defaultValue = DateTimeField::convertToUserFormat($defaultValue);
				}
                else if (!$defaultValue) {
					$defaultValue = $fieldInstance->getDisplayValue($defaultValue);
				}
                else if (is_array($defaultValue)) {
					foreach ($defaultValue as $key => $value) {
						$defaultValue[$key] = $fieldInstance->getDisplayValue($value);
					}

					$defaultValue = Zend_Json::encode($defaultValue);
				}
                
                $fieldInfo['fieldDefaultValue'] = $defaultValue;
			}
            else {
                $defaultValue = $fieldInstance->get('defaultvalue');
                $fieldInfo['fieldDefaultValueRaw'] = $defaultValue;
                $fieldInfo['fieldDefaultValue'] = $defaultValue;
            }

            // Added by Hieu Nguyen on 2021-08-23 to fix permission of required field
            if ($mandatory) {
                Vtiger_BlockAndField_Helper::setFieldAccessibleForAllRoles($fieldInstance->block->module->id, $fieldInstance->getId());
            }
            // End Hieu Nguyen
            
            // Added by Hieu Nguyen on 2018-08-17
            require_once('include/utils/LangUtils.php');
            $moduleName = $fieldInstance->block->module->name;
            $fieldLabel = $fieldInstance->get('label');

            if ($request->get('fieldLabelDisplayEnChanged') == '1') {
                $languageStrings = [$fieldLabel => trim($request->get('fieldLabelDisplayEn'))];
                LangUtils::writeModStrings($languageStrings, [], $moduleName, 'en_us');
            }

            if ($request->get('fieldLabelDisplayVnChanged') == '1') {
                $languageStrings = [$fieldLabel => trim($request->get('fieldLabelDisplayVn'))];
                LangUtils::writeModStrings($languageStrings, [], $moduleName, 'vn_vn');
            }
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2019-03-01
            Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($request->get('sourceModule'), $fieldInstance->get('name'), $changedAttributes);
            // End Hieu Nguyen

            // Added by Hieu Nguyen on 2021-08-02 to save audit log
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update Field {$fieldInstance->name}", ['old_field' => $oldFieldInstance, 'changed_attributes' => $changedAttributes]);
            // End Hieu Nguyen

            $response->setResult(array_merge(array('success'=>true), $fieldInfo));
        }
        catch (Exception $e) {
			$response->setError($e->getCode(), $e->getMessage());
		}
        
		$response->emit();
	}

    // Modified by Hieu Nguyen on 2021-08-02 to Check if this block can be deleted by customer, dev or R&D
    public function delete(Vtiger_Request $request) {
        $fieldId = $request->get('fieldid');
        $fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
        $response = new Vtiger_Response();

        // Check if this block can be deleted by customer, dev or R&D
        if (!$fieldInstance->isEditable() || !$fieldInstance->isDeletable()) {
            $moduleName = $request->getModule(false);
            $developerTeam = checkDeveloperTeam();
            $message = vtranslate('LBL_DELETE_SYSTEM_FIELD_ERROR_MSG', $moduleName);

            if ($developerTeam == 'DEV') {
                $message = vtranslate('LBL_DELETE_SYSTEM_FIELD_ERROR_MSG_FOR_DEV', $moduleName);
            }

            $response->setError('122', $message);
            $response->emit();
            return;
        }

        try {
            $this->_deleteField($fieldInstance);
        }
        catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }
    
    private function _deleteField($fieldInstance) {
        $sourceModule = $fieldInstance->get('block')->module->name;
        $fieldLabel = $fieldInstance->get('label');
        if($fieldInstance->uitype == 16 || $fieldInstance->uitype == 33){
            $pickListValues = Settings_Picklist_Field_Model::getEditablePicklistValues ($fieldInstance->name);
            $fieldLabel = array_merge(array($fieldLabel),$pickListValues);
        }
        $fieldInstance->delete();

        // Added by Hieu Nguyen on 2019-03-01
        Vtiger_BlockAndField_Helper::removeFieldFromRegisterFile($sourceModule, $fieldInstance->name);
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2021-08-02 to save audit log
        Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Delete Field {$fieldInstance->name}", $fieldInstance);
        // End Hieu Nguyen

        // Removed the logic by Hieu Nguyen on 2021-06-14 to prevent error
        //Settings_LayoutEditor_Module_Model::removeLabelFromLangFile($sourceModule, $fieldLabel);
        // End Hieu Nguyen

        //we should delete any update field workflow associated with custom field
        $moduleName = $fieldInstance->getModule()->getName();
        Settings_Workflows_Record_Model::deleteUpadateFieldWorkflow($moduleName, $fieldInstance->getFieldName());
    }

    // Modified by Hieu Nguyen on 2021-08-02
    public function updateFieldSequence(Vtiger_Request $request) {
        $sourceModule = $request->get('selectedModule');
        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $updatedFieldsList = $request->get('updatedFields');
        
        // Get old field instance for tracking
        $oldFieldInstances = [];

        foreach ($updatedFieldsList as $updatedFieldInfo) {
            $fieldInstance = Vtiger_Field::getInstance($updatedFieldInfo['fieldid'], $moduleModel); // Use this function to prevent caching
            $oldFieldInstances[] = $fieldInstance;
        }

		// Update the fields sequence
        Settings_LayoutEditor_Block_Model::updateFieldSequenceNumber($updatedFieldsList, $moduleModel);
        
        // Then save new block and sequence of moved fields into register file
        foreach ($updatedFieldsList as $i => $updatedFieldInfo) {
            $oldFieldInstance = $oldFieldInstances[$i];
            $fieldInstance = Vtiger_Field::getInstance($updatedFieldInfo['fieldid'], $moduleModel); // Use this function to prevent caching
            
            $changedAttributes = [];

            if ($fieldInstance->block->id != $oldFieldInstance->block->id) {
                $blockField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_block_name' : 'detailview_block_name';
                $changedAttributes[$blockField] = $fieldInstance->block->label;
            }

            if ($fieldInstance->sequence != $oldFieldInstance->sequence) {
                $sequenceField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_sequence' : 'sequence';
                $changedAttributes[$sequenceField] = $fieldInstance->sequence;
            }

            if (!empty($changedAttributes)) {
                Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($sourceModule, $fieldInstance->name, $changedAttributes);
            }
        }

        // Save audit log
        $updatedView = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'EditView' : 'DetailView';
        Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update {$updatedView} Layout", ['old_layout' => $oldFieldInstances, 'new_layout', $updatedFieldsList]);

        $response = new Vtiger_Response();
		$response->setResult(['success' => true]);
        $response->emit();
    }

    // Modified by Hieu Nguyen on 2021-07-01 to save register file and audit log
    public function unHide(Vtiger_Request $request) {
        $response = new Vtiger_Response();

        try {
			$fieldIds = $request->get('fieldIdList');
            Settings_LayoutEditor_Field_Model::makeFieldActive($fieldIds, $request->get('blockId'), $request->get('selectedModule'));
			$responseData = [];

			foreach ($fieldIds as $fieldId) {
				$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);

                // Modified by Hieu Nguyen on 2021-07-08 to save changes into register file
                $blockField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_block_name' : 'detailview_block_name';
                $sequenceField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_sequence' : 'sequence';
                $presenceField = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'editview_presence' : 'presence';
                
                $changedAttributes = [
                    $blockField => $fieldModel->get('block')->label,
                    $sequenceField => $fieldModel->get('sequence'), 
                    $presenceField => $fieldModel->get('presence')
                ];

                Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($request->get('selectedModule'), $fieldModel->getName(), $changedAttributes);
                // End Hieu Nguyen

				$fieldInfo = $fieldModel->getFieldInfo();
                $additionInfo = ['id' => $fieldModel->getId(), 'blockid' => $fieldModel->get('block')->id, 'customField' => $fieldModel->isCustomField()];
				$responseData[] = array_merge($fieldInfo, $additionInfo);
			}

            // Save audit log
            $updatedView = $_REQUEST['layouteditor_tab'] == 'editViewTab' ? 'EditView' : 'DetailView';
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Unhide Fields In {$updatedView}", $responseData);

            $response->setResult($responseData);
        }
        catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }

        $response->emit();
    }

    // Modified by Hieu Nguyen on 2021-07-01 to save changes into register file
	public function updateDuplicateHandling(Vtiger_Request $request) {
		$response = new Vtiger_Response();

		try {
			$moduleName = $request->get('sourceModule');
			$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);

			$newUniqueFieldIds = !empty($request->get('fieldIdsList')) ? $request->get('fieldIdsList'): [];
            $curUniqueFieldIds = Vtiger_Field_Model::getUniqueFields($moduleName, true);

			$result = $moduleModel->updateDuplicateHandling($request->get('rule'), $newUniqueFieldIds, $request->get('syncActionId'));

            // Save attribute isunique=1 for selected fields
            foreach ($newUniqueFieldIds as $fieldId) {
                $fieldInstance = Vtiger_Field_Model::getInstance($fieldId, $moduleModel);
                Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($moduleName, $fieldInstance->get('name'), ['isunique' => 1]);
            }

            $removedFieldIds = array_diff($curUniqueFieldIds, $newUniqueFieldIds);

            // Save attribute isunique=0 for removed fields
            foreach ($removedFieldIds as $fieldId) {
                $fieldInstance = Vtiger_Field_Model::getInstance($fieldId, $moduleModel);
                Vtiger_BlockAndField_Helper::saveFieldAttributesToRegisterFile($moduleName, $fieldInstance->get('name'), ['isunique' => 0]);
            }

            // Save audit log
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update Duplicate Check Fields", ['old_fields' => $curUniqueFieldIds, 'new_fields' => $newUniqueFieldIds]);

			$response->setResult($result);
		}
        catch (Exception $ex) {
			$response->setError($ex->getCode(), $ex->getMessage());
		}

		$response->emit();
	}

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}