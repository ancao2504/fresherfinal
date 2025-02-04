<?php

/*
    File: SaveSyncCustomerInfoConfig.php
    Author: PhuVo
    Date: 2019.03.22
    Purpose: User notification handler
*/

class Settings_Vtiger_SaveSyncCustomerInfoConfig_Action extends Settings_Vtiger_Basic_Action {

    function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    }

    function process(Vtiger_Request $request) {
        $configs = $request->get('configs');

        Settings_Vtiger_Config_Model::saveConfig('sync_customer_info', $configs);

        // Respond
        $responce = new Vtiger_Response();
        $responce->setResult(['success' => true]);
        $responce->emit();
    }
}