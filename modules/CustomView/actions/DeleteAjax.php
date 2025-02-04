<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class CustomView_DeleteAjax_Action extends Vtiger_Action_Controller {

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		$customViewModel = CustomView_Record_Model::getInstanceById($request->get('record'));

		// Modified by Hieu Nguyen on 2020-06-26 to support removing a specific shared filter from shared list
        global $current_user;
        
        if ($request->get('is_shared') == 'true') {
            $customViewModel->removeFromSharedList($current_user->id);
        }
        else if ($customViewModel->getOwnerId() == $current_user->id || $current_user->is_admin == 'on') {
            $customViewModel->delete();
        }
        // End Hieu Nguyen
	}
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}
