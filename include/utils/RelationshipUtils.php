<?php

/*
    class RelationshipUtils
    Author: Hieu Nguyen
    Date: 2018-09-04
    Purpose: handle all custom logic related to Relationship
*/

class RelationshipUtils {

    // Check if a relationship is exists
    static function isRelationshipExists($leftSideModuleId, $rightSideModuleId, $relationshipType, $listingFunctionName = null) {
        $db = PearDatabase::getInstance();
        $isExists = false;

        // Check for duplicate relationship
        if($relationshipType == 'N:N') {
            // Each pair of modules can have only 1 N:N relationship
            $sql = "SELECT 1 FROM vtiger_relatedlists
                WHERE tabid = {$leftSideModuleId} AND related_tabid = {$rightSideModuleId} 
                    AND (relationtype = 'N:N' OR relationtype IS NULL)";
            $isExists = $db->getOne($sql);
        }
        else {
            // Each pair of modules can have only many 1:N and 1:1 relationships, but should not be duplicated
            $sql = "SELECT 1 FROM vtiger_relatedlists
                WHERE tabid = {$leftSideModuleId} AND related_tabid = {$rightSideModuleId} 
                    AND relationtype = '{$relationshipType}' 
                    AND name ". ($listingFunctionName == null ? 'IS NULL' : "= '" . $listingFunctionName . "'");
            $isExists = $db->getOne($sql);
        }

        return $isExists;
    }

    // Check if a reference field is exists
    public static function isReferenceFieldExists($moduleInstance, $fieldName) {
        global $adb;

        $sql = "SELECT 1 FROM vtiger_field WHERE tabid = {$moduleInstance->getId()} AND fieldname = '{$fieldName}'";
        $isExists = $adb->getOne($sql);

        return $isExists;
    }

    // Repair all to register new relationship with the database
    public static function repairRelationships($moduleName = '') {
        include_once('vtlib/Vtiger/Module.php');

        // Fetch all relationship register files
        $pattern = 'modules/*/RelationshipsRegister.php';

        if(!empty($moduleName)) {
            $pattern = 'modules/'. $moduleName .'/RelationshipsRegister.php';
        }

        $db = PearDatabase::getInstance();

        foreach(glob($pattern) as $registerFile) {
            global $relationships;
            include_once($registerFile);

            // Register each relationship with the system
            foreach($relationships as $relationship) {
                $leftSideModule = $relationship['leftSideModule'];
                $rightSideModule = $relationship['rightSideModule'];
                $relationshipType = $relationship['relationshipType'];
                $relationshipName = $relationship['relationshipName'];
                $enabledActions = !empty($relationship['enabledActions']) ? $relationship['enabledActions'] : array('ADD', 'SELECT');
                $listingFunctionName = !empty($relationship['listingFunctionName']) ? $relationship['listingFunctionName'] : NULL;  // Can be null in 1:1 relationship
                $leftSideReferenceFieldName = !empty($relationship['leftSideReferenceFieldName']) ? $relationship['leftSideReferenceFieldName'] : NULL;  // Can be null in N:N or 1:N relationship
                $rightSideReferenceFieldName = !empty($relationship['rightSideReferenceFieldName']) ? $relationship['rightSideReferenceFieldName'] : NULL;  // Can be null in N:N relationship
                $leftSideModuleInstance = Vtiger_Module::getInstance($leftSideModule);
                $rightSideModuleInstance = Vtiger_Module::getInstance($rightSideModule);

                // Check for duplicate relationship
                $isExists = self::isRelationshipExists($leftSideModuleInstance->getId(), $rightSideModuleInstance->getId(), $relationshipType, $listingFunctionName);

                // Skip if this relationship is already exists
                if($isExists) {
                    continue;
                }

                // This is a new relationship, process to create it
                if($leftSideModuleInstance && $rightSideModuleInstance && !empty($relationshipName) && !empty($enabledActions)){
                    // Get reference field info
                    $leftSideReferenceField = getField($leftSideReferenceFieldName, $leftSideModuleInstance);
                    $rightSideReferenceField = getField($rightSideReferenceFieldName, $rightSideModuleInstance);
                    
                    // Create relationship
                    $leftSideModuleInstance->setRelatedList($rightSideModuleInstance, $relationshipName, $enabledActions, $listingFunctionName, $rightSideReferenceField['fieldid']);
                    
                    // Get created relationship id
                    $sql = "SELECT LAST_INSERT_ID();";
                    $createdRelationshipId = $db->getOne($sql);

                    if($createdRelationshipId) {
                        // Update the relationship type after the relationship is created
                        $sql = "UPDATE vtiger_relatedlists SET relationtype = ? WHERE relation_id = ?";
                        $params = array($relationshipType, $createdRelationshipId);
                        $db->pquery($sql, $params);

                        // Create a new config record in table vtiger_fieldmodulerel
                        $sql = "INSERT INTO vtiger_fieldmodulerel(fieldid, module, relmodule)
                            VALUES(?, ?, ?)";
                        $params = array($rightSideReferenceField['fieldid'], $rightSideModule, $leftSideModule);
                        $db->pquery($sql, $params);
                    }
                }
            }
        }
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    public static function fixMissingRelationships() {
        $db = PearDatabase::getInstance();
        $pattern = 'modules/*/RelationshipsRegister.php';

        // Loop through Relationship register files
        foreach (glob($pattern) as $registerFile) {
            global $relationships;
            include($registerFile);
            
            // Loop through module relationships to update vtiger_relatedlists
            foreach ($relationships as $relationship) {
                $leftSideModule = $relationship['leftSideModule'];
                $rightSideModule = $relationship['rightSideModule'];
                $relationshipType = $relationship['relationshipType'];
                $listingFunctionName = !empty($relationship['listingFunctionName']) ? $relationship['listingFunctionName'] : NULL;  // Can be null in 1:1 relationship
                $enabledAction = join(',', $relationship['enabledActions']);
                $rightSideReferenceFieldName = !empty($relationship['rightSideReferenceFieldName']) ? $relationship['rightSideReferenceFieldName'] : NULL;  // Can be null in N:N relationship
                $leftSideModuleInstance = Vtiger_Module::getInstance($leftSideModule);
                $rightSideModuleInstance = Vtiger_Module::getInstance($rightSideModule);
                $isExists = self::isRelationshipExists($leftSideModuleInstance->getId(), $rightSideModuleInstance->getId(), $relationshipType, $listingFunctionName);

                // Process 1:N relationship type
                if ($isExists && $rightSideReferenceFieldName != NULL) {
                    $rightSideReferenceField = getField($rightSideReferenceFieldName, $rightSideModuleInstance);

                    // Check if relation field exist
                    $sql = "SELECT relation_id, relationfieldid FROM vtiger_relatedlists WHERE relationfieldid = ? AND actions = ? AND tabid = ? AND related_tabid = ? AND name = ?";
                    $isRelatedListMatched = $db->getOne($sql, [$rightSideReferenceField['fieldid'], $enabledAction, $leftSideModuleInstance->getId(), $rightSideModuleInstance->getId(), $listingFunctionName]);

                    // Update relation field id
                    if (!$isRelatedListMatched) {
                        $sql = "UPDATE vtiger_relatedlists SET relationfieldid = ?, actions = ? WHERE tabid = ? AND related_tabid = ? AND name = ?";
                        $db->pquery($sql, [$rightSideReferenceField['fieldid'], $enabledAction, $leftSideModuleInstance->getId(), $rightSideModuleInstance->getId(), $listingFunctionName]);
                    }
                }
            }
        }

        // Load all vtiger_relatedlists for relationship 1:N
        $sql = "SELECT DISTINCT vtiger_field.fieldid, parent_tab.name AS parent_module, related_tab.name AS related_module, vtiger_field.uitype
            FROM vtiger_relatedlists
            INNER JOIN vtiger_tab AS parent_tab ON (parent_tab.tabid = vtiger_relatedlists.tabid)
            INNER JOIN vtiger_tab AS related_tab ON (related_tab.tabid = vtiger_relatedlists.related_tabid)
            INNER JOIN vtiger_field ON (vtiger_field.fieldid = vtiger_relatedlists.relationfieldid)
            WHERE relationtype = '1:N'";
        $result = $db->pquery($sql);

        // Check and insert missing relationship data base on table vtiger_relatedlists
        while ($relation = $db->fetchByAssoc($result)) {
            // Get field type id
            $sql = "SELECT fieldtypeid FROM vtiger_ws_fieldtype WHERE uitype = ?";
            $fieldTypeId = $db->getOne($sql, [$relation['uitype']]);

            // Check vtiger_ws_referencetype exist
            $sql = "SELECT 1 FROM vtiger_ws_referencetype WHERE fieldtypeid = ? AND type = ?";
            $isReferenceTypeExist = $db->getOne($sql, [$fieldTypeId, $relation['parent_module']]);

            // Insert new reference type
            if (!$isReferenceTypeExist) {
                $sql = "INSERT INTO vtiger_ws_referencetype (fieldtypeid, type) VALUES (?, ?)";
                $db->pquery($sql, [$fieldTypeId, $relation['parent_module']]);
            }

            // Check if field module rel exist
            $sql = "SELECT 1 FROM vtiger_fieldmodulerel WHERE fieldid = ? AND module = ? AND relmodule = ?";
            $isFieldModuleRelExist = $db->getOne($sql, [$relation['fieldid'], $relation['related_module'], $relation['parent_module']]);

            // Insert field module rel base on updated relation field
            if (!$isFieldModuleRelExist) {
                $sql = "INSERT INTO vtiger_fieldmodulerel (fieldid, module, relmodule) VALUES (?, ?, ?)";
                $db->pquery($sql, [$relation['fieldid'], $relation['related_module'], $relation['parent_module']]);
            }
        }
    }
}