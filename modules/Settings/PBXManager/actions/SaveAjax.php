<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Settings_PBXManager_SaveAjax_Action extends Vtiger_SaveAjax_Action {

    // To save Mapping of user from mapping popup
    public function process(Vtiger_Request $request) {
        $id = $request->get('id');

        // Modified by Hieu Nguyen on 2018-10-03
        /*$qualifiedModuleName = 'PBXManager';
        
        $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        $recordModel->set('gateway',$qualifiedModuleName);
        if($id) {
            $recordModel->set('id',$id);
        }*/
        $gatewayName = $request->get('gateway');

        if(!empty($id)) {
            $recordModel = Settings_PBXManager_Record_Model::getInstanceById($id);
        }
        else {
            $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
        }
        
        // Set gateway
        $recordModel->set('gateway', $gatewayName);

        // Set params
        $connector = Settings_PBXManager_Module_Model::getConnector($gatewayName);

        foreach($connector->getSettingFields() as $field => $type) {
            $recordModel->set($field, $request->get($field));
        }
        
        // Save and respond
        $response = new Vtiger_Response();

        try {
            $recordModel->save();
            $response->setResult(true);
        } 
        catch (Exception $e) {
            $response->setError($e->getMessage());
        }
        // End Hieu Nguyen

        $response->emit();
    }
}
