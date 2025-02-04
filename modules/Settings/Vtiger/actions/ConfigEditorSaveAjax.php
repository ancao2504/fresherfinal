<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

// Refactored by Hieu Nguyen on 2022-06-13
class Settings_Vtiger_ConfigEditorSaveAjax_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		$response = new Vtiger_Response();
		$qualifiedModuleName = $request->getModule(false);
		$updatedFields = $request->get('updatedFields');
		$configEditorModel = Settings_Vtiger_ConfigEditor_Model::getInstance();

		if ($updatedFields) {
			$configEditorModel->set('updatedFields', $updatedFields);
			$status = $configEditorModel->save();

			if ($status === true) {
				$response->setResult(array($status));
			}
			else {
				$response->setError(vtranslate($status, $qualifiedModuleName));
			}
		}
		else {
			$response->setError(vtranslate('LBL_FIELDS_INFO_IS_EMPTY', $qualifiedModuleName));
		}
		
		$response->emit();
	}
	
	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}