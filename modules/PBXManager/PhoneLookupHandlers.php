<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PhoneLookupHandler extends VTEventHandler {   // Rename class name by Hieu Nguyen on 2020-09-09

    function handleEvent($eventName, $entityData) {
        $moduleName = $entityData->getModuleName();

        $acceptedModule = array('Contacts', 'Accounts', 'Leads', 'CPTarget');   // Added module CPTarget by Hieu Nguyen on 2019-07-25
        if(!in_array($moduleName, $acceptedModule))
            return;
        
        if ($eventName == 'vtiger.entity.aftersave') {
            self::handlePhoneLookUpSaveEvent($entityData, $moduleName); // Modified by Hieu Nguyen on 2020-09-09
        }
 
		if ($eventName == 'vtiger.lead.convertlead' && $moduleName == 'Leads') {
			// PBXManagerHandler::handlePhoneLookupDeleteEvent($entityData); // Comment out by Phu Vo to prevent inbound call could not retrieve lead information
		}

		if($eventName == 'vtiger.entity.afterdelete'){
            self::handlePhoneLookupDeleteEvent($entityData);    // Modified by Hieu Nguyen on 2020-09-09
        }
        
        if($eventName == 'vtiger.entity.afterrestore'){
            $this->handlePhoneLookUpRestoreEvent($entityData, $moduleName);
        }
    }

    static function handlePhoneLookUpSaveEvent($entityData, $moduleName) {
        $recordid = $entityData->getId();
        $data = $entityData->getData();
        
        $values['crmid'] = $recordid;
        $values['setype'] = $moduleName;
        $recordModel = new PBXManager_Record_Model;

        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldsModel = $moduleInstance->getFieldsByType('phone');
        
        foreach ($fieldsModel as $field => $fieldName) {
                $fieldName = $fieldName->get('name');      
                $values[$fieldName] = $data[$fieldName];
                
                if($values[$fieldName])
                    $recordModel->receivePhoneLookUpRecord($fieldName, $values, true);
        }
    }
    
    static function handlePhoneLookupDeleteEvent($entityData){
        $recordid = $entityData->getId();
        $recordModel = new PBXManager_Record_Model;
        $recordModel->deletePhoneLookUpRecord($recordid);
    }
    
    protected function handlePhoneLookUpRestoreEvent($entityData, $moduleName) {
        $recordid = $entityData->getId();

        //To get the record model of the restored record
        $recordmodel = Vtiger_Record_Model::getInstanceById($recordid, $moduleName);

        $values['crmid'] = $recordid;
        $values['setype'] = $moduleName;
        $recordModel = new PBXManager_Record_Model;

        $moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
        $fieldsModel = $moduleInstance->getFieldsByType('phone');
        
        foreach ($fieldsModel as $field => $fieldName) {
            $fieldName = $fieldName->get('name');  
            $values[$fieldName] = $recordmodel->get($fieldName);
            
            if($values[$fieldName])
                 $recordModel->receivePhoneLookUpRecord($fieldName, $values, true);
        }
    }

}

class PhoneLookupBatchHandler extends VTEventHandler {   // Rename class name by Hieu Nguyen on 2020-09-09
    
    function handleEvent($eventName, $entityDatas) {
        foreach ($entityDatas as $entityData) {
            $moduleName = $entityData->getModuleName();

            $acceptedModule = array('Contacts','Accounts','Leads');
            if(!in_array($moduleName, $acceptedModule))
                return;

            if ($eventName == 'vtiger.batchevent.save') {
                PhoneLookupHandler::handlePhoneLookUpSaveEvent($entityData, $moduleName);    // Modified by Hieu Nguyen on 2020-09-09
            }
            
            if ($eventName == 'vtiger.batchevent.delete') {
                PhoneLookupHandler::handlePhoneLookupDeleteEvent($entityData);   // Modified by Hieu Nguyen on 2020-09-09
            }
        }
    }
}

?>