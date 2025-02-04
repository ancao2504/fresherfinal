<?php

/*
    File: SaveUserNotificationConfig.php
    Author: PhuVo
    Date: 2019.03.22
    Purpose: User notification handler
*/

require_once('modules/CPNotifications/models/Data.php');

class Settings_Vtiger_SaveUserNotificationConfig_Action extends Settings_Vtiger_Basic_Action {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    }

    function process(Vtiger_Request $request) {
        $config = $request->get('config');

        if(empty($config)) {
            return;
        }
        
        CPNotifications_Data_Model::saveUserConfig($config);
        
        // Respond
        $result = array('success' => true);
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}