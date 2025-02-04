<?php

/*
*   Class PortalApiUtils
*   Author: Hieu Nguyen
*   Date: 2020-06-24
*   Purpose: Handle request from customer portal
*/

require_once('include/utils/MobileApiUtils.php');

class PortalApiUtils extends MobileApiUtils {

    static function globalSearch(Vtiger_Request $request) {
        self::setResponse(403); // Don't allow customer portal's users to use this API
    }

    protected static function _login($username, $password, $loginWithOpenId = false) {
        $info = self::_getCustomerInfoByUsername($username);

		if (empty($info)) {
            self::setResponse(401);
        }

        if (!$loginWithOpenId && !Vtiger_Functions::compareEncryptedPassword($password, $info['user_password'], $info['cryptmode'])) {
            self::setResponse(401);
        }

        if ($info['portal_active'] != '1') {
            self::setResponse(200, ['success' => 0, 'message' => 'USER_INACTIVE']);
        }

        if (!empty($info['support_end_date']) && date('Y-m-d') > $info['support_end_date']) {
            self::setResponse(200, ['success' => 0, 'message' => 'OUT_OF_SUPPORT_TIME']);
        }

        self::_auth($info['id']);
    }

    protected static function _getCustomerInfoByUsername($username) {
        global $adb;
        if (empty($username)) return [];

		$sql = "SELECT info.id, info.user_name, info.user_password, info.cryptmode, 
                detail.portal AS portal_active, detail.support_start_date, detail.support_end_date
            FROM vtiger_portalinfo AS info
            INNER JOIN vtiger_customerdetails AS detail ON (detail.customerid = info.id)
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = info.id AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            WHERE info.user_name = ?";

		$result = $adb->pquery($sql, [$username]);
		$info = $adb->fetchByAssoc($result);

        return $info;
    }
 
    protected static function _auth($customerId) {
		if (!empty($customerId)) {
            $sessionId = session_id();
            self::_setAuthSession($sessionId, $customerId);

            $response = [
                'token' => $sessionId,
                'user_info' => self::_getProfile(),
                'metadata' => self::_getMetadata(),
                'update_counters' => self::_getCounters(),
                'free_call_token' => self::_getFreeCallToken()
            ];

			self::setResponse(200, $response);
        } 
        else {
			self::setResponse(401);
		}
    }

    protected static function _setAuthSession($sessionId, $customerId) {
        global $adb;
        $headers = getallheaders();

        // For mobile, session should not be expired so we have to store it into the database
        if ($headers['Client'] == 'Mobile') {
            // Check if this session_id is exists
            $sql = "SELECT COUNT(session_id) FROM portal_oauth_sessions WHERE session_id = ? AND customer_id = ?";
            $isExist = $adb->getOne($sql, [$sessionId, $customerId]);

            if ($isExist) {
                // Update auth_time if this session is already exists
                $sql = "UPDATE portal_oauth_sessions SET auth_time = NOW() WHERE session_id = ?";
                $adb->pquery($sql, [$sessionId]);
            }
            else {
                // Insert this session_id if it is not exists
                $sql = "INSERT INTO portal_oauth_sessions (session_id, customer_id, auth_time) VALUES (?, ?, NOW())";
                $adb->pquery($sql, [$sessionId, $customerId]);
            }

            // Prevent multiple login
            $sql = "DELETE FROM oauth_sessions WHERE customer_id = ? AND session_id != ?";
            $adb->pquery($sql, [$customerId, $sessionId]);
        }

        // Init customer id
        $_SESSION['authenticated_customer_id'] = $customerId;

        // Init portal user
        $portalUser = self::_getPortalUser();
        vglobal('current_user', $portalUser);

        Vtiger_Session::set('AUTHUSERID', $portalUser->id);
        $_SESSION['authenticated_user_id'] = $portalUser->id;
        $_SESSION['app_unique_key'] = vglobal('application_unique_key');
        $_SESSION['authenticated_user_language'] = vglobal('default_language');
    }

    protected static function _getMetadata() {
        $meta = [
            'enum_list' => self::_getEnumList(),
            'dependence_list' => self::_getDependenceList(),
            'package_features' => getPackageFeatures(),
            'validation_config' => vglobal('validationConfig'),
        ];

        return $meta;
    }

    static function checkSession($token) {
        global $adb;
        $headers = getallheaders();

        // For mobile, session will not be expired
        if ($headers['Client'] == 'Mobile') {
            // Check if id on oauth_session
            $sql = "SELECT * FROM portal_oauth_sessions WHERE session_id = ?";
            $result = $adb->pquery($sql, [$token]);
            if ($result) $session = $adb->fetchByAssoc($result);

            if (empty($session)) {
                session_destroy();
                self::setResponse(401);
            }

            // Update time
            $sql = "UPDATE portal_oauth_sessions SET auth_time = NOW() WHERE session_id = ?";
            $adb->pquery($sql, [$token]);

            // Init portal user in every request
            $portalUser = self::_getPortalUser();

        }
        // For web, session will be expired at session timeout
        else {
            session_id($token);

            if (!isset($_SESSION['authenticated_user_id'])) {
                session_destroy();
                self::setResponse(401);
            }

            // Init portal user in every request
            $portalUserId = Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);

            if (empty($portalUserId)) {
                self::setResponse(401);
            }

            $portalUser = CRMEntity::getInstance('Users');
            $portalUser->retrieveCurrentUserInfoFromFile($portalUserId);
        }

        vglobal('current_user', $portalUser);
    }

    protected static function _getPortalUser() {
        global $adb;
        $sql = "SELECT id FROM vtiger_users WHERE user_name = ? AND deleted = 0 AND status = 'Active'";
        $portalUserId = $adb->getOne($sql, ['portal']);

        $portalUser = CRMEntity::getInstance('Users');
        $portalUser->retrieveCurrentUserInfoFromFile($portalUserId);

        return $portalUser;
    }

    protected static function _getProfile() {
        static $customerInfo = [];
        if (!empty($customerInfo)) return $customerInfo;
        $customerId = $_SESSION['authenticated_customer_id'];

        if (!empty($customerId)) {
            $customerRecordModel = Vtiger_Record_Model::getInstanceById($customerId, 'Contacts');
        
            $customerInfo = $customerRecordModel->getData();
            $customerInfo['avatar'] = self::_getUserAvatarFromArray($customerRecordModel);
        }

        return $customerInfo;
    }

    protected static function _getUserAvatarFromArray($customerRecordModel) {
        $avatar = $customerRecordModel->getImageDetails();

        if (!empty($avatar[0]['id'])) {
            return "/{$avatar[0]['path']}_{$avatar[0]['name']}";
        }

        return '';
    }

    protected static function _getCounters() {
        global $adb;
        $customerId = $_SESSION['authenticated_customer_id'];

        // Get tickets count
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            INNER JOIN vtiger_troubletickets ON (ticketid = crmid AND contact_id = ?)
            WHERE deleted = 0 AND setype = 'HelpDesk'";
        $ticketsCount = $adb->getOne($sql, [$customerId]);

        // Get notifications count
        $notificationsCount = self::_getNotificationsCount();
        
        $result = [
            'tickets_count' => $ticketsCount,
            'notifications_count' => $notificationsCount,
        ];

        return $result;
    }
    
    protected static function _changePassword($customerId, $newPassword) {
        global $adb;
        $passwordHash = Vtiger_Functions::generateEncryptedPassword($newPassword);

        try {
            $sql = "UPDATE vtiger_portalinfo SET user_password = ?, cryptmode = ? WHERE id = ?";
            $params = [$passwordHash, 'CRYPT', $customerId];
            $adb->pquery($sql, $params);
            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    protected static function _getNotificationsCount() {
        global $adb;
        $customerId = $_SESSION['authenticated_customer_id'];

        $sql = "SELECT COUNT(id) FROM portal_notifications WHERE receiver_id = ? AND `read` = 0";
        $count = $adb->getOne($sql, [$customerId]);

        return $count;
    }

    protected static function _loadNotifications($offset) {
        global $adb;
        $customerId = $_SESSION['authenticated_customer_id'];
        $maxResults = 20;

        // Convert to safe string
        $offset = intval($adb->sql_escape_string($offset));

        // Get list with paging
        $select = '';
        $fromAndWhere = '';
        $orderBy = "ORDER BY created_time DESC ";

        $select = "SELECT *";
        $fromAndWhere = "FROM portal_notifications 
            WHERE receiver_id = {$customerId} ";

        // Fetch result
        $sqlGetList = "{$select} {$fromAndWhere} {$orderBy} LIMIT {$offset}, {$maxResults}";
        // echo $sqlGetList;
        $result = $adb->pquery($sqlGetList);
        $notifications = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $notification = [
                'message' => $row['message'],
                'data' => [
                    'id' => $row['id'],
                    'image' => file_exists($row['image']) ? $row['image'] : '',
                    'related_record_id' => $row['related_record_id'],
                    'related_record_name' => $row['related_record_name'],
                    'related_module_name' => $row['related_module_name'],
                    'created_time' => $row['created_time'],
                    'read' => $row['read'],
                    'extra_data' => json_decode($row['extra_data'], true),
                ]
            ];

            $notifications[] = $notification;
        }

        // Calculate next offset
        $sqlGetCount = "SELECT COUNT(*) {$fromAndWhere}";
        $count = $adb->getOne($sqlGetCount);
        
        $nextOffset = ($offset + $maxResults < intval($count)) ? $offset + $maxResults : null;

        $result = [
            'data' => $notifications,
            'unread_count' => self::_getNotificationsCount(),
            'next_offset' => $nextOffset
        ];

        return $result;
    }

    protected static function _getFreeCallToken() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $gateway = $serverModel->get('gateway');

        if ($gateway) {
            $connector = $serverModel->getConnector();
            
            if (method_exists($connector, 'getFreeCallToken')) {
                $hotline = PBXManager_Logic_Helper::getDefaultOutboundHotline();
                $hotline = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($hotline);
                $customerProfile = self::_getProfile();
                $customerNumber = $customerProfile['mobile'];
                $token = $connector->getFreeCallToken($customerNumber);
                
                $tokenInfo = [
                    'gateway' => $gateway,
                    'hotline' => $hotline,
                    'token' => $token
                ];

                return $tokenInfo;
            }
        }

        return [];
    }

    protected static function _getRecord($moduleName, $id, $referenceFields = [], $relatedModules = [], $customerLinkField = '') {
        // Process
        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
            $data = $recordModel->getData();

            if (!empty($customerLinkField) && $data[$customerLinkField] != $_SESSION['authenticated_customer_id']) {
                self::setResponse(200, ['success' => 0, 'message' => 'ACCESS_DENIED']);
            }

            // Fetch linked record name
            if (!empty($referenceFields)) {
                foreach ($referenceFields as $fieldName => $columnName) {
                    $fieldName = is_nan($fieldName) ? $fieldName : $columnName;
                    $referenceId = $data[$columnName];
                    $referenceNameField = str_replace('_id', '', $fieldName) . '_name';

                    $data[$referenceNameField] = self::getReferenceNameFromId($recordModel->getModule(), $fieldName, $referenceId);

                    if ($fieldName === 'parent_id') { // Exception for contact
                        $data['parent_type'] = !empty($referenceId) ? Vtiger_Record_Model::getInstanceById($referenceId)->getModuleName() : '';
                    }

                    if ($fieldName === 'related_to') { // Case by case for now
                        $data['related_to_type'] = !empty($referenceId) ? Vtiger_Record_Model::getInstanceById($referenceId)->getModuleName() : '';
                    }
                }
            }

            if (!empty($relatedModules)) {
                $data['counters'] = [];

                foreach ($relatedModules as $relatedModuleName) {
                    $counterLabel = self::_getCounterModuleMap($relatedModuleName);

                    $data['counters'][$counterLabel . '_count'] = self::_getRelatedCount($recordModel, $relatedModuleName);
                    $data[$counterLabel . '_list'] = self::_getRelatedList($recordModel, $relatedModuleName);
                }
            }

            self::_resolveOwnersName($data);

            // Respond
            $response = [
                'success' => 1,
                'data' => decodeUTF8($data),
                'metadata' => [
                    'enum_list' => self::_getEnumList($moduleName),
                    'field_list' => self::_getFieldList($moduleName)
                ]
            ];

            self::setResponse(200, $response);
        }
        // Handle error
        catch (Exception $ex) {
            global $app_strings;

            if ($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                self::setResponse(200, ['success' => 0, 'message' => 'RECORD_NOT_FOUND']);
            }

            self::setResponse(200, ['success' => 0, 'message' => 'RETRIEVING_ERROR']);
        }
    }

    protected static function _saveRecord($moduleName, $data, $processCallback = null, $saveCallback = null, $customerLinkField = '') {
        global $current_user, $adb;
        $id = $data['id'];

        // Validate request
        if (empty($data)) {
            self::setResponse(400);
        }

        // Process
        try {
            $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

            if (!empty($id)) {
                $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);

                if (!empty($customerLinkField) && $recordModel->get($customerLinkField) != $_SESSION['authenticated_customer_id']) {
                    self::setResponse(200, ['success' => 0, 'message' => 'ACCESS_DENIED']);
                }
            }

            $retrievedId = $recordModel->get('id');

            foreach ($data as $fieldName => $value) {
                $recordModel->set($fieldName, $value);

                // Trigeger process callback
                if ($processCallback) $processCallback($recordModel, $fieldName, $value);
            }

            // Added by Phu Vo on 2018.02.28 Delete existed image
            if ($data['imgDelete'] && $data['id']) {
                $_REQUEST['imgDeleted'] = true; // To remove old imagename at crmentyti save logic
                
                $getImageIdsSql = "SELECT attachmentsid AS id FROM vtiger_seattachmentsrel WHERE crmid = ?";
                $result = $adb->pquery($getImageIdsSql, [$data['id']]);

                while ($row = $adb->fetchByAssoc($result)) {
                    $status = $recordModel->deleteImage($row['id']);
                }
            }

            if (!empty($retrievedId)) {
                $recordModel->set('mode', 'edit');
            }

            $recordModel->save();

            // Trigeger save callback
            if ($saveCallback) $saveCallback($recordModel);

            // Respond
            $response = [
                'success' => 1,
                'id' => $recordModel->get('id')
            ];

            return $response;
        }
        // Handle error
        catch (Exception $ex) {
            global $app_strings;

            if ($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                return ['success' => 0, 'message' => 'RECORD_NOT_FOUND'];
            }
            
            return ['success' => 0, 'message' => 'SAVING_ERROR'];
        }
    }
}