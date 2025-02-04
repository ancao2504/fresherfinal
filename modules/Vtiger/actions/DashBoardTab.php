<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_DashBoardTab_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('addTab');
		$this->exposeMethod('deleteTab');
		$this->exposeMethod('renameTab');
		$this->exposeMethod('updateTabSequence');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if ($mode) {
			$this->invokeExposedMethod($mode, $request);
		}
	}

	/**
	 * Function to add Dashboard Tab
	 * @param Vtiger_Request $request
	 */
    // Modified by Hieu Nguyen on 2020-10-12 to save tab name in both English and Vietnamese
	function addTab(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$tabNameEn = trim($request->get('tab_name_en'));    // Modified by Hieu Nguyen on 2020-01-09 to avoid XSS
		$tabNameVn = trim($request->get('tab_name_vn'));

		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$tabExist = $dashBoardModel->checkTabExist($tabNameEn, $tabNameVn);
		$tabLimitExceeded = $dashBoardModel->checkTabsLimitExceeded();
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);

		if ($tabLimitExceeded) {
			$response->setError(100, vtranslate('LBL_DASHBOARD_TABS_LIMIT_EXCEEDED_ERROR_MSG', 'Home'));
		} 
        else if ($tabExist) {
			$response->setError(100, vtranslate('LBL_DASHBOARD_TAB_ALREADY_EXIST_ERROR_MSG', 'Home'));
		} 
        else {
			$tabData = $dashBoardModel->addTab($tabNameEn, $tabNameVn);
			$response->setResult($tabData);
		}

		$response->emit();
	}

	/**
	 * Function to delete Dashboard Tab
	 * @param Vtiger_Request $request
	 */
	function deleteTab(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$tabId = $request->get('tabid');
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$result = $dashBoardModel->deleteTab($tabId);
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		if ($result) {
			$response->setResult($result);
		} else {
			$response->setError(100, 'Failed To Delete Tab');
		}
		$response->emit();
	}

	/**
	 * Funtion to rename Dashboard Tab
	 * @param Vtiger_Request $request
	 */
    // Modified by Hieu Nguyen on 2020-10-12
	function renameTab(Vtiger_Request $request) {
        global $current_user;
		$moduleName = $request->getModule();
        $tabId = $request->get('tab_id');
		$tabNameEn = $request->get('tab_name_en');
		$tabNameVn = $request->get('tab_name_vn');

		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$tabExist = $dashBoardModel->checkTabExist($tabNameEn, $tabNameVn, $tabId);

        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);

        if ($tabExist) {
			$response->setError(100, vtranslate('LBL_DASHBOARD_TAB_ALREADY_EXIST_ERROR_MSG', 'Home'));
		}
        else {
            $success = $dashBoardModel->renameTab($tabId, $tabNameEn, $tabNameVn);
            
            if ($success) {
                $tabName = ($current_user->language == 'vn_vn') ? $tabNameVn : $tabNameEn;
                $response->setResult(['tab_name' => $tabName]);
            } 
            else {
                $response->setError(100, vtranslate('LBL_DASHBOARD_TAB_RENAME_ERROR_MSG', 'Home'));
            }
        }

		$response->emit();
	}

	function updateTabSequence(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$sequence = $request->get("sequence");
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
		$result = $dashBoardModel->updateTabSequence($sequence);
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		if ($result) {
			$response->setResult($result);
		} else {
			$response->setError(100, 'Failed To rearrange Tabs');
		}
		$response->emit();
	}
}
