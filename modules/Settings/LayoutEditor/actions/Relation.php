<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Relation_Action extends Settings_Vtiger_Index_Action {
    
    // Addd by Hieu Nguyen on 2018-09-04
    public function __construct() {
        $this->exposeMethod('add');
        $this->exposeMethod('update');
    }

    // Implemented by Hieu Nguyen on 2018-09-04
    function add(Vtiger_Request $request) {
        $leftSideModule = $request->get('leftSideModule');
        $rightSideModule = $request->get('rightSideModule');
        $relationType = $request->get('relationType');
        $listingFunctionName = $request->get('listingFunctionName');
        $leftSideReferenceField = $request->get('leftSideReferenceField');
        $rightSideReferenceField = $request->get('rightSideReferenceField');
        $relationLabelKey = $request->get('relationLabelKey');
        $relationLabelDisplayEn = $request->get('relationLabelDisplayEn');
        $relationLabelDisplayVn = $request->get('relationLabelDisplayVn');
        $response = new Vtiger_Response();

        $leftSideModuleInstance = Vtiger_Module::getInstance($leftSideModule);
        $rightSideModuleInstance = Vtiger_Module::getInstance($rightSideModule);

        // Validate
        if ($leftSideModule == null || $rightSideModule == null) {
            $response->setResult(array('success' => false, 'message' => 'MODULE_NOT_EXISTS'));
            $response->emit();
            return;
        }

        require_once('include/utils/RelationshipUtils.php');
        if (empty($listingFunctionName)) $listingFunctionName = NULL;

        // Check for duplicate relationship
        $isExists = RelationshipUtils::isRelationshipExists($leftSideModuleInstance->getId(), $rightSideModuleInstance->getId(), $relationType, $listingFunctionName);

        if ($isExists) {
            $message = 'RELATIONSHIP_EXISTS';

            if ($relationType == 'N:N') {
                $message = 'RELATIONSHIP_N2N_EXISTS';
            }

            $response->setResult(array('success' => false, 'message' => $message));
            $response->emit();
            return;
        }

        // Check for duplicate reference field
        if (!empty($leftSideReferenceField) && RelationshipUtils::isReferenceFieldExists($leftSideModuleInstance, $leftSideReferenceField)) {
            $response->setResult(array('success' => false, 'message' => 'LEFT_SIDE_REFERENCE_FIELD_EXISTS'));
            $response->emit();
            return;
        }

        if (!empty($rightSideReferenceField) && RelationshipUtils::isReferenceFieldExists($rightSideModuleInstance, $rightSideReferenceField)) {
            $response->setResult(array('success' => false, 'message' => 'RIGHT_SIDE_REFERENCE_FIELD_EXISTS'));
            $response->emit();
            return;
        }

        // Process
        require_once('include/utils/FileUtils.php');
        require_once('include/utils/LangUtils.php');

        // Create reference fields
        if ($relationType == '1:1' || $relationType == '1:N') {
            // Create left side reference field
            if ($relationType == '1:1') {
                $leftSideDefaultBlock = $leftSideModuleInstance->getDefaultBlock();

                $field = new Vtiger_Field();
                $field->name = $leftSideReferenceField;
                $field->column = $leftSideReferenceField;
                $field->table = $leftSideModuleInstance->basetable;
                $field->columntype = 'VARCHAR(15)';
                $field->uitype = 10;
                $field->typeofdata = 'V~O';
                $field->label = $rightSideModuleInstance->label;

                $leftSideDefaultBlock->addField($field);

                // Added by Hieu Nguyen on 2019-03-11
                Vtiger_BlockAndField_Helper::syncFieldToRegisterFile($leftSideModuleInstance, $field->name);
                // End Hieu Nguyen
            }

            // Create right side reference field
            $rightSideDefaultBlock = $rightSideModuleInstance->getDefaultBlock();

            $field = new Vtiger_Field();
            $field->name = $rightSideReferenceField;
            $field->column = $rightSideReferenceField;
            $field->table = $rightSideModuleInstance->basetable;
            $field->columntype = 'VARCHAR(15)';
            $field->uitype = 10;
            $field->typeofdata = 'V~O';
            $field->label = $leftSideModuleInstance->label;

            $rightSideDefaultBlock->addField($field);

            // Added by Hieu Nguyen on 2019-03-11
            Vtiger_BlockAndField_Helper::syncFieldToRegisterFile($rightSideModuleInstance, $field->name);
            // End Hieu Nguyen
        }

        // Write module language
        $languageStrings = [$relationLabelKey => $relationLabelDisplayEn];
        LangUtils::writeModStrings($languageStrings, [], $leftSideModule, 'en_us');

        $languageStrings = [$relationLabelKey => $relationLabelDisplayVn];
        LangUtils::writeModStrings($languageStrings, [], $leftSideModule, 'vn_vn');

        // Write data into register file inside left side module folder
        global $relationships;
        $registerFile = 'modules/'. $leftSideModule .'/RelationshipsRegister.php';

        if (!file_exists($registerFile)) {
            file_put_contents($registerFile, '');
        }

        require($registerFile);

        $enabledActions = ['ADD', 'SELECT'];   // Only N:N relationship can have both buttons
        if ($relationType == '1:N') $enabledActions = ['ADD'];    // 1:N relationship has only ADD button
        if ($relationType == '1:1') $enabledActions = NULL;    // 1:1 relationship has no button

        $newRelationship = [
            'leftSideModule' => $leftSideModule,
            'rightSideModule' => $rightSideModule,
            'relationshipType' => $relationType,
            'relationshipName' => $relationLabelKey,
            'enabledActions' => $enabledActions,
            'listingFunctionName' => !empty($listingFunctionName) ? $listingFunctionName : NULL,
            'leftSideReferenceFieldName' => !empty($leftSideReferenceField) ? $leftSideReferenceField : NULL,
            'rightSideReferenceFieldName' => !empty($rightSideReferenceField) ? $rightSideReferenceField : NULL
        ];

        $relationships[] = $newRelationship;
        FileUtils::writeArrayToFile(['relationships' => $relationships], $registerFile);

        // Save audit log
        Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Create a new {$relationType} relationship in module {$leftSideModule}", $newRelationship);

        // Repair
        require_once('include/utils/RelationshipUtils.php');
        RelationshipUtils::repairRelationships($leftSideModule);

        // Respond
        $response->setResult(['success' => true]);
        $response->emit();
    }

    // Renamed this function by Hieu Nguyen on 2018-09-04
    public function update(Vtiger_Request $request) {
        $relationInfo = $request->get('related_info');
        $updatedRelatedList = $relationInfo['updated'];
        $deletedRelatedList = $relationInfo['deleted'];
		if(empty($updatedRelatedList)) {
			$updatedRelatedList = array();
		}
		if(empty($deletedRelatedList)) {
			$deletedRelatedList = array();
		}
        $sourceModule = $request->get('sourceModule');
        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule, false);
        $relationModulesList = Vtiger_Relation_Model::getAllRelations($moduleModel, false);
        $sequenceList = array();
        foreach($relationModulesList as $relationModuleModel) {
            $sequenceList[] = $relationModuleModel->get('sequence');
        }
        //To sort sequence in ascending order
        sort($sequenceList);
        $relationUpdateDetail = array();
        $index = 0;
        foreach($updatedRelatedList as $relatedId) {
            $relationUpdateDetail[] = array('relation_id' => $relatedId, 'sequence' => $sequenceList[$index++] , 'presence' => 0);
        }
        foreach($deletedRelatedList as $relatedId) {
            $relationUpdateDetail[] = array('relation_id'=> $relatedId, 'sequence' => $sequenceList[$index++], 'presence' => 1);
        }
        $response = new Vtiger_Response();
        try{
            $response->setResult(array('success'=> true));
            Vtiger_Relation_Model::updateRelationSequenceAndPresence($relationUpdateDetail, $moduleModel->getId());

            // Added by Hieu Nguyen on 2021-08-06 to save audit log
            Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Updated existing relationship in module {$sourceModule}", ['changed' => $relationInfo, 'new_sequence' => $relationUpdateDetail]);
            // End Hieu Nguyen
        }
        catch(Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        }
        $response->emit();
    }
    
    public function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    } 
}