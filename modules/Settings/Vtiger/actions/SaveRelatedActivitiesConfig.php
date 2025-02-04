<?php

/*
    File: SaveRelatedActivitiesConfig.php
    Author: PhuVo
    Date: 2019.03.22
    Purpose: User notification handler
*/

class Settings_Vtiger_SaveRelatedActivitiesConfig_Action extends Settings_Vtiger_Basic_Action {

    function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    }

    function process(Vtiger_Request $request) {
        $configs = $request->get('configs');

        // Default configs is array
        if (!is_array($configs)) $configs = [];

        // Handle value for checkbox input
        $configs['main_owner_full_access'] = $configs['main_owner_full_access'] == 'on' ? 1 : 0;

        Settings_Vtiger_Config_Model::saveConfig('related_activities_config', $configs);

        // Respond
        $responce = new Vtiger_Response();
        $responce->setResult(['success' => true]);
        $responce->emit();
    }
}