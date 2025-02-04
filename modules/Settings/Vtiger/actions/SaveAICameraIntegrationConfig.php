<?php

/*
    File: SaveAICameraIntegrationConfig.php
    Author: PhuVo
    Date: 2021.04.02
    Purpose: SaveAICameraIntegrationConfig Handler
*/

class Settings_Vtiger_SaveAICameraIntegrationConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('connectVendor');
		$this->exposeMethod('disconnectVendor');
        $this->exposeMethod('removePlace');
	}

    function validateRequest(Vtiger_Request $request) { 
        $request->validateWriteAccess(); 
    }

    function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
    }

    function connectVendor(Vtiger_Request $request) {
        $data = $request->get('config');
        $isNew = $request->get('new');
        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);

        if ($isNew == 1) {
            $config = $data;
        }
        else {
            foreach ($data as $key => $value) {
                $config[$key] = $value;
            }
        }

        Settings_Vtiger_Config_Model::saveConfig('ai_camera_config', $config);

        // Respond
        $responce = new Vtiger_Response();
        $responce->setResult($config);
        $responce->emit();
    }

    function disconnectVendor(Vtiger_Request $request) {
        $config = [];
        Settings_Vtiger_Config_Model::saveConfig('ai_camera_config', $config);

        // Respond
        $responce = new Vtiger_Response($config);
        $responce->setResult(true);
        $responce->emit();
    }

    function removePlace(Vtiger_Request $request) {
        $placeId = $request->get('place_id');
        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);
        unset($config['cameras'][$placeId]);
        Settings_Vtiger_Config_Model::saveConfig('ai_camera_config', $config);

        // Respond
        $responce = new Vtiger_Response($config);
        $responce->setResult(true);
        $responce->emit();
    }
}