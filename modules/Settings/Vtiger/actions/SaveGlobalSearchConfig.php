<?php

/**
 * File: SaveGlobalSearchConfig.php
 * Author: Phu Vo
 * Date: 2020.07.14
 */

class Settings_Vtiger_SaveGlobalSearchConfig_Action extends Settings_Vtiger_Basic_Action {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

    function process(Vtiger_Request $request) {
        $configs = $request->get('configs');
        Settings_Vtiger_Config_Model::saveConfig('global_search', $configs);

        // Respond
        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }
}