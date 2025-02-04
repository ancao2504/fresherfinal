<?php

/*
    Action CheckDuplicateAjax.js
    Author: Hieu Nguyen
    Date: 2019-03-13
    Purpose: to handle checking for duplicate value
*/

class Users_CheckDuplicateAjax_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		// Always allow users to access this action
		$allowAccess = true;

		if(!$allowAccess) {
			throw new AppException(vtranslate($moduleName, $moduleName) .' '. vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	function process(Vtiger_Request $request) {
		$userId = $request->get('record_id');
		$checkField = $request->get('check_field');
		$checkValue = $request->get('check_value');
        $allowedFields = ['phone_crm_extension'];   // Add any field to allow checking. Be careful!

        if(!in_array($checkField, $allowedFields)) {
            return;
        }

		$isDuplicated = Users_Record_Model::isDuplicated($userId, $checkField, $checkValue);
		
        echo !$isDuplicated ? 'true' : 'false';
    }
}