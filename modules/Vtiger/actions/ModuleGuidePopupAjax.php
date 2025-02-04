<?php

/*
    Action: ModuleGuidePopupAjax
    Author: Hieu Nguyen
    Date: 2020-01-18
    Purpose: handle ajax request from Module Guide Popup
*/

class Vtiger_ModuleGuidePopupAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('savePreferences');
	}

    function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
    }
    
    function savePreferences(Vtiger_Request $request) {
        global $current_user;
        $targetModule = $request->get('target_module');
        $showNextTime = $request->get('show_next_time');

        // Get existing config
        $preferences = Users_Preferences_Model::loadPreferences($current_user->id, 'module_guide', true) ?? [];

        // Save new config
        $preferences[$targetModule] = $showNextTime;
        Users_Preferences_Model::savePreferences($current_user->id, 'module_guide', $preferences);

        $respone = new Vtiger_Response();
        $respone->setResult(true);
        $respone->emit();
    }
}
