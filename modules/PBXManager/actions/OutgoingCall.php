<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PBXManager_OutgoingCall_Action extends Vtiger_Action_Controller{
    
    public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if (!$permission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

        // Added by Hieu Nguyen on 2022-08-23 to prevent user doing make call when his role is not supported in outbound config
        $hotlines = PBXManager_Logic_Helper::getOutboundHotlines();

		if (empty($hotlines)) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
	}
    
    // Modified by Hieu Nguyen on 2018-10-16
    public function process(Vtiger_Request $request) {
        require_once('include/utils/CallCenterUtils.php');

        $serverModel = PBXManager_Server_Model::getInstance();
        $gateway = $serverModel->get('gateway');
        $response = new Vtiger_Response();
        $user = Users_Record_Model::getCurrentUserModel();
	    $userNumber = $user->phone_crm_extension;
        
        if ($gateway && $userNumber) {
            try {
                $hotlineNumber = $request->get('hotline_number');
                $phoneNumber = PBXManager_Logic_Helper::cleanupPhoneNumber($request->get('phone_number')); // Clean up special characters
                $recordId = $request->get('record_id');
                $callLogId = $request->get('call_log_id');
                $targetRecordId = $request->get('target_record_id');
                $targetModule = $request->get('target_module'); // Added by Vu Mai on 2022-10-05 to save target module to outbound cache 
                $targetView = $request->get('target_view'); // Added by Vu Mai on 2022-11-03 to save target view to outbound cache
                $connector = $serverModel->getConnector();

                // Added by Phu Vo on 2019.07.24 to process out phone call number
                $callCenterConfig = Settings_Vtiger_Config_Model::loadConfig('callcenter_config'); // Modified 2019.08.01 get config from db

                // Some connector require to add number prefix before customer number to make phone call
                $outboundPrefix = $callCenterConfig->outbound_prefix;

                // Process only number exist to void someone call request with empty number
                if (!empty($phoneNumber) && !empty($outboundPrefix)) {
                    if (substr($phoneNumber, 0, 1) !== $outboundPrefix) $phoneNumber = $outboundPrefix . $phoneNumber; // <== Process here
                }
                // End process out phone call number
                
                $result = $connector->makeCall($phoneNumber, $recordId, $hotlineNumber);

                // Handle make call error
                if (empty($result) || !$result['success']) {
                    $error = '';

                    if (is_array($result) && !empty($result['message'])) {
                        $error = $result['message'];
                    }

                    throw new Exception($error);
                } 

                // Store record id in session so that the popup can display the selected customer correctly
                if (!empty($recordId)) {
                    PBXManager_Logic_Helper::saveOutboundCache($userNumber, $recordId, $phoneNumber, $callLogId, $targetRecordId, $targetModule, $targetView); // Modified by Vu Mai on 2022-10-05 to save target module and on 2022-11-03 to save target view to outbound cache
                }

                // Added by Phu Vo on 2020.02.17
                $msg = [
                    'state' => 'PROCESSING',
                    'call_id' => 'PROCESSING',
                    'receiver_id' => $user->getId(),
                    'direction' => 'OUTBOUND',
                ];

                // Modified by Hieu Nguyen on 2021-08-31 to support calling to any phone number which does not require the customer to be existing
                if (!empty($recordId)) {
                    $customerInfo = PBXManager_Data_Model::findCustomerById($recordId);
                }
                else {
                    $customerInfo = [];
                }
                // End Hieu Nguyen

                CallCenterUtils::fillMsgDataForRingingEvent($msg, $phoneNumber, $customerInfo);
                PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
                // End Phu Vo

                $response->setResult($result);
            }
            catch (Exception $e) {
                $errorMessage = $e->getMessage();

                if (!empty($errorMessage)) {
                    $response->setError(500, $errorMessage);
                }
                else {
                    $response->setError(500, vtranslate('LBL_MAKE_CALL_ERROR_MSG', 'PBXManager'));
                }
            }
        }
        else {
            // Modified by Phu Vo on 2019.08.28 to return error message
            if (empty($gateway)) {
                $response->setError(400, vtranslate('LBL_GOING_CALL_GATEWAY_ERROR', 'PBXManager'));
            }
            elseif (empty($userNumber)) {
                $response->setError(400, vtranslate('LBL_GOING_CALL_EXT_ERROR', 'PBXManager'));
            } 
            else {
                $response->setResult(false);
            }
            // End Phu Vo
        }

        $response->emit();
    }
    // End Hieu Nguyen
}