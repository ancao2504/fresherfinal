<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class CustomView_Delete_Action extends Vtiger_Action_Controller {

	public function process(Vtiger_Request $request) {
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));
		$moduleModel = $customViewModel->getModule();

        // Modified by Hieu Nguyen on 2020-06-26 to support removing a specific shared filter from shared list
        global $current_user;
        
        if ($request->get('is_shared') == 'true') {
            $customViewModel->removeFromSharedList($current_user->id);
        }
        else if ($customViewModel->getOwnerId() == $current_user->id || $current_user->is_admin == 'on') {
            $customViewModel->delete();
        }
        // End Hieu Nguyen

		$listViewUrl = $moduleModel->getListViewUrl();
		if ($request->isAjax()) {
			$response = new Vtiger_Response();
			$response->setResult(array('success' => true));
			$response->emit();
		} else {
			header("Location: $listViewUrl");
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}
