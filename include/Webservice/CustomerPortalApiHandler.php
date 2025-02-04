<?php

/*
*   Class CustomerPortalApiHandler
*   Author: Hieu Nguyen
*   Date: 2020-06-19
*   Purpose: Handle request from customer portal (web and mobile)
*/

require_once('include/utils/PortalApiUtils.php');

class CustomerPortalApiHandler extends PortalApiUtils {

    static function login(Vtiger_Request $request) {
        // Prevent log into mobile app if this feature is not available in current CRM package
        if (isForbiddenFeature('CustomerPortal')) {
            self::setResponse(401);
        }

        $isOpenId = $request->get('IsOpenId');
        $credentials = $request->get('Credentials');

        // Validate request
        if (empty($credentials)) {
            self::setResponse(401);
        }

        // Using open id
        if ($isOpenId == '1') {
            global $mobileConfig;

            $apiKey = $credentials['api_key'];
            $email = $credentials['email'];
            
            if (empty($apiKey) || empty($email)) {
                self::setResponse(401);
            }

            if ($apiKey != $mobileConfig['api_key']) {
                self::setResponse(401);
            }

            self::_login($email, '', true);
        }
        // Normal login
        else {
            $credentials = $request->get('Credentials');
            $username = $credentials['username'];
            $password = $credentials['password'];
            
            if (empty($username) || empty($password)) {
                self::setResponse(401);
            }

            self::_login($username, $password);
        }
    }

    static function resetPassword(Vtiger_Request $request) {
        require_once('include/Mailer.php');
        $params = $request->get('Params');
        $username = $params['username'];

        // Validate request
        if (empty($username)) {
            self::setResponse(400);
        }

        $info = self::_getCustomerInfoByUsername($username);

        if (empty($info)) {
            self::setResponse(401);
        }

        if ($info['portal_active'] != '1') {
            self::setResponse(200, ['success' => 0, 'message' => 'USER_INACTIVE']);
        }

        if (!empty($info['support_end_date']) && date('Y-m-d') > $info['support_end_date']) {
            self::setResponse(200, ['success' => 0, 'message' => 'OUT_OF_SUPPORT_TIME']);
        }

        // Generate new password
        $newPassword = makeRandomPassword();	
        $result = self::_changePassword($info['id'], $newPassword);

        if (!$result) {
            self::setResponse(200, ['success' => 0]);
        }

        // Send new password via email
        $dateTimeField = new DateTimeField();
        $portalUserModel = Users_Privileges_Model::getCurrentUserModel();
        $customerName = Vtiger_Util_Helper::getRecordName($info['id']);
        $email = $username;
        
        $mainReceivers = [
            ['name' => $customerName, 'email' => $email]
        ];

        $templateId = getSystemEmailTemplateByName('[Portal] Reset Password');

        $variables = [
            'username' => $username,
            'full_name' => $customerName,
            'new_password' => $newPassword,
            'reset_time' => $dateTimeField->getDisplayDateTimeValue($portalUserModel),
            'email' => $email,
        ];

        $result = Mailer::send(true, $mainReceivers, $templateId, $variables);

        // Respond
        if (!$result['success']) {
            self::setResponse(200, ['success' => 0, 'message' => 'EMAIL_SENDING_ERROR']);
        }

        self::setResponse(200, ['success' => 1]);
    }

    static function logout($sessionId) {
        global $adb;

        if (empty($sessionId)) {
            self::setResponse(400);
        }

        // Clear session
        session_destroy();
        $sql = "DELETE FROM portal_oauth_sessions WHERE session_id = ?";
        $adb->pquery($sql, [$sessionId]);

        self::setResponse(200, ['success' => 1]);
    }

    static function getProfile(Vtiger_Request $request) {
        $customerInfo = self::_getProfile();

        // Respond
        $response = [
            'success' => 1,
            'profile_info' => $customerInfo
        ];

        self::setResponse(200, $response);
    }

    static function saveProfile(Vtiger_Request $request) {
        $data = $request->get('Data');

        // Validate request
        if (empty($data)) {
            self::setResponse(400);
        }

        // Process
        try {
            $customerId = $_SESSION['authenticated_customer_id'];
            $customerRecordModel = Vtiger_Record_Model::getInstanceById($customerId, 'Contacts');

            $customerRecordModel->set('firstname', $data['firstname']);
            $customerRecordModel->set('lastname', $data['lastname']);
            $customerRecordModel->set('mobile', $data['mobile']);
            $customerRecordModel->set('email', $data['email']);
            $customerRecordModel->set('mailingstreet', $data['mailingstreet']);
            $customerRecordModel->set('mode', 'edit');
            $customerRecordModel->save();

            // Respond
            self::setResponse(200, [
                'success' => 1, 
                'profile_info' => self::_getProfile()
            ]);
        }
        // Handle error
        catch (Exception $ex) {
            self::setResponse(200, ['success' => 0, 'message' => 'SAVING_ERROR']);
        }
    }

    static function changePassword(Vtiger_Request $request) {
        $params = $request->get('Params');
        $newPassword = $params['new_password'];

        // Validate request
        if (empty($params) || empty($newPassword)) {
            self::setResponse(400);
        }

        // Process
        $customerId = $_SESSION['authenticated_customer_id'];
        $result = self::_changePassword($customerId, $newPassword);

        // Respond
        if (!$result) {
            self::setResponse(200, ['success' => 0]);
        }

        self::setResponse(200, ['success' => 1]);
    }

    static function savePushClientToken(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $token = $params['token'];

        if (empty($params) || empty($token)) {
            self::setResponse(400);
        }

        // Process
        $customerId = $_SESSION['authenticated_customer_id'];
        CustomerPortal_Notification_Helper::saveFcmToken($customerId, $token);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    static function removePushClientToken(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $token = $params['token'];

        if (empty($params) || empty($token)) {
            self::setResponse(400);
        }

        // Process
        $customerId = $_SESSION['authenticated_customer_id'];
        CustomerPortal_Notification_Helper::removeFcmToken($customerId, $token);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    static function getNotificationList(Vtiger_Request $request) {
        $params = $request->get('Params');
        $paging = $params['paging'];

        // Validate request
        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $result = self::_loadNotifications($paging['offset']);

        // Return result
        self::setResponse(200, [
            'success' => 1,
            'entry_list' => $result['data'],
            'unread_count' => $result['unread_count'],
            'paging' => ['next_offset' => $result['next_offset']]
        ]);
    }

    static function markNotificationsAsRead(Vtiger_Request $request) {
        global $adb;

        // Validate request
        $params = $request->get('Params');
        $target = $params['target'];

        if (empty($params) || empty($target)) {
            self::setResponse(400);
        }

        // Process
        $customerId = $_SESSION['authenticated_customer_id'];
        $sql = "UPDATE portal_notifications SET `read` = 1 WHERE receiver_id = ? ";
        $sqlParams = [$customerId];

        if ($target != 'all') {
            $sql .= "AND id = ?";
            $sqlParams[] = $target;
        }
        
        $adb->pquery($sql, $sqlParams);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    static function getCounters(Vtiger_Request $request) {
        $counters = self::_getCounters();
        
        $response = [
            'success' => 1,
            'counters' => $counters
        ];

        // Respond
        self::setResponse(200, $response);
    }

    static function getTicketList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $customerId = $_SESSION['authenticated_customer_id'];
        $select = "SELECT t.ticketid, t.ticket_no, t.title, t.category, t.status, t.solution, createdtime, smcreatorid, smownerid, main_owner_id ";
        $fromAndwhere = "FROM vtiger_troubletickets AS t
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.setype = 'HelpDesk' AND vtiger_crmentity.deleted = 0)
            WHERE t.contact_id = ?";
        $sqlParams = [$customerId];

        // Filtering
        if (!empty($keyword)) {
            $fromAndwhere .= "AND (UPPER(t.ticket_no) LIKE ? OR UPPER(t.title) LIKE ?) ";
            $sqlParams = ["%{$keyword}%", "%{$keyword}%"];
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndwhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(t.ticketid) AS total_count {$fromAndwhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        self::setResponse(200, $response);
    }

    static function getTicket(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'HelpDesk';
        $id = $params['id'];
        $relatedFields = ['parent_id', 'contact_id', 'product_id'];

        self::_getRecord($moduleName, $id, $relatedFields, ['Calendar'], 'contact_id');
    }

    static function saveTicket(Vtiger_Request $request) {
        $moduleName = 'HelpDesk';
        $data = $request->get('Data');
        $data['contact_id'] = $_SESSION['authenticated_customer_id'];   // Important!

        $response = self::_saveRecord($moduleName, $data, null, null, 'contact_id');

        self::setResponse(200, $response);
    }

    // ============== MORE APIS FROM HERE ================= //
}