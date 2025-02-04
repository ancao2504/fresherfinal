<?php

/*
    Action CheckDuplicateAjax.js
    Author: Phuc Lu
    Date: 2019.11.26
    Purpose: to handle checking for duplicate value
*/

class EmailTemplates_CheckDuplicateAjax_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		// Always allow users to access this action
		$allowAccess = true;

		if(!$allowAccess) {
			throw new AppException(vtranslate($moduleName, $moduleName) .' '. vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Vtiger_Request $request) {
		$templateId = $request->get('record_id');
		$checkField = $request->get('check_field');
		$checkValue = $request->get('check_value');

        $isDuplicated = EmailTemplates_Record_Model::isDuplicated($templateId, $checkField, $checkValue);
        
        $response = new Vtiger_Response();
        $response->setResult($isDuplicated ? 'true' : 'false');
        $response->emit();
    }
}