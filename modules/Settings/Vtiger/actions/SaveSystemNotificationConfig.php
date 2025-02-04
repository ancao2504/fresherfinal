<?php

/*
    File: SaveSystemNotificationConfig.php
    Author: PhuVo
    Date: 2019.03.22
    Purpose: User notification handler
*/

class Settings_Vtiger_SaveSystemNotificationConfig_Action extends Settings_Vtiger_Basic_Action {

    function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    }

    function process(Vtiger_Request $request) {
        $config = $request->get('config');

        Settings_Vtiger_Config_Model::saveConfig('notification_config', $config);

        // Respond
        $responce = new Vtiger_Response();
        $responce->setResult(['success' => true]);
        $responce->emit();
    }
}