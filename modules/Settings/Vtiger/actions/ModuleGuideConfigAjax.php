<?php

/*
    Action: ModuleGuideConfigAjax
    Author: Hieu Nguyen
    Date: 2020-01-18
    Purpose: handle ajax request from Module Guide Config
*/

class Settings_Vtiger_ModuleGuideConfigAjax_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('getConfig');
		$this->exposeMethod('saveConfig');
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

    function getConfig(Vtiger_Request $request) {
        $targetModule = $request->get('target_module');
        $config = Settings_Vtiger_Config_Model::loadConfig('module_guide', true) ?? [];
        $result = [];

        if (!empty($config[$targetModule])) {
            $result = ['guide_content' => $config[$targetModule]];
        }

        $respone = new Vtiger_Response();
        $respone->setResult($result);
        $respone->emit();
    }
    
    function saveConfig(Vtiger_Request $request) {
        $targetModule = $request->get('target_module');
        $guideContent = $request->getRaw('guide_content');

        // Get existing config
        $config = Settings_Vtiger_Config_Model::loadConfig('module_guide', true) ?? [];

        // Save new config
        $config[$targetModule] = $guideContent;
        Settings_Vtiger_Config_Model::saveConfig('module_guide', $config);

        $respone = new Vtiger_Response();
        $respone->setResult(true);
        $respone->emit();
    }
}