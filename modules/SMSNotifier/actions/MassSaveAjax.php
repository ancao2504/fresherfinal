<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SMSNotifier_MassSaveAjax_Action extends Vtiger_Mass_Action {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName, $moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	/**
	 * Function that saves SMS records
	 * @param Vtiger_Request $request
	 */
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$recordIds = $this->getRecordsListFromRequest($request);
		$phoneFieldList = $request->get('fields');
		$message = $request->get('message');

		$sendSMSRecordIds = []; // [Core][8FyNg1E4] Added by Phu Vo on 2019.11.22 to fix duplicate relation history

		foreach($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$numberSelected = false;
			foreach($phoneFieldList as $fieldname) {
				$fieldValue = $recordModel->get($fieldname);
				if(!empty($fieldValue)) {
					$toNumbers[$fieldValue] = $recordId;    // Changed array format to ['phone_number' => 'record_id'] by Hieu Nguyen on 2020-03-12 to carry the record id and remove duplicate phone numbers too
					$numberSelected = true;
				}
			}
			if($numberSelected) {
				$sendSMSRecordIds[] = $recordId; // [Core][8FyNg1E4] Modified by Phu Vo on 2019.11.22 to fix duplicate relation history
			}
		}

		$response = new Vtiger_Response();
        
		if(!empty($toNumbers)) {
            $GLOBALS['sms_ott_channel'] = $request->get('channel'); // Added by Hieu Nguyen on 2020-10-28 to support sending multi-channel message

			// Modified by Hieu Nguyen on 2020-11-18 to return result to the client
			$result = SMSNotifier_Record_Model::SendSMS($message, $toNumbers, $currentUserModel->getId(), $sendSMSRecordIds, $moduleName);
			$response->setResult($result);
            // End Hieu Nguyen
		} else {
			$response->setResult(false);
		}
		return $response;
	}
}
