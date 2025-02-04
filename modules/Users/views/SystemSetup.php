<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Users_SystemSetup_View extends Vtiger_Index_View {
	
	public function preProcess(Vtiger_Request $request, $display=true) {
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$userModel = Users_Record_Model::getCurrentUserModel();
		$isFirstUser = Users_CRMSetup::isFirstUser($userModel);
		
		if($isFirstUser) {
			$viewer->assign('IS_FIRST_USER', $isFirstUser);
			$viewer->assign('PACKAGES_LIST', Users_CRMSetup::getPackagesList());
			$viewer->view('SystemSetup.tpl', $moduleName);
		} else {
            // [DataSecurity] Added by Hieu Nguyen on 2021-02-22 to redirect System Admin user to Admin page right after login
            if ($userModel->get('user_name') == 'sysadmin') {
                header('Location: index.php?module=Vtiger&parent=Settings&view=Index');
                exit();
            }
            // End Hieu Nguyen

			header ('Location: index.php?module=Users&parent=Settings&view=UserSetup');
			exit();
		}
	}
	
	function postProcess(Vtiger_Request $request) {
		return true;
	}
	
}