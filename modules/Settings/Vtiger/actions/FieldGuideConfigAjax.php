<?php

/*
    Action: FieldGuideConfigAjax
    Author: Hieu Nguyen
    Date: 2020-01-20
    Purpose: handle ajax request from Field Guide Config
*/

class Settings_Vtiger_FieldGuideConfigAjax_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
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
    
    function saveConfig(Vtiger_Request $request) {
        $targetModule = $request->get('target_module');
        $helpTexts = $request->get('help_text');

        $targetModuleModel = Vtiger_Module_Model::getInstance($targetModule);
        
        foreach ($helpTexts as $fieldName => $helpText) {
            $fieldModel = $targetModuleModel->getField($fieldName);

            if ($fieldModel) {
                $helpText = trim(strip_tags($helpText));
                $fieldModel->setHelpInfo($helpText);
            }
        }

        $respone = new Vtiger_Response();
        $respone->setResult(true);
        $respone->emit();
    }
}