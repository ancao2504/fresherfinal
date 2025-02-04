<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_UserSetupSave_Action extends Users_Save_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$userModuleModel = Users_Module_Model::getInstance($moduleName);
		$userRecordModel = Users_Record_Model::getCurrentUserModel();
		
		//Handling the user preferences
		$userRecordModel->set('mode','edit');
		$userRecordModel->set('language', $request->get('lang_name'));
		$userRecordModel->set('time_zone', $request->get('time_zone'));
		$userRecordModel->set('date_format', $request->get('date_format'));

        // Added by Hieu Nguyen on 2019-11-07 to save user preferred currency and currency format
		$userRecordModel->set('currency_id', $request->get('currency_id'));
		$userRecordModel->set('currency_symbol_placement', $request->get('currency_symbol_placement'));
        // End Hieu Nguyen

		$userRecordModel->set('tagcloud', 0);
		$userRecordModel->save();
		//End

        // Modified by Hieu nguyen on 2019-11-07 to save system base currency name
		$baseCurrencyName = $request->get('base_currency_name');
		if(!empty($baseCurrencyName)) $userModuleModel->updateBaseCurrency($baseCurrencyName);
		$userModuleModel->insertEntryIntoCRMSetup($userRecordModel->getId());
        // End Hieu Nguyen

		header("Location: index.php");
		//End
	}
}