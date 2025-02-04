<?php

/*
*   Class IntegrationApiUtils
*   Author: Hieu Nguyen
*   Date: 2018-09-07
*   Purpose: A parent class for integration api
*/

require_once('include/utils/RestfulApiUtils.php');

class IntegrationApiUtils extends RestfulApiUtils {

    // Implemented by Hieu Nguyen
    static function login(Vtiger_Request $request) {
        $credentials = $request->get('Credentials');
        $username = $credentials['username'];
        $password = $credentials['password'];
        
        if(empty($username) || empty($password)) {
            self::setResponse(400);
        }

		$userEntity = CRMEntity::getInstance('Users');
		$userEntity->column_fields['user_name'] = $username;

		if ($userEntity->doLogin($password)) {
            $sessionId = session_id();
            
            $userId = $userEntity->retrieve_user_id($username);
            self::_setAuthSession($userId);

			// Track the login history
            $userModuleModel = Users_Module_Model::getInstance('Users');
            $userModuleModel->saveLoginHistory($userEntity->column_fields['user_name']);

            $response = array(
                'token' => $sessionId
            );

			self::setResponse(200, $response);
        } 
        else {
			self::setResponse(401);
		}
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    protected static function getRecord($moduleName, $id, $referenceFields = []) {
        // Process
        try {
            self::_checkRecordAccessPermission($moduleName, 'DetailView', $id);
            $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
            $data = $recordModel->getData();

            // Fetch linked record name
            if(!empty($referenceFields)) {
                foreach($referenceFields as $fieldName => $columnName) {
                    $fieldName = is_nan($fieldName) ? $fieldName : $columnName;
                    $referenceId = $data[$columnName];
                    $referenceNameField = str_replace('_id', '', $fieldName) . '_name';

                    $data[$referenceNameField] = self::getReferenceNameFromId($recordModel->getModule(), $fieldName, $referenceId);

                    if($fieldName === "parent_id") { // exception for contact
                        $data["parent_type"] = !empty($referenceId) ? Vtiger_Record_Model::getInstanceById($referenceId)->getModuleName() : "";
                    }
                }
            }

            // Respond
            $response = [
                'success' => 1,
                'data' => decodeUTF8($data)
            ];

            self::setResponse(200, $response);
        }
        // Handle error
        catch(Exception $ex) {
            global $app_strings;

            if($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                self::setResponse(200, ['success' => 0, 'message' => 'RECORD_NOT_FOUND']);
            }

            $response = ['success' => 1, 'message' => 'RETRIEVING_ERROR'];
        }
    }

    // Implemented by Hieu Nguyen on 2018-11-13. This function accepts $processCallback and $saveCallback as annonymous functions
    protected static function saveRecord($moduleName, $data, $processCallback = null, $saveCallback = null) {
        global $current_user, $adb;
        $id = $data['id'];

        // Validate request
        if(empty($data)) {
            self::setResponse(400);
        }

        // Process
        try {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

            if(!empty($id)) {
                self::_checkRecordAccessPermission($moduleName, 'Save', $id);
                $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
            }

            $retrievedId = $recordModel->get('id');

            foreach($data as $fieldName => $value) {
                $recordModel->set($fieldName, $value);

                // Trigeger process callback
                if($processCallback) $processCallback($recordModel, $fieldName, $value);
            }

            if(!empty($retrievedId)) {
                $recordModel->set('mode', 'edit');
            }

            $recordModel->save();

            // Trigeger save callback
            if($saveCallback) $saveCallback($recordModel);

            // Respond
            $response = [
                'success' => 1,
                'id' => $recordModel->get('id')
            ];

            return $response;
        }
        // Handle error
        catch(Exception $ex) {
            global $app_strings;

            if($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                return ['success' => 0, 'message' => 'RECORD_NOT_FOUND'];
            }
            
            return ['success' => 0, 'message' => 'SAVING_ERROR'];
        }
    }
}