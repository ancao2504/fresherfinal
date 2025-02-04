<?php

/*
    Action: DashletGuideConfigAjax
    Author: Hieu Nguyen
    Date: 2022-03-10
    Purpose: handle ajax request from Dashlet Guide Config
*/

class Settings_Vtiger_DashletGuideConfigAjax_Action extends Settings_Vtiger_Basic_Action {

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
        $widgetId = $request->get('widget_id');
        $config = Settings_Vtiger_Config_Model::loadConfig('dashlet_guide', true) ?? [];
        $result = [];

        if (!empty($config[$widgetId])) {
            $result = ['guide_content' => $config[$widgetId]];
        }

        $respone = new Vtiger_Response();
        $respone->setResult($result);
        $respone->emit();
    }
    
    function saveConfig(Vtiger_Request $request) {
        $widgetId = $request->get('widget_id');
        $guideContent = $request->getRaw('guide_content');

        // Get existing config
        $config = Settings_Vtiger_Config_Model::loadConfig('dashlet_guide', true) ?? [];

        // Save new config
        $config[$widgetId] = $guideContent;
        Settings_Vtiger_Config_Model::saveConfig('dashlet_guide', $config);

        $respone = new Vtiger_Response();
        $respone->setResult(true);
        $respone->emit();
    }
}