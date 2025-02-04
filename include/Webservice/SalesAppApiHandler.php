<?php

/*
*   Class SalesAppApiHandler
*   Author: Hieu Nguyen
*   Date: 2018-10-22
*   Purpose: Handle request from sales app
*/

require_once('include/utils/MobileApiUtils.php');

class SalesAppApiHandler extends MobileApiUtils {

    // Implemented by Hieu Nguyen on 2018-11-12
    static function resetPassword(Vtiger_Request $request) {
        require_once('include/Mailer.php');
        global $adb; // Modified by Phu Vo on 2020.06.11

        $params = $request->get('Params');
        $username = $params['username'];
        $email = $params['email'];

        // Validate request
        if (empty($username) || empty($email)) {
            self::setResponse(400);
        }
        
        // Process
        $sql = "SELECT id, first_name, last_name, status FROM vtiger_users WHERE deleted = 0 AND user_name = ? AND email1 = ?";
        $sqlParams = [$username, $email];
        $result = $adb->pquery($sql, $sqlParams);
        $userInfo = $adb->fetchByAssoc($result);
        $fullName = getFullNameFromArray('Users', $userInfo);

        if (empty($userInfo)) {
            self::setResponse(200, ['success' => 0, 'message' => 'USER_NOT_FOUND']);
        }

        if ($userInfo['status'] != 'Active') {
            self::setResponse(200, ['success' => 0, 'message' => 'USER_INACTIVE']);
        }

        $newPassword = substr(md5(time()), 0, 10);
        $result = self::_changePassword($userInfo['id'], $newPassword);

        if ($result == false) {
            self::setResponse(200, ['success' => 0]);
        }

        // Send new password via email modified by Phu Vo on 2019.11.04
        $dateTime = new DateTimeField();
        $user = new Users();
        $user->id = $userInfo['id'];
        $user->retrieve_entity_info($userInfo['id'], 'Users');
        
        $mainReceivers = [
            ['name' => $fullName, 'email' => $email]
        ];

        $templateId = getSystemEmailTemplateByName('Mobile Reset Password');

        $variables = [
            'username' => $username,
            'fullname' => $fullName,
            'new_password' => $newPassword,
            'reset_time' => $dateTime->getDisplayDateTimeValue($user),
            'email' => $email,
        ];
        // End Phu Vo

        $result = Mailer::send(true, $mainReceivers, $templateId, $variables);

        // Respond
        if (!$result['success']) {
            self::setResponse(200, ['success' => 0, 'message' => 'EMAIL_SENDING_ERROR']);
        }

        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getProfile(Vtiger_Request $request) {
        global $current_user;

        $userInfo = self::_getProfile();

        // Respond
        $response = [
            'success' => 1,
            'user_info' => $userInfo,
            'home_screen_config' => Users_Preferences_Model::loadPreferences($current_user->id, 'home_screen_config')
        ];

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveProfile(Vtiger_Request $request) {
        $data = $request->get('Data');

        // Validate request
        if (empty($data)) {
            self::setResponse(400);
        }

        // Process
        try {
            $ignoreFields = ['id', 'password', 'user_hash', 'status', 'is_admin', 'home_screen_config'];
            $userId = self::getCurrentUserId();
            $recordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');

            foreach ($data as $fieldName => $value) {
                if (in_array($fieldName, $ignoreFields)) continue;

                $recordModel->set($fieldName, $value);
            }

            // Save profile
            $recordModel->set('mode', 'edit');
            $recordModel->save();

            // Save Home Screen Config
            $homeScreenConfig = $data['home_screen_config'];
            Users_Preferences_Model::savePreferences($userId, 'home_screen_config', $homeScreenConfig);

            // Save avatar
            // if (isset($_FILES['Avatar']) && $_FILES['Avatar']['error'] === 0) {
            //     $entity = $recordModel->getEntity();
            //     $entity->insertIntoAttachment($userId, 'Users');
            // }

            // Update current language
            if (!empty($data['language'])) {
                $currentUserModal = Users_Record_Model::getCurrentUserModel();
                $currentUserModal->set('language', $data['language']);
            }

            // Respond
            self::setResponse(200, [
                'success' => 1, 
                'user_info' => self::_getProfile(), // Added by Phu Vo on 2021.03.20
                'home_screen_config' => Users_Preferences_Model::loadPreferences($userId, 'home_screen_config'), // Added by Phu Vo on 2021.03.20
                'enum_list' => self::_getEnumList(), // Added by Phu Vo on 2021.03.20
                'dependence_list' => self::_getDependenceList(),
            ]);
        }
        // Handle error
        catch (Exception $ex) {
            self::setResponse(200, ['success' => 0, 'message' => 'SAVING_ERROR']);
        }
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function changePassword(Vtiger_Request $request) {
        $params = $request->get('Params');
        $newPassword = $params['new_password'];

        // Validate request
        if (empty($newPassword)) {
            self::setResponse(400);
        }
        
        // Process
        $userId = self::getCurrentUserId();
        $result = self::_changePassword($userId, $newPassword);

        // Respond
        $response = [
            'success' => 1,
        ];

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function saveStar(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = $params['module'];
        $id = $params['id'];
        $starred = $params['starred'];

        // Validate request
        if (empty($moduleName) || empty($id) || !in_array($starred, [0, 1])) {
            self::setResponse(400);
        }

        // Process
        try {
            // Modified by Phu Vo on 2019.01.08 to change save star mechanism
            $moduleUserSpecificTableName = Vtiger_Functions::getUserSpecificTableName($moduleName);

            $focus = CRMEntity::getInstance($moduleName);
			$focus->mode = 'edit';
			$focus->id = $id;
			$focus->column_fields->startTracking();
			$focus->column_fields['starred'] = $starred;
			$focus->insertIntoEntityTable($moduleUserSpecificTableName, $moduleName);

            // Respond
            $response = [
                'success' => 1,
                'id' => $focus->id
            ];
            // End Phu Vo

            self::setResponse(200, $response);
        }
        // Handle error
        catch (Exception $ex) {
            global $app_strings;

            if ($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                self::setResponse(200, ['success' => 0, 'message' => 'RECORD_NOT_FOUND']);
            }

            self::setResponse(200, ['success' => 0, 'message' => 'SAVING_ERROR']);
        }
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function checkinCustomer(Vtiger_Request $request) {
        $params = $request->get('Params');
        $qrCode = $params['qr_code'];

        // Validate request
        if (empty($params) || empty($qrCode)) {
            self::setResponse(400);
        }

        $result = CPEventRegistration_Logic_Helper::verifyQRCode($qrCode);
        $result['success'] = $result['success'] ? 1 : 0;    // Convert from true/false to 1/0

        self::setResponse(200, $result);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function deleteRecord(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = $params['module'];
        $id = $params['id'];

        // Validate request
        if (empty($moduleName) || empty($id)) {
            self::setResponse(400);
        }

        if (!Users_Privileges_Model::isPermitted($moduleName, 'Delete', $id)) {
            self::setResponse(200, ['success' => 0, 'message' => 'ACCESS_DENIED']);
        }

        // Process
        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
            $recordModel->delete();

            // Respond
            self::setResponse(200, ['success' => 1]);
        }
        // Handle error
        catch (Exception $ex) {
            global $app_strings;

            if ($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                self::setResponse(200, ['success' => 0, 'message' => 'RECORD_NOT_FOUND']);
            }

            self::setResponse(200, ['success' => 0, 'message' => 'SAVING_ERROR']);
        }
    }

    static function getQuoteList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Quotes';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT vtiger_quotes.quoteid,
            vtiger_quotes.subject,
            vtiger_quotes.quotestage,
            vtiger_quotes.type,
            vtiger_quotes.taxtype,
            vtiger_quotes.pre_tax_total,
            vtiger_quotes.discount_percent,
            vtiger_quotes.discount_amount,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";

        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_contactdetails.contactid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND UPPER(TRIM(vtiger_quotes.subject)) LIKE ? ";
            $sqlParams = "%{$keyword}%";
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_quotes.quoteid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getLeadList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Leads';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT vtiger_leaddetails.leadid,
            vtiger_leaddetails.lead_no,
            vtiger_leaddetails.firstname,
            vtiger_leaddetails.lastname,
            vtiger_leaddetails.salutation,
            vtiger_leaddetails.company,
            vtiger_leaddetails.leadstatus,
            vtiger_leadaddress.phone,
            vtiger_leadaddress.mobile, 
            vtiger_leaddetails.email,
            vtiger_leadaddress.lane AS address,
            vtiger_leadsubdetails.website,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";
        
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_leadaddress ON (vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid)";
        $extraJoins[] = "LEFT JOIN vtiger_leadsubdetails ON (vtiger_leadsubdetails.leadsubscriptionid = vtiger_leaddetails.leadid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_leaddetails.leadid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $filterSql = "0 = 1 ";
            $filterParams = [];
            $filterSql .= "OR UPPER(TRIM(CONCAT(vtiger_leaddetails.lastname, ' ', vtiger_leaddetails.firstname))) LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_leadaddress.phone LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_leadaddress.mobile LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_leaddetails.email LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_leaddetails.secondaryemail LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_leaddetails.company LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $fromAndWhere .= "AND (" . $filterSql . ") ";
            $sqlParams = array_merge($sqlParams, $filterParams);
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;
        
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row);
            $row['fullname'] = getFullNameFromArray($moduleName, $row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_leaddetails.leadid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['enum_list'] = ['district_list' => self::_getUniqueDistricts('Leads')];
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getLead(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Leads';
        $id = $params['id'];

        $referenceFields = ['account_converted_id', 'contact_converted_id', 'potential_converted_id', 'related_campaign'];

        self::_getRecord($moduleName, $id, $referenceFields, ['Calendar', 'ModComments', 'Documents']);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveLead(Vtiger_Request $request) {
        $moduleName = 'Leads';
        $data = $request->get('Data');

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Nghia Nguyen on 2021-07-27
    static function getDocumentList(Vtiger_Request $request) {
        global $current_user, $adb, $site_URL;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Documents';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT vtiger_notes.notesid,
            vtiger_notes.note_no,
            vtiger_notes.title,
            vtiger_notes.filename,
            vtiger_notes.filetype,
            vtiger_notes.notecontent,
            vtiger_notes.filedownloadcount, 
            vtiger_notes.filestatus,
            vtiger_attachments.attachmentsid,
            vtiger_attachments.description,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";
        
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_seattachmentsrel ON (vtiger_seattachmentsrel.crmid = vtiger_notes.notesid)";
        $extraJoins[] = "LEFT JOIN vtiger_attachments ON (vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_notes.notesid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $filterSql = "0 = 1 ";
            $filterParams = [];
            $filterSql .= "OR vtiger_notes.title LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_notes.filename LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_notes.notecontent LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_notes.filedownloadcount LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_notes.filestatus LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_attachments.description LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $fromAndWhere .= "AND (" . $filterSql . ") ";
            $sqlParams = array_merge($sqlParams, $filterParams);
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;
        
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            // Get attachments info
            $row['file_url'] = $row['filename'];
            $row['file_type'] = 'url';

            if (!empty($row['attachmentsid'])) {
                $row['file_url'] = $site_URL . '/' . 'entrypoint.php?name=DownloadFile&module=Documents&record=' . $row['notesid'];
                $row['file_type'] = 'file';
            }

            self::_resolveOwnersName($row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_notes.notesid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);
        
        self::setResponse(200, $response);
    }

    // Implemented by Nghia Nguyen on 2021-07-27
    static function getDocument(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Documents';
        $id = $params['id'];

        self::_getRecord($moduleName, $id, [], ['Accounts', 'Leads', 'Contacts']);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getAccountList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Accounts';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_account.accountid,
            vtiger_account.account_no,
            vtiger_account.accountname,
            vtiger_account.account_type,
            vtiger_account.phone,
            vtiger_account.otherphone, 
            vtiger_account.email1,
            vtiger_account.website,
            vtiger_accountbillads.bill_street AS address,
            vtiger_accountbillads.bill_city AS city,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";
            
            $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_account.accountid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $filterSql = "0 = 1 ";
            $filterParams = [];
            $filterSql .= "OR UPPER(vtiger_account.accountname) LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_account.phone LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_account.otherphone LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_account.email1 LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_account.email2 LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $fromAndWhere .= "AND (" . $filterSql . ") ";
            $sqlParams = array_merge($sqlParams, $filterParams);
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_account.accountid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['enum_list'] = ['district_list' => self::_getUniqueDistricts('Accounts')];
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getAccount(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Accounts';
        $id = $params['id'];

        self::_getRecord($moduleName, $id, ['account_id'], ['Calendar', 'Potentials', 'Contacts', 'HelpDesk', 'ModComments', 'Documents']);
    }

    // Implemented by Phu Vo on 2019.01.15
    static function getCustomerByCode(Vtiger_Request $request) {
        global $adb;

        // Parse Request
        $params = $request->get('Params');
        $moduleName = 'Contacts';
        $contactNo = $params['contact_no'];

        // Get Account Id vie account_no
        $sql = "SELECT DISTINCT contactid FROM vtiger_contactdetails AS c
            INNER JOIN vtiger_crmentity AS e ON c.contactid = e.crmid AND e.deleted = 0
            WHERE c.contact_no = ?";

        $id = $adb->getOne($sql, [$contactNo]);

        // Call getAccount method
        $request->set('Params', $params);
        self::_getRecord($moduleName, $id);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveAccount(Vtiger_Request $request) {
        $moduleName = 'Accounts';
        $data = $request->get('Data');

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getContactList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Contacts';
        $sqlParams = [];
        if (empty($params['cv_id'])) $params['cv_id'] = 'all';

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT vtiger_contactdetails.contactid,
            vtiger_contactdetails.contact_no,
            vtiger_contactdetails.firstname,
            vtiger_contactdetails.lastname,
            vtiger_contactdetails.salutation,
            vtiger_contactdetails.phone,
            vtiger_contactdetails.mobile,
            vtiger_contactdetails.email,
            vtiger_contactdetails.secondaryemail,
            vtiger_contactaddress.mailingstreet AS address,
            vtiger_account.accountid,
            vtiger_account.accountname,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred ";
            
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_account ON (vtiger_account.accountid = vtiger_contactdetails.accountid)";
        $extraJoins[] = "LEFT JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.accountid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_contactdetails.contactid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $filterSql = "0 = 1 ";
            $filterParams = [];
            $filterSql .= "OR UPPER(TRIM(CONCAT(vtiger_contactdetails.lastname, ' ', vtiger_contactdetails.firstname))) LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_contactdetails.phone LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_contactdetails.mobile LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_contactdetails.email LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_contactdetails.secondaryemail LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $filterSql .= "OR vtiger_account.accountname LIKE ? ";
            $filterParams[] = "%{$keyword}%";
            $fromAndWhere .= "AND (" . $filterSql . ") ";
            $sqlParams = array_merge($sqlParams, $filterParams);
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['fullname'] = getFullNameFromArray($moduleName, $row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_contactdetails.contactid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['enum_list'] = ['district_list' => self::_getUniqueDistricts('Contacts')];
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getContact(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Contacts';
        $id = $params['id'];

        $relatedModules = ['Calendar', 'Potentials', 'HelpDesk', 'ModComments', 'Documents'];

        self::_getRecord($moduleName, $id, ['account_id', 'contact_id'], $relatedModules);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveContact(Vtiger_Request $request) {
        $moduleName = 'Contacts';
        $data = $request->get('Data');

        if ($_FILES['Avatar']) { // Save image for contacct
            self::mapImageFileForSaving($data);
        }

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getOpportunityList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Potentials';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_potential.potentialid,
            vtiger_potential.potential_no,
            vtiger_potential.potentialname,
            vtiger_potential.sales_stage,
            vtiger_potential.amount,
            vtiger_potential.closingdate,
            vtiger_account.accountname,
            vtiger_contactdetails.firstname,
            vtiger_contactdetails.lastname,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";
            
            $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_account ON (vtiger_account.accountid = vtiger_potential.related_to)";
        $extraJoins[] = "LEFT JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_potential.potentialid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_potential.potential_no) LIKE ? OR UPPER(vtiger_potential.potentialname) LIKE ?) ";
            $sqlParams = ["%{$keyword}%", "%{$keyword}%"];;
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['contact_name'] = getFullNameFromArray('Contacts', $row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_potential.potentialid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getOpportunity(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Potentials';
        $id = $params['id'];

        $relatedModules = ['Calendar', 'Contacts', 'ModComments'];

        self::_getRecord($moduleName, $id, ['contact_id', 'related_to'], $relatedModules);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveOpportunity(Vtiger_Request $request) {
        $moduleName = 'Potentials';
        $data = $request->get('Data');

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-22
    static function getTicketList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];
        $filters = $params['filters'];
        $ordering = $params['ordering'];
        $filterBy = $params['filter_by'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Validate filter
        if (!empty($filters) && !is_array($filters)) self::setResponse(400);

        // Process
        $moduleName = 'HelpDesk';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_troubletickets.ticketid,
            vtiger_troubletickets.ticket_no,
            vtiger_troubletickets.title,
            vtiger_troubletickets.category,
            vtiger_troubletickets.priority,
            vtiger_troubletickets.status,
            vtiger_troubletickets.solution,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";

        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_troubletickets.ticketid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);
        
        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_troubletickets.ticket_no) LIKE ? OR UPPER(vtiger_troubletickets.title) LIKE ?) ";
            $sqlParams = ["%{$keyword}%", "%{$keyword}%"];
        }

        // Filter by enum
        // TODO: Make it better next time
        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                if (!empty($value)) $fromAndWhere .= "ANd vtiger_troubletickets.{$filter} = '{$value}' ";
            }
        }

        // Filter by owner
        if (!empty($filterBy) && strtolower($filterBy) == 'mine') {
            $fromAndWhere .= 'AND vtiger_crmentity.main_owner_id = ' . $current_user->id . ' ';
        }

        // Sorting
        if (!empty($ordering)) { // Process sorting support multiple field
            $orderBy = self::_resolveOrderingSql($ordering, $moduleName);
        }
        else { // Process sorting by old way
            $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required
    
            if (!empty($paging['order_by'])) {
                $orderBy = "ORDER BY {$paging['order_by']} ";
            }
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_troubletickets.ticketid) AS total_count {$fromAndWhere}";

        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-22
    static function getOpenTickets(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $filters = $params['filters'];
        $paging = ['offset' => 0, 'max_results' => 5];
        $ordering = $params['ordering'];
        $filterBy = $params['filter_by'];

        if (empty($params)) {
            self::setResponse(400);
        }

        // Validate input data format
        if (!empty($filters) && !is_array($filters)) self::setResponse(400);
        if (!empty($ordering) && !is_array($ordering)) self::setResponse(400);

        // Process
        $moduleName = 'HelpDesk';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_troubletickets.ticketid,
            vtiger_troubletickets.ticket_no,
            vtiger_troubletickets.title,
            vtiger_troubletickets.category,
            vtiger_troubletickets.priority,
            vtiger_troubletickets.status,
            vtiger_troubletickets.solution,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";

        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, 0, $paging);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_troubletickets.ticketid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        $fromAndWhere .= "AND vtiger_troubletickets.status IN ('Open', 'Reopen') ";
        
        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_troubletickets.ticket_no) LIKE ? OR UPPER(vtiger_troubletickets.title) LIKE ?) ";
            $sqlParams = ["%{$keyword}%", "%{$keyword}%"];
        }

        // Filter by owner
        if (!empty($filterBy) && strtolower($filterBy) == 'mine') {
            $fromAndWhere .= 'AND vtiger_crmentity.main_owner_id = ' . $current_user->id . ' ';
        }

        // Sorting
        if (!empty($ordering)) { // Process sorting support multiple field
            $orderBy = self::_resolveOrderingSql($ordering, $moduleName);
        }
        else { // Process sorting by old way
            $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required
        }

        // Paging
        $paginate = "LIMIT 5 ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_troubletickets.ticketid) AS total_count {$fromAndWhere}";

        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getTicket(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'HelpDesk';
        $id = $params['id'];
        $relatedFields = ['parent_id', 'contact_id', 'product_id', 'service_id', 'related_lead', 'related_campaign'];
        $relatedModules = ['Calendar', 'ModComments'];

        self::_getRecord($moduleName, $id, $relatedFields, $relatedModules);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function saveTicket(Vtiger_Request $request) {
        checkAccessForbiddenFeature('CaptureTicketsViaSalesApp');
        $moduleName = 'HelpDesk';
        $data = $request->get('Data');

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    static function getActivityList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];
        $filter = $params['filter'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'Calendar';
        $sqlParams = [];
        if (!empty($filter)) $params['cv_id'] = '0';

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_activity.activityid,
            vtiger_activity.subject,
            vtiger_activity.activitytype,
            vtiger_activity.status AS taskstatus,
            vtiger_activity.eventstatus,
            vtiger_activity.date_start,
            vtiger_activity.time_start,
            vtiger_contactdetails.contactid,
            vtiger_contactdetails.firstname,
            vtiger_contactdetails.lastname,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id "; 
            
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_cntactivityrel ON (vtiger_cntactivityrel.activityid = vtiger_activity.activityid)";
        $extraJoins[] = "LEFT JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_activity.activityid AND vtiger_crmentity_user_field.userid = {$current_user->id})";
        $extraJoins[] = "LEFT JOIN vtiger_invitees ON (vtiger_invitees.activityid = vtiger_activity.activityid AND vtiger_invitees.inviteeid = ? AND vtiger_invitees.invitee_type = 'Users')";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        $sqlParams[] = $current_user->id;

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND UPPER(vtiger_activity.subject) LIKE ? ";
            $sqlParams[] = "%{$keyword}%";
        }

        if ($filter == 'incoming') {
            $fromAndWhere .= "AND CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) >= NOW() ";
            $fromAndWhere .= "AND vtiger_activity.date_start <= ADDDATE(DATE(NOW()), 7) ";
            $fromAndWhere .= "AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred', 'Cancelled')) ";
            $fromAndWhere .= "AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held', 'Not Held', 'Cancelled')) ";
            $fromAndWhere .= "AND (vtiger_crmentity.main_owner_id = ?) ";
            
            $sqlParams[] = $current_user->id;
        }

        // Remove duplicate rows using GROUP BY statement instead of DISTINCT
        $groupBy = "GROUP BY vtiger_activity.activityid ";

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $groupBy . $orderBy . $paginate;
        
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['contact_name'] = getFullNameFromArray('Contacts', $row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_activity.activityid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    static function getCalendarEventDates(Vtiger_Request $request) {
        global $current_user;

        $params = $request->get('Params');
        $selectedMonth = $params['selected_month'] ?? Date('Y-m');
        $eventDates = [];
        $eventDates['my_calendar'] = [];
        $eventDates['shared_calendar'] = [];

        $startDate = Date('Y-m-d', strtotime($selectedMonth));
        $endDate = Date('Y-m-t', strtotime($selectedMonth));
        
        $currentUserFeedInfo = Calendar_SharedCalendar_Model::getCurrentUserFeedInfo();

        // Pull Event dates
        Calendar_Data_Model::pullCalendarEventDates($eventDates['my_calendar'], 'MyCalendar', $current_user->id, 'Events', $startDate, $endDate);
        Calendar_Data_Model::pullCalendarEventDates($eventDates['my_calendar'], 'MyCalendar', $current_user->id, 'Tasks', $startDate, $endDate);

        if ($currentUserFeedInfo['visible'] == 1) {
            Calendar_Data_Model::pullCalendarEventDates($eventDates['shared_calendar'], 'SharedCalendar', $current_user->id, 'Events', $startDate, $endDate);
        }
        
        // Get all shared user id
        $savedUserFeedList = Calendar_SharedCalendar_Model::getSavedUserFeedList();

        foreach ($savedUserFeedList as $userFeed) {
            if ($userFeed['visible'] == 0) continue;
            
            Calendar_Data_Model::pullCalendarEventDates($eventDates['shared_calendar'], 'SharedCalendar', $userFeed['id'], 'Events', $startDate, $endDate);
        }

        $response = [
            'success' => 1,
            'event_dates' => $eventDates, 
        ];

        self::setResponse(200, $response);
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static function getCalendarActivityList(Vtiger_Request $request) {
        global $current_user, $adb;

        $params = $request->get('Params');
        $selectedDate = $params['selected_date'];
        $view = $params['view'] ?? 'MyCalendar';
        $results = [];
        $entryList = [];

        // Assign view to global post to make sure Activity entity create temp table and access query properly
        $_POST['calendar_view'] = $view;

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted('Calendar', 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        // Pull Events and Tasks
        if (!empty($selectedDate)) {
            $currentUserFeedInfo = Calendar_SharedCalendar_Model::getCurrentUserFeedInfo();
            $currentUserTextColor = Settings_Picklist_Module_Model::getTextColor($currentUserFeedInfo['color']);
            if ($view == 'MyCalendar' || ($view == 'SharedCalendar' && $currentUserFeedInfo['visible'] == 1)) {
                $key = $view . 'Events' . $currentUserFeedInfo['id'];
                $results[$key] = [];
                Calendar_Data_Model::pullEvents($selectedDate, $selectedDate, $results[$key], $current_user->id, $currentUserFeedInfo['color'], $currentUserTextColor, $view);
            }

            if ($view == 'SharedCalendar') {
                // Get all shared user id
                $savedUserFeedList = Calendar_SharedCalendar_Model::getSavedUserFeedList();

                foreach ($savedUserFeedList as $userFeed) {
                    if ($userFeed['visible'] == 0) continue;
                    $key = $view . 'Events' . $userFeed['id'];
                    $results[$key] = [];
                    $userTextColor = Settings_Picklist_Module_Model::getTextColor($userFeed['color']);
                    Calendar_Data_Model::pullEvents($selectedDate, $selectedDate, $results[$key], $userFeed['id'], $userFeed['color'], $userTextColor, $view);
                }
            }

            if ($view == 'MyCalendar') {
                $key = $view . 'Taks' . $currentUserFeedInfo['id'];
                $results[$key] = [];
                Calendar_Data_Model::pullTasks($selectedDate, $selectedDate, $results[$key], $currentUserFeedInfo['color'], $currentUserTextColor, $view);
            }
        }

        $selectedDateTime = new DateTime($selectedDate);
        foreach ($results as $key => $entries) {
            foreach ($entries as $entry) {
                $startDateTime = new DateTime(explode(' ', $entry['start'])[0]);
    
                // Filter what mobile really need
                if ($startDateTime == $selectedDateTime || $startDateTime < $selectedDateTime) {
                    self::_resolveOwnersName($entry);
                    $entryList[] = $entry;
                }
            }
        }

        // Sort entry list by created time
        $tempArray = [];
        $entryIds = [];
        foreach ($entryList as $entry) {
            $entryIds[] = $entry['id'];
            $tempArray[] = strtotime($entry['start']);
        }

        array_multisort($tempArray, $entryList);
        // End sort entry list by created time

        // Fetch extra data
        $extraDatas = [];
        $entryIdsString = "('" . join("', '", $entryIds) . "')";

        $sql = "SELECT 
                vtiger_activity.activityid AS recordid, 
                IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred, 
                CASE WHEN vtiger_activity.checkin_time IS NOT NULL AND vtiger_activity.checkin_time <> '' THEN 1 ELSE 0 END AS is_checked_in 
            FROM vtiger_activity
            LEFT JOIN vtiger_crmentity_user_field ON (vtiger_activity.activityid = vtiger_crmentity_user_field.recordid)
            WHERE vtiger_activity.activityid IN {$entryIdsString} AND vtiger_activity.activityid > 0 AND vtiger_crmentity_user_field.userid = ?";
        $result = $adb->pquery($sql, [$current_user->id]);

        // Process extra data
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $extraDatas[$row['recordid']] = $row;
        }

        foreach ($entryList as $index => $entry) {
            $entryList[$index]['starred'] = $extraDatas[$entry['id']]['starred'];
            $entryList[$index]['is_checked_in'] = $extraDatas[$entry['id']]['is_checked_in'];
        }

        $response = [
            'success' => 1,
            'entry_list' => $entryList,
        ];

        self::setResponse(200, $response);
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static protected function _getCalendarSettings() {
        $settings = Calendar_Settings_Model::getUserSettings();

        $settings['shared_calendar_activity_types'] = explode(',', $settings['shared_calendar_activity_types']);

        if (is_array($settings['selected_users'])) {
            $sharedUserIds = [];

            foreach ($settings['selected_users'] as $sharedUserId => $hostId) {
                $sharedUserIds[] = 'Users:' . $sharedUserId;
            }
        }

        $settings['calendar_feeds'] = self::_getCalendarFeeds();

        return $settings;
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static protected function _getCalendarFeeds() {
        $currentUserFeedInfo = Calendar_SharedCalendar_Model::getCurrentUserFeedInfo();
        $savedUserFeedList = Calendar_SharedCalendar_Model::getSavedUserFeedList();
        $calendarFeeds = array_merge([$currentUserFeedInfo], $savedUserFeedList);
        $result = [];

        foreach ($calendarFeeds as $feed) {
            $result[$feed['id']] = $feed;
        }

        return $result;
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static protected function _getCalendarSettingMeta() {
        $userModel = Users_Record_Model::getCurrentUserModel();
		$userRecordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($userModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
        $recordStructure = $userRecordStructure->getStructure();
        $calendarStructure = $recordStructure['LBL_CALENDAR_SETTINGS'];
        $calendarSettingMeta = [];
        $calendarPicklistValues = [];
        $calendarFields = [];

        foreach ($calendarStructure as $fieldName => $fieldModel) {
            $fieldDataType = $fieldModel->getFieldDataType();

            $calendarFields[$fieldName] = [
                'name' => $fieldName,
                'label' => vtranslate($fieldModel->get('label'), 'Users'),
            ];

            if ($fieldDataType == 'picklist') {
                $picklistValues = $fieldModel->getPicklistValues();
                $calendarPicklistValues[$fieldName] = [];

                foreach ($picklistValues as $value => $label) {
                    $calendarPicklistValues[$fieldName][] = [
                        'id' => $value,
                        'text' => $label,
                    ];
                }
            }
        }

        $calendarFields['sharedtype'] = [
            'name' => 'sharedtype',
            'lable' => vtranslate('LBL_CALENDAR_SETTINGS_SHARING_OPTIONS', 'Calendar'),
        ];
        
        $calendarPicklistValues['sharedtype'] = [
            [
                'id' => 'public',
                'text' => vtranslate('Public', 'Calendar'),
            ],
            [
                'id' => 'private',
                'text' => vtranslate('Private', 'Calendar'),
            ],
            [
                'id' => 'selectedusers',
                'text' => vtranslate('LBL_SELECTED_USERS', 'Calendar'),
            ],
        ];

        $calendarSettingMeta = [
            'field_list' => $calendarFields,
            'enum_list' => $calendarPicklistValues,
        ];

        return $calendarSettingMeta;
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static function getCalendarSettings(Vtiger_Request $request) {        
        $response = [
            'success' => 1,
            'calendar_settings' => self::_getCalendarSettings(),
            'meta_data' => self::_getCalendarSettingMeta(),
        ];

        self::setResponse(200, $response);
    }

    /** Implemented by Phu Vo on 2020.08.12 */
    static function saveCalendarSettings(Vtiger_Request $request) {
        global $current_user;

        $data = $request->get('Data');

        // Validate request
        if (empty($data)) {
            self::setResponse(400);
        }

        $sharedType = $data['sharedtype'];
        $data['calendarsharedtype'] = $sharedType;
        $data['shared_calendar_activity_types'] = implode(',', $data['shared_calendar_activity_types']);

        try {
            $userModel = Users_Record_Model::getCurrentUserModel();
            $userModel->set('mode', 'edit');
            $modelData = $userModel->getData();

            foreach ($modelData as $fieldName => $value) {
                if (!isset($data[$fieldName])) continue;
                if ($fieldName == 'is_admin') continue;
                if ($fieldName == 'is_owner') continue;

                $fieldValue = $data[$fieldName];

                if (!is_array($data[$fieldName])) {
                    $fieldValue = trim($fieldValue);
                }
                else {
                    json_encode($fieldValue);
                }

                $userModel->set($fieldName, $fieldValue);
            }

            $userModel->save();

            // Save share calendar setting

            $calendarModuleModel = Calendar_Module_Model::getInstance('Calendar');

            // Delete all previous shared users to cleanup the db
            $calendarModuleModel->deleteSharedUsers($current_user->id);

            // Save selected users from select2
            if ($sharedType == 'selectedusers') {
                $selectedUserIdsString = $data['selected_users'];
                
                if (!empty($selectedUserIdsString)) {
                    $selectedUserIds = Vtiger_CustomOwnerField_Helper::getOwnerIdsFromRequest($selectedUserIdsString);
                    $calendarModuleModel->insertSharedUsers($current_user->id, $selectedUserIds);
                }
            }

            // Save user feed
            $oldFeeds = self::_getCalendarFeeds();
            $calendarFeeds = [];

            // Make sure it have user id as array index
            foreach ($data['calendar_feeds'] as $feedInfo) {
                $calendarFeeds[$feedInfo['id']] = $feedInfo;
            }

            // Handle remove visibility old feed
            foreach ($oldFeeds as $userId => $feedInfo) {
                if (!in_array($userId, array_keys($calendarFeeds)) && $userId != $current_user->id) {
                    Calendar_SharedCalendar_Model::deleteUserFeed($userId);
                }
            }

            foreach ($calendarFeeds as $userId => $feedInfo) {
                // Handle Update
                if (in_array($userId, array_keys($oldFeeds))) {
                    if ($feedInfo['visible'] != $oldFeeds[$userId]['visible']) {
                        Calendar_SharedCalendar_Model::updateUserFeedVisibility($userId, $feedInfo['visible']);
                    }

                    // Update color if need
                    if ($feedInfo['color'] != $oldFeeds[$userId]['color']) {
                        $selectedColor = $feedInfo['color'];
                        Calendar_SharedCalendar_Model::saveUserFeed($userId, $selectedColor);
                    }
                }
                else { // Handle create
                    $accessibleStatus = Calendar_SharedCalendar_Model::getUserFeedAccessibleStatus($userId);
                    if ($accessibleStatus !== true) continue;

                    $selectedColor = $feedInfo['color'];
                    if (empty($selectedColor)) $selectedColor = '#' . dechex(rand(0x000000, 0xFFFFFF));

                    Calendar_SharedCalendar_Model::saveUserFeed($userId, $selectedColor);
                }
            }

            $response = [
                'success' => 1,
                'calendar_settings' => self::_getCalendarSettings(),
            ];

            self::setResponse(200, $response);
        }
        catch (Exception $ex) {
            self::setResponse(200, ['success' => 0, 'message' => 'SAVING_ERROR']);
        }
    }

    // Implemented by Hieu Nguyen on 2018-11-08
    static function getActivity(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Calendar';
        $id = $params['id'];
        $referenceFields = ['parent_id', 'related_lead', 'contact_id', 'related_account'];

        self::_getRecord($moduleName, $id, $referenceFields);
    }

    // Implemented by Hieu Nguyen on 2018-11-13. Modified by Phu Vo on 2020.03.31
    static function saveActivity(Vtiger_Request $request) {
        $moduleName = 'Calendar';
        $data = $request->get('Data');

        // Vtiger will convert time to 24hrs format from $_REQUEST object
        if (!empty($data['time_start'])) $data['time_start'] = Vtiger_Time_UIType::getTimeValueWithSeconds($data['time_start']);
        if (!empty($data['time_end'])) $data['time_end'] = Vtiger_Time_UIType::getTimeValueWithSeconds($data['time_end']);

        // Prepare information to process event invitation
        $processInvitation = false;

        if (isset($data['contact_invitees']) || isset($data['user_invitees'])) {
            $processInvitation = true;
            $contactInvitees = explode(',', $data['contact_invitees']) ?? [];
            $userInvitees = explode(',', $data['user_invitees']) ?? [];
    
            if (!empty($contactInvitees) || !empty($userInvitees)) {
                foreach ($contactInvitees as $index => $contactId) {
                    if (!empty($contactId)) $contactInvitees[$index] = 'Contacts:' . trim($contactId);
                }
    
                foreach ($userInvitees as $index => $userId) {
                    if (!empty($userId)) $userInvitees[$index] = 'Users:' . trim($userId);
                }
            }

            $eventInvitees = array_merge($contactInvitees, $userInvitees);
            $data['user_invitees'] = implode(',', $userInvitees);
            unset($data['contact_invitees']);
            unset($data['user_invitees']);
        }

        // Added by Phu Vo on 2020.1229 to process reminder logic
        if (!empty($data['reminder_time'])) {
            $reminderUiType = new Vtiger_Reminder_UIType();
            $reminderValues = $reminderUiType->getEditViewDisplayValue($data['reminder_time']);

            // Assign necessary info for reminder data
            if (!empty($data['id'])) $_REQUEST['mode'] = 'edit';
            $_REQUEST['remdays'] = $reminderValues[0];
            $_REQUEST['remhrs'] = $reminderValues[1];
            $_REQUEST['remmin'] = $reminderValues[2];
            $_REQUEST['set_reminder'] = 'Yes';
        }
        // End Phu Vo

        $response = self::_saveRecord($moduleName, $data);

        if ($response['success'] == 1 && $processInvitation) {
            Events_Invitation_Helper::saveInvitations($response['id'], $eventInvitees, $data);
        }

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-08
    static function getFaqList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];
        $filters = $params['filters'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Validate filter
        if (!empty($filters) && !is_array($filters)) self::setResponse(400);

        // Process
        $moduleName = 'Faq';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_faq.id,
            vtiger_faq.faq_no,
            vtiger_faq.question,
            vtiger_faq.category,
            vtiger_faq.status,
            vtiger_products.productname,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";

        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_products ON (vtiger_faq.product_id = vtiger_products.productid)";
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_faq.id AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_faq.faq_no) LIKE ? OR UPPER(vtiger_faq.question) LIKE ?) ";
            $sqlParams = ["%{$keyword}%", "%{$keyword}%"];
        }

        // Filter by enum
        // TODO: Make it better next time
        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                if (!empty($value)) $fromAndWhere .= "ANd vtiger_faq.{$filter} = '{$value}' ";
            }
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;
        
        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['contact_name'] = getFullNameFromArray('Contacts', $row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_faq.id) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-08
    static function getFaq(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'Faq';
        $id = $params['id'];
        $referenceFields = ['product_id'];

        self::_getRecord($moduleName, $id, $referenceFields);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getContractList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $selectedDate = $params['selected_date'];
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'ServiceContracts';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_servicecontracts.servicecontractsid,
            vtiger_servicecontracts.contract_no,
            vtiger_servicecontracts.subject,
            vtiger_servicecontracts.contract_type,
            vtiger_servicecontracts.start_date,
            vtiger_servicecontracts.due_date,
            vtiger_servicecontracts.contract_status,
            vtiger_servicecontracts.progress,
            vtiger_servicecontracts.sc_related_to,
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";
            
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_servicecontracts.servicecontractsid AND vtiger_crmentity_user_field.userid = {$current_user->id})";
        
        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_servicecontracts.contract_no) LIKE ? OR UPPER(vtiger_servicecontracts.subject) LIKE ?) ";
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
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['sc_related_to_name'] = getFullNameFromArray('Contacts', $row); // TODO
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_servicecontracts.servicecontractsid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getContract(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = 'ServiceContracts';
        $id = $params['id'];
        $referenceFields = ['sc_related_to'];

        self::_getRecord($moduleName, $id, $referenceFields);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveContract(Vtiger_Request $request) {
        $moduleName = 'ServiceContracts';
        $data = $request->get('Data');

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getSalesOrderList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $selectedDate = $params['selected_date'];
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $moduleName = 'SalesOrder';
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT
            vtiger_salesorder.salesorderid,
            vtiger_salesorder.salesorder_no,
            vtiger_salesorder.subject,
            vtiger_salesorder.sostatus,
            vtiger_salesorder.total,
            vtiger_salesorder.accountid AS 'account_id',
            vtiger_salesorder.contactid AS 'contact_id',
            IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred,
            vtiger_crmentity.createdtime,
            vtiger_crmentity.smcreatorid,
            vtiger_crmentity.smownerid,
            vtiger_crmentity.main_owner_id ";

        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);
        
        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_salesorder.salesorderid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND (UPPER(vtiger_salesorder.salesorder_no) LIKE ? OR UPPER(vtiger_salesorder.subject) LIKE ?) ";
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
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $row['account_name'] = self::getReferenceNameFromId($moduleModel, 'account_id', $row['account_id']);
            $row['contact_name'] = self::getReferenceNameFromId($moduleModel, 'contact_id', $row['contact_id']);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_salesorder.salesorderid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getSalesOrder(Vtiger_Request $request) {
        global $adb;

        $params = $request->get('Params');
        $moduleName = 'SalesOrder';
        $id = $params['id'];
        $referenceFields = ['account_id', 'contact_id', 'quote_id'];

        try {
            self::_checkRecordAccessPermission($moduleName, 'DetailView', $id);
            $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
            $data = $recordModel->getData();

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
                }
            }

            // Get Product and Service
            $data['product_list'] = [];
            $data['service_list'] = [];

            $sql = "SELECT p.*, e.setype AS product_type FROM vtiger_inventoryproductrel AS p 
                INNER JOIN vtiger_crmentity AS e (ON p.productid = e.crmid AND e.deleted = 0)
                WHERE id = ?";

            $result = $adb->pquery($sql, $data['id']);

            while ($row = $adb->fetchByAssoc($result)) {
                $row['id'] = $row['productid'];

                if ($row['product_type'] === 'Products') {
                    $data['product_list'][] = $row;
                }
                if ($row['product_type'] === 'Services') {
                    $data['service_list'][] = $row;
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
        catch (Exception $ex) {
            global $app_strings;

            if ($ex->getMessage() == $app_strings['LBL_RECORD_NOT_FOUND']) {
                self::setResponse(200, ['success' => 0, 'message' => 'RECORD_NOT_FOUND']);
            }

            $response = ['success' => 1, 'message' => 'RETRIEVING_ERROR'];
        }
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function getDataForSalesOrder(Vtiger_Request $request) {
        global $adb;

        // Get products
        $sql = "SELECT p.productid, p.product_no, p.productcode, p.productname, p.unit_price, p.productcategory, p.usageunit
            FROM vtiger_products AS p
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = p.productid AND vtiger_crmentity.setype = 'Products' AND vtiger_crmentity.deleted = 0)
            WHERE 1 = 1";
        $result = $adb->pquery($sql);

        $productList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $productList[] = decodeUTF8($row);
        }

        // Get services
        $sql = "SELECT s.serviceid, s.service_no, s.servicename, s.unit_price, s.servicecategory, s.service_usageunit
            FROM vtiger_service AS s
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = s.serviceid AND vtiger_crmentity.setype = 'Services' AND vtiger_crmentity.deleted = 0)
            WHERE 1 = 1";
        $result = $adb->pquery($sql);

        $serviceList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $serviceList[] = decodeUTF8($row);
        }
        
        // Get Tax info
        $sql = "SELECT taxname, taxlabel, percentage FROM vtiger_inventorytaxinfo WHERE deleted = 0";
        $result = $adb->pquery($sql);

        $taxList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $taxList[] = [
                'key' => $row['taxname'],
                'label' => vtranslate($row['taxlabel']),
                'value' => $row['percentage']
            ];
        }

        $response = [
            'success' => 1,
            'enum_list' => [
                'product_list' => $productList,
                'service_list' => $serviceList,
                'tax_list' => $taxList
            ]
        ];
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    // static function saveSalesOrder(Vtiger_Request $request) {        
    //     // TODO REMAP REQUEST TO VTIGER SAVE ACTION USABLE REQUEST HERE
    //     $saveRequest = self::_mapSaveSalesOrderRequest($request);

    //     // Action Save
    //     $handler = new SalesOrder_Save_Action();
    //     $recordModel = $handler->_saveRecord($saveRequest);

    //     self::setResponse(200, [
    //         'success' => 1,
    //         'id' => $recordModel->get('id')
    //     ]);
    // }

    // Implemented by Hieu Nguyen on 2018-11-13
    // static function saveSalesOrder(Vtiger_Request $request) {        
    //     $moduleName = 'SalesOrder';
    //     $data = $request->get('Data');
        
    //     // Edit or Update?
    //     $data['save_mode'] = empty($data['id']) ? 'new' : 'edit';

    //     $response = self::_saveRecord($moduleName, $data, null, "self::saveSalesOrderProducts");
    //     if ($response['success'] === 1) {
    //         $data = $response['id'];
    //         self::saveSalesOrderProducts($data);
    //     }

    //     self::setResponse(200, $response);
    // }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveSalesOrder(Vtiger_Request $request) {       
        global $adb;
         
        $moduleName = 'SalesOrder';
        $data = $request->get('Data');

        if (
            (!empty($data['product_list']) && !is_array($data['product_list'])) || 
            (!empty($data['service_list']) && !is_array($data['service_list']))
        ) {
            self::setResponse(400);
        }

        // Validate each item
        foreach ($data['product_list'] as $item) {
            if (empty($item['productid']) || empty($item['quantity']) || empty($item['listprice'])) {
                self::setResponse(400);
            }
        }
        
        foreach ($data['service_list'] as $item) {
            if (empty($item['productid']) || empty($item['quantity']) || empty($item['listprice'])) {
                self::setResponse(400);
            }
        }

        // Default values
        $data['taxtype'] = !empty($data['taxtype']) ? $data['taxtype'] : 'group';

        $response = self::_saveRecord($moduleName, $data);

        if ($response['success'] === 1) {
            $orderId = $response['id'];
            // Save something "readonly" in definition
            // TODO: Find a way to save these information using bean
            $sql = "UPDATE vtiger_salesorder 
                SET taxtype = ?, subtotal = ?, discount_percent = ?, discount_amount = ?, pre_tax_total = ?, adjustment = ?, total = ? 
                WHERE salesorderid = ?";

            $params = [$data['taxtype'], $data['subtotal'], $data['discount_percent'], $data['discount_amount'], $data['pre_tax_total'], $data['adjustment'], $data['total'], $orderId];

            $adb->pquery($sql, $params);

            // Set up Some Data to save order details;
            $sql = "SELECT pt.productid, pt.taxpercentage, it.taxname, p.purchase_cost
                FROM vtiger_inventorytaxinfo AS it 
                INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
                INNER JOIN vtiger_products AS p ON (p.productid = pt.productid)
                UNION ALL
                SELECT pt.productid, pt.taxpercentage, it.taxname, s.purchase_cost
                FROM vtiger_inventorytaxinfo AS it 
                INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
                INNER JOIN vtiger_service AS s ON (s.serviceid = pt.productid)";

            $result = $adb->pquery($sql);
            $dataForSO = [];
            
            // Store here to use later
            $dataForSO['product_tax'] = [];
            $dataForSO['purchase_cost'] = [];

            while ($row = $adb->fetchByAssoc($result)) {
                if (!isset($dataForSO['product_tax'][$row['productid']])) $dataForSO['product_tax'][$row['productid']] = [];

                $dataForSO['product_tax'][$row['productid']][$row['taxname']] = $row['taxpercentage'];
                $dataForSO['purchase_cost'][$row['productid']] = $row['purchase_cost'];
            }

            // Delete all product link to this sales order

            $adb->pquery("DELETE FROM vtiger_inventoryproductrel WHERE id = ?", [$orderId]);

            $count = 1;

            foreach ($data['product_list'] as $item) {
                if ($item['quantity'] <= 0) continue;
                
                // Item price
                $itemPrice = $item['listprice'] * $item['quantity'];

                // Calculate discount
                $itemDiscount = 0;

                if (!empty($item['discount_percent'])) {
                    $itemDiscount = ($itemPrice * $item['discount_percent']) / 100;
                }
                elseif (!empty($item['discount_amount'])) {
                    $itemDiscount = $itemPrice - $item['discount_amount'];
                }

                // Calculate purchase cost
                $purchaseCost = $dataForSO['purchase_cost'][$item['productid']] * $item['quantity'];

                $params = array_merge(
                    [
                        'id' => $orderId,
                        'productid' => $item['productid'],
                        'sequence_no' => $count,
                        'section_num' => 1,
                        'section_name' => '',
                        'quantity' => $item['quantity'],
                        'listprice' => $item['listprice'],
                        'discount_percent' => $item['discount_percent'],
                        'discount_amount' => $item['discount_amount'],
                        'comment' => $item['comment'],
                        'description' => $item['description'],
                        'purchase_cost' => $purchaseCost,
                        'margin' => $itemPrice - $purchaseCost
                    ],
                    $dataForSO['product_tax'][$item['productid']] // Merge product tax,
                );

                // Generate inset sql
                $sql = $adb->sql_insert_data('vtiger_inventoryproductrel', $params);
                $adb->query($sql); // Maybe being transaction or something i dunno

                $count++;
            }

            foreach ($data['service_list'] as $item) {
                if ($item['quantity'] <= 0) continue;

                // Item price
                $itemPrice = $item['listprice'] * $item['quantity'];

                // Calculate discount
                $itemDiscount = 0;
                
                if (!empty($item['discount_percent'])) {
                    $itemDiscount = ($itemPrice * $item['discount_percent']) / 100;
                }
                elseif (!empty($item['discount_amount'])) {
                    $itemDiscount = $itemPrice - $item['discount_amount'];
                }

                // Calculate purchase cost
                $purchaseCost = $dataForSO['purchase_cost'][$item['productid']] * $item['quantity'];

                $params = array_merge(
                    [
                        'id' => $orderId,
                        'productid' => $item['productid'],
                        'sequence_no' => $count,
                        'section_num' => 1,
                        'section_name' => '',
                        'quantity' => $item['quantity'],
                        'listprice' => $item['listprice'],
                        'discount_percent' => $item['discount_percent'],
                        'discount_amount' => $item['discount_amount'],
                        'comment' => $item['comment'],
                        'description' => $item['description'],
                        'purchase_cost' => $purchaseCost,
                        'margin' => $itemPrice - $purchaseCost
                    ],
                    $dataForSO['product_tax'][$item['productid']] // Merge product tax,
                );

                // Generate inset sql
                $sql = $adb->sql_insert_data('vtiger_inventoryproductrel', $params);
                $adb->query($sql); // Maybe being transaction or something i dunno

                $count++;
            }

            $adb->query("commit;"); // Commit apply change
        }

        self::setResponse(200, $response);
    }

    static function getProductList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) self::setResponse(400);

        // Process
        $moduleName = 'Products';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT *";
            
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_products.productid AND vtiger_crmentity_user_field.userid = {$current_user->id})";

        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND UPPER(TRIM(vtiger_products.productname)) LIKE ? ";
            $sqlParams[] = "%{$keyword}%";
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by'] }";
        }

        // $paging
        if (strtoupper($paging['max_results']) != 'ALL') {
            $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";
        }

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        $sqlTotalCount = "SELECT COUNT(vtiger_products.productid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Response
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    static function getServiceList(Vtiger_Request $request) {
        global $current_user, $adb;

        // Validate request
        $params = $request->get('Params');
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($paging)) self::setResponse(400);

        // Process
        $moduleName = 'Services';
        $sqlParams = [];

        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted($moduleName, 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $select = "SELECT *";
        
        $fromAndWhere = self::_getfromAndWhereSqlByCvId($moduleName, $params['cv_id'], $paging, $params);

        $extraJoins = [];
        $extraJoins[] = "LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_service.serviceid AND vtiger_crmentity_user_field.userid = {$current_user->id})";
        
        $fromAndWhere = self::_resolveQueryExtraJoin($fromAndWhere, $extraJoins);

        // Filtering
        if (!empty($keyword)) {
            $fromAndWhere .= "AND UPPER(TRIM(vtiger_service.servicename)) LIKE ? ";
            $sqlParams[] = "%{$keyword}%";
        }

        // Sorting
        $orderBy = "ORDER BY vtiger_crmentity.createdtime DESC ";

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY {$paging['order_by'] }";
        }

        // $paging
        if (strtoupper($paging['max_results']) != 'ALL') {
            $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";
        }

        // Main query
        $sql = $select . $fromAndWhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        $sqlTotalCount = "SELECT COUNT(vtiger_service.serviceid) AS total_count {$fromAndWhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Response
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['cv_list'] = self::_getModuleCvIdList($moduleName);

        self::setResponse(200, $response);
    }

    static function getCommentList(Vtiger_Request $request) {
        // Added by Phu Vo on 2021.07.12 to check permission on module and action
        if (isPermitted('ModComments', 'ListView') == 'no') {
            self::setResponse(403, ['success' => 0, 'message' => 'PERMISSION_DENIED']);
        }

        $params = $request->get('Params');
        $relatedModule = $params['module'];
        $relatedRecord = $params['record_related_id'];
        
        $recordModel = Vtiger_Record_Model::getInstanceById($relatedRecord, $relatedModule);
        $entryList = self::_getRelatedList($recordModel, 'ModComments');
        $totalCount = self::_getRelatedCount($recordModel, 'ModComments');

        // Respond
        $response = self::_getResponseWithPaging($entryList, null, $totalCount, $totalCount);
        
        self::setResponse(200, $response);
    }

    static function saveComment(Vtiger_Request $request) {
        global $current_user;
        $moduleName = 'ModComments';
        $data = $request->get('Data');
        $data['userid'] = $current_user->id;    // Commenter ID

        if ($_FILES['Filename']) { // Save image for contacct
            self::mapImageFileForSaving($data, ['Filename' => 'filename']);
        }

        $response = self::_saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }

    // Added by Phu Vo. Modified by Hieu Nguyen on 2022-02-24 for Request #2958
    static function getStatistic(Vtiger_Request $request) {
        global $adb, $current_user;

        $summaryModel = new Home_SalesSummaryWidget_Model();
        $requestData = $request->get('Data');
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($requestData);
        $subDay = $summaryModel->periodToAddUnitMapping($requestData['period']);
        $filterBy = $requestData['filter_by'] ? strtolower($requestData['filter_by']) : '';
        $currentUserId = $current_user->id;

        $data['sales'] = [];
        $data['new_lead'] = [];
        $data['close_won_potential'] = [];
        $data['avg_deal_size'] = [];
        $data['convert_rate'] = [];

        // Sales
        $thisPeriodSql = "SELECT SUM(vtiger_salesorder.total)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_salesorder.order_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_salesorder.order_date) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $thisPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['sales']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_salesorder.total)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_salesorder.order_date) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_salesorder.order_date) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $lastPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['sales']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['sales']['change'] = $summaryModel->getPeriodChange($data['sales']['value'], $data['sales']['last_period']);
        $data['sales']['direction'] = $summaryModel->resolveDirection($data['sales']['value'], $data['sales']['last_period']);

        // New Lead
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $thisPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $thisPeriodNewLeads = $adb->getOne($thisPeriodSql);

        $data['new_lead']['value'] = $thisPeriodNewLeads;

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $lastPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $lastPeriodNewLeads = $adb->getOne($lastPeriodSql);
        $data['new_lead']['last_period'] = $lastPeriodNewLeads;
        $data['new_lead']['change'] = $summaryModel->getPeriodChange($data['new_lead']['value'], $data['new_lead']['last_period']);
        $data['new_lead']['direction'] = $summaryModel->resolveDirection($data['new_lead']['value'], $data['new_lead']['last_period']);

        // Close Won Sales
        $thisPeriodSql = "SELECT COUNT(vtiger_potential.potentialid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_potential.actual_closing_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_potential.actual_closing_date) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Won'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $thisPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['close_won_potential']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_potential.potentialid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_potential.actual_closing_date) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_potential.actual_closing_date) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Won'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $lastPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['close_won_potential']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['close_won_potential']['change'] = $summaryModel->getPeriodChange($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);
        $data['close_won_potential']['direction'] = $summaryModel->resolveDirection($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);

        // Average Deal Size
        $thisPeriodSql = "SELECT SUM(vtiger_potential.amount) / COUNT(vtiger_potential.potentialid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Won'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $thisPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['avg_deal_size']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_potential.amount) / COUNT(vtiger_potential.potentialid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Won'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $lastPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $data['avg_deal_size']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['avg_deal_size']['change'] = $summaryModel->getPeriodChange($data['avg_deal_size']['value'], $data['avg_deal_size']['last_period']);
        $data['avg_deal_size']['direction'] = $summaryModel->resolveDirection($data['avg_deal_size']['value'], $data['avg_deal_size']['last_period']);

        // Conversion Rate
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_leaddetails.leadstatus = 'Converted'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $thisPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $thisPeriodConvertedLeads = $adb->getOne($thisPeriodSql);
        $data['convert_rate']['value'] = $thisPeriodNewLeads > 0 ? ($thisPeriodConvertedLeads / $thisPeriodNewLeads) * 100 : 0;

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_leaddetails.leadstatus = 'Converted'";

        if (!empty($filterBy) && $filterBy == 'mine') {
            $lastPeriodSql .= " AND vtiger_crmentity.main_owner_id = {$currentUserId}";
        }

        $lastPeriodConvertedLeads = $adb->getOne($lastPeriodSql);
        $data['convert_rate']['last_period'] = $lastPeriodNewLeads > 0 ? ($lastPeriodConvertedLeads / $lastPeriodNewLeads) * 100 : 0;
        $data['convert_rate']['change'] = $summaryModel->getPeriodChange($data['convert_rate']['value'], $data['convert_rate']['last_period']);
        $data['convert_rate']['direction'] = $summaryModel->resolveDirection($data['convert_rate']['value'], $data['convert_rate']['last_period']);
        $data['convert_rate']['value'] = round($data['convert_rate']['value'], 2);
        $data['convert_rate']['last_period'] = round($data['convert_rate']['last_period'], 2);
        
        $response = [
            'success' => 1,
            'data' => $data,
        ];

        // Respond
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    static function getCounters(Vtiger_Request $request) {
        global $current_user;
        $counters = self::_getCounters($current_user->id);
        
        $response = [
            'success' => 1,
            'counters' => $counters
        ];

        // Respond
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2020-08-13
    static function getCallCenterInfo(Vtiger_Request $request) {
        $info = self::_getCallCenterInfo();

        $response = [
            'success' => 1,
            'data' => $info
        ];

        self::setResponse(200, $response);
    }

    static function getDataForChart(Vtiger_Request $request) {
        global $adb;

        $userId = self::getCurrentUserId();

        $data = [];

        $moduleMapper = [
            'Accounts' => 'Accounts',
            'Leads' => 'Leads',
            'Contacts' => 'Contacts',
            'Potentials' => 'Opportunities',
            'Calendar' => 'Calendar',
            'SalesOrder' => 'SalesOrder',
            'HelpDesk' => 'Tickets',
            'ServiceContracts' => 'Contracts'
        ];

        // Fetch counter result
        $data['activity_count'] = [];

        foreach ($moduleMapper as $module => $alias) {
            // Counters sql // Modified SQL by Phu Vo on 2019.11.06 to get data using main_owner_id
            $sql = "SELECT IFNULL(COUNT(crmid), 0) FROM vtiger_crmentity 
                WHERE deleted = 0 AND main_owner_id = ? AND setype = ? 
                AND MONTH(createdtime) = MONTH(NOW()) AND DATE(createdtime) <= DATE(NOW())";

            $result = $adb->getOne($sql, [$userId, $module]);

            $data['activity_count'][] = [
                'key' => $module,
                'value' => $result,
            ];
        }

        // Init data for Opp charts
        $nowDb = time(); // TODO => convert it to now db time
        $periods = []; // Loop throw to calculate data

        $periods['month_period']['this_month']['start'] = date('Y-m-01', $nowDb); // Start of this month
        $periods['month_period']['this_month']['end'] = date('Y-m-d', $nowDb); // Today
        $periods['month_period']['same_period_month']['start'] = date("Y-m-01", strtotime("-1 year", $nowDb)); // Start same period month
        $periods['month_period']['same_period_month']['end'] = date("Y-m-t", strtotime("-1 year", $nowDb)); // End same period month

        $periods['quarter_period']['this_quarter']['start'] = self::_getQuaterPeriod($nowDb, 'start'); // Start of this quarter
        $periods['quarter_period']['this_quarter']['end'] = date('Y-m-d', $nowDb); // Today
        $periods['quarter_period']['same_period_quater']['start'] = self::_getQuaterPeriod(strtotime("-1 year", $nowDb), 'start'); // Start of same last year quater
        $periods['quarter_period']['same_period_quater']['end'] = self::_getQuaterPeriod(strtotime("-1 year", $nowDb), 'end');; // End of same last year quater

        $periods['year_period']['this_year']['start'] = date('Y-01-01', $nowDb); //start of this year
        $periods['year_period']['this_year']['end'] = date('Y-m-d', $nowDb); // Today
        $periods['year_period']['one_year_from_now']['start'] = date('Y-01-01', strtotime('-1 year', $nowDb)); // Start of last year
        $periods['year_period']['one_year_from_now']['end'] = date('Y-12-t', strtotime('-1 year', $nowDb)); // End of last year
        $periods['year_period']['two_year_from_now']['start'] = date('Y-01-01', strtotime('-2 years', $nowDb)); // End of last 2 year
        $periods['year_period']['two_year_from_now']['end'] = date('Y-12-t', strtotime('-2 years', $nowDb)); // End of last 2 year

        foreach ($periods as $periodKey => $periodArray) {
            $data[$periodKey] = [];

            foreach ($periodArray as $key => $value) {
                // Counters sql // Modified SQL by Phu Vo on 2019.11.06 to get data using main_owner_id
                $sql = "SELECT SUM(p.amount)
                    FROM vtiger_potential AS p
                    INNER JOIN vtiger_crmentity AS e ON (p.potentialid = e.crmid AND e.deleted = 0)
                    WHERE p.sales_stage = 'Closed Won' AND e.main_owner_id = ? AND DATE(e.modifiedtime) >= ? AND DATE(e.modifiedtime) <= ?";

                $result = $adb->getOne($sql, [$userId, $value['start'], $value['end']]);

                $data[$periodKey][] = array(
                    'key' => $key,
                    'value' => $result ? $result : 0
                );
            }
        }

        $response = [
            'success' => 1,
            'data' => $data
        ];

        self::setResponse(200, $response);
    }

    protected static function _getQuaterPeriod($timeStamp, $key) {
        $quarterMonthStart = array('01', '04', '07', '10');
        $quarterMonthEnd = array('03', '06', '09', '12');

        $whichQuarter = round((date('m', $timeStamp) * 4) / 12); // Base on index

        $return = array(
            'start' => date("Y-{$quarterMonthStart[$whichQuarter]}-01", $timeStamp),
            'end' => date("Y-{$quarterMonthEnd[$whichQuarter]}-t", $timeStamp)
        );

        if (isset($key) && in_array($key, array_keys($return))) {
            return $return[$key];
        }

        return $return;
    }

    static function checkin(Vtiger_Request $request) {
        global $current_user, $adb;
        $data = $request->get('Data');

        if (empty($data['id'])) self::setResponse(400);

        // Check meeting type
        $precheckDataSql = "SELECT activityid, activitytype, eventstatus, checkin_salesman_image, checkin_customer_image FROM vtiger_activity WHERE activityid = ?";
        $precheckData = $adb->fetchByAssoc($adb->pquery($precheckDataSql, [$data['id']]));

        // Validate meeting data
        if (!$precheckData['activityid'] || $precheckData['activitytype'] !== 'Meeting' || $precheckData['eventstatus'] === 'Held') self::setResponse(400);

        // Validate image
        if (empty($_FILES['CustomerPicture']) || empty($_FILES['SalesmanPicture'])) self::setResponse(400);

        $dataMapping = [
            'checkin_longitude' => 'longitude',
            'checkin_latitude' => 'latitude',
            'checkin_address' => 'address',
            'description' => 'note',
        ];

        // Record field mapping goes here in needed
        $data = self::_getDataMappingForSave($data, $dataMapping);

        // Upload or upload support logic goes here
        // [todo] unlink old image
        $data['checkin_salesman_image'] = self::_saveImageToUpload('SalesmanPicture', $precheckData['checkin_salesman_image']);
        $data['checkin_customer_image'] = self::_saveImageToUpload('CustomerPicture', $precheckData['checkin_customer_image']);

        // Assign auto value for checkin goes here
        // $data['eventstatus'] = 'Held'; // Status will update in check-out action

        // Checkin time
        $checkinDateTime = new DateTimeField(); // Create new vtiger time date object
        $data['checkin_time'] = $checkinDateTime->getDBInsertDateTimeValue($current_user); // Get db insert time

        // Request to create new activity with processed data
        $data = ['Data' => $data];
        $activityRequest = new Vtiger_Request($data, $data);

        self::saveActivity($activityRequest, true);
    }

    static function syncContacts(Vtiger_Request $request) {
        // Init input
        $data = $request->get('Data');
        $direction = strtolower($data['direction']);
        $validDirection = ['up', 'down'];
        $inputContacts = $data['local_contacts'];

        // Validate request
        if (empty($direction)) self::setResponse(400);
        if (!in_array($direction, $validDirection)) self::setResponse(400);
        if (empty($inputContacts)) self::setResponse(400);
        if (!is_array($inputContacts)) self::setResponse(400);

        // Process with direction
        if ($direction === 'up') self::_syncUpContacts($request);
        if ($direction === 'down') self::_syncDownContacts($request);

        self::setResponse(400);
    }

    protected static function _syncUpContacts(Vtiger_Request $request) {
        global $adb, $current_user;

        $data = $request->get('Data');
        $inputContacts = $data['local_contacts'];
        $allowDuplicate = $data['allow_duplicate'] == 1 ? true : false;
        $outputResult = [];
        $outputResult['updated_contacts'] = [];
        $outputResult['created_contacts'] = [];
        $outputResult['duplicated_contacts'] = [];
        $outputResult['ignored_contacts'] = [];
        $phoneFields = ['phone', 'mobile', 'homephone', 'otherphone'];
        $emailFields = ['email', 'secondaryemail'];
        $contactFields = array_merge($phoneFields, $emailFields);
        $contactHashTable = [];
        $inputNumbers = [];
        $inputEmails = [];

        // Generate numbers and emails array to use with query
        foreach ($inputContacts as $index => $contactData) {
            // Ignore when contact data not include useful information
            $isValid = false;

            foreach ($contactFields as $fieldName) {
                if (!empty($contactData[$fieldName])) $isValid = true;
            }

            if (!$isValid) {
                $outputResult['ignored_contacts'][] = $contactData;
                unset($inputContacts[$index]);
                continue;
            }

            foreach ($contactFields as $fieldName) {
                $fieldValue = $contactData[$fieldName];
                if (in_array($fieldName, $phoneFields)) {
                    $fieldValue = self::_convertPhoneNumber($fieldValue);
                    $inputNumbers = array_merge($inputNumbers, self::_generatePhoneLookupArray($fieldValue));
                }
                else {
                    $inputEmails[] = $fieldValue;
                }
            }
        }

        // Reorder input contact data to process later with nicely ordered index (after unset)
        $inputContacts = array_values($inputContacts);

        $inputNumbersString = !empty($inputNumbers) ? "('" . implode("','", $inputNumbers) . "')" : "('')";
        $inputEmailsString = !empty($inputEmails) ? "('" . implode("','", $inputEmails) . "')" : "('')";

        // Begin generate sql to get all exist assigned contact on CRM
        $sql = "SELECT e.crmid AS id, pl.fnumber AS value, pl.fieldname AS fieldname
            FROM vtiger_crmentity AS e
            INNER JOIN vtiger_pbxmanager_phonelookup AS pl ON (e.crmid = pl.crmid AND pl.setype = 'Contacts')
            WHERE e.deleted = 0
                AND e.setype = 'Contacts'
                AND e.main_owner_id = ?
                AND pl.fnumber <> ''
                AND pl.fnumber IN {$inputNumbersString}
            UNION ALL
            SELECT e.crmid AS id, el.value AS value, f.fieldname AS fieldname
            FROM vtiger_crmentity AS e
            INNER JOIN vtiger_emailslookup AS el ON (e.crmid = el.crmid AND el.setype = 'Contacts')
            INNER JOIN vtiger_field AS f ON (el.fieldid = f.fieldid)
            WHERE e.deleted = 0
                AND e.setype = 'Contacts'
                AND e.main_owner_id = ?
                AND el.value <> ''
                AND el.value IN {$inputEmailsString}
        ";
        $queryParams = [$current_user->id, $current_user->id];

        $queryResult = $adb->pquery($sql, $queryParams);

        // Query result is the existed Contact on crm
        // And we don't know for sure which row match with which input contact
        // So have to save information to hash table and process input contacts later
        while ($row = $adb->fetchByAssoc($queryResult)) {
            $row = decodeUTF8($row);
            $fieldName = $row['fieldname'];
            $fieldValue = $row['value'];

            if (in_array($fieldName, $phoneFields)) $fieldValue = self::_convertPhoneNumber($fieldValue);
            if (empty($contactHashTable[$fieldValue])) $contactHashTable[$fieldValue] = [];

            if (!in_array($row['id'], $contactHashTable[$fieldValue])) $contactHashTable[$fieldValue][] = $row['id'];
        }

        $updatedCrmContactIds = [];
        $ignoredContactIds = [];

        foreach ($inputContacts as $index => $contactData) {
            // We can check this contact if it exist in hash table, it exist on crm too
            // And if not, it is a new contact so we will create new one
            $isCrmContactFound = false;
            $foundContactIds = [];

            foreach ($contactFields as $fieldName) {
                $fieldValue = $contactData[$fieldName];

                if (in_array($fieldName, $phoneFields)) $fieldValue = self::_convertPhoneNumber($fieldValue);

                $fieldLookupIds = array_filter($contactHashTable[$fieldValue]);

                if (!empty($fieldLookupIds)) {
                    $isCrmContactFound = true;

                    foreach ($fieldLookupIds as $recordId) {
                        // Prevent duplicate input contact cause update a crm contact multiple time
                        // We will check if this record id didn't update along with this contact id
                        // but already updated by another process
                        if (
                            in_array($recordId, $updatedCrmContactIds) // Already updated
                            && !in_array($recordId, $foundContactIds) // And not with this contact data
                        ) {
                            // Only output when it not in result yet
                            if (!in_array($recordId, $foundContactIds)) $foundContactIds[] = $recordId;
                            if (!in_array($recordId, $ignoredContactIds)) $ignoredContactIds[] = $recordId;

                            $processData = array_merge(['duplicated_id' => $recordId], $contactData);
                            $outputResult['duplicated_contacts'][] = $processData;

                            // Allow this contact data create new crm contact if confirmed
                            if ($allowDuplicate) $isCrmContactFound = false;

                            continue;
                        }
                        // End prevent duplicate input contact

                        if (in_array($recordId, $foundContactIds)) continue;

                        if (!in_array($recordId, $foundContactIds)) $foundContactIds[] = $recordId;
                        if (!in_array($recordId, $updatedCrmContactIds)) $updatedCrmContactIds[] = $recordId;

                        $processData = array_merge(['id' => $recordId], $contactData);
                        $saveResult = self::_saveRecord('Contacts', $processData);

                        if ($saveResult['success'] == 1) {
                            $outputResult['updated_contacts'][] = $processData;
                        }
                    }
                }
            }

            // Create new Contact
            if (!$isCrmContactFound) {
                $processData = array_filter($contactData);
                $isContactDataValid = false;

                foreach ($contactFields as $fieldName) {
                    if (!empty($contactData[$fieldName]) && !$isContactDataValid) $isContactDataValid = true;
                }

                if ($isContactDataValid) {
                    $saveResult = self::_saveRecord('Contacts', $processData);
                    
                    if ($saveResult['success'] == 1) {
                        $processData = array_merge(['id' => $saveResult['id']], $contactData);
                        $outputResult['created_contacts'][] = $processData;
                    }
                    else {
                        $outputResult['ignored_contacts'][] = $contactData;
                    }
                }
            }
        }

        self::setResponse(200, ['success' => 1, 'result' => $outputResult]);
    }

    protected static function _syncDownContacts(Vtiger_Request $request) {
        global $adb, $current_user;

        $inputData = $request->get('Data');
        $inputContacts = $inputData['local_contacts'];
        $outputResult = [];
        $outputResult['existed_contacts'] = [];
        $outputResult['updated_contacts'] = [];
        $outputResult['new_contacts'] = [];
        $outputResult['ignored_contacts'] = [];
        $crmContacts = [];
        $phoneFields = ['phone', 'mobile', 'homephone', 'otherphone'];
        $emailFields = ['email', 'secondaryemail'];
        $contactFields = array_merge($phoneFields, $emailFields);
        $contactHashTable = [];

        // We will create a sql to query all current user Contacts
        $sql = "SELECT e.crmid AS id,
                cd.firstname, cd.lastname,
                cd.phone, cd.mobile, cs.homephone, cs.otherphone,
                cd.email, cd.secondaryemail
            FROM vtiger_crmentity AS e
            INNER JOIN vtiger_contactdetails cd ON (e.crmid = cd.contactid)
            LEFT JOIN vtiger_contactsubdetails cs ON (cd.contactid = cs.contactsubscriptionid)
            WHERE e.main_owner_id = ?
                AND e.deleted = 0
                AND (
                    cd.phone <> ''
                    OR cd.mobile <> ''
                    OR cs.homephone <> ''
                    OR cs.otherphone <> ''
                    OR cd.email <> ''
                    OR cd.secondaryemail <> ''
                )
        ";
        $queryParams = [$current_user->id];

        $queryResult = $adb->pquery($sql, $queryParams);

        // Query Result is all current user contact which have useful information
        // When fetching result, generate contact hash table with value is contact ids
        // as the same time to search by number/email later
        while ($row = $adb->fetchByAssoc($queryResult)) {
            $crmContacts[$row['id']] = decodeUTF8($row); // Save crm contact to get data later

            foreach ($contactFields as $fieldName) {
                $fieldValue = $row[$fieldName];

                if (empty($fieldValue)) continue;

                if (in_array($fieldName, $phoneFields)) $fieldValue = self::_convertPhoneNumber($fieldValue);
                if (empty($contactHashTable[$fieldValue])) $contactHashTable[$fieldValue] = [];

                if (!in_array($row['id'], $contactHashTable[$fieldValue])) $contactHashTable[$fieldValue][] = $row['id'];
            }
        }

        foreach ($inputContacts as $contactData) {
            $isCrmContactFound = false;

            foreach ($contactFields as $fieldName) {
                $fieldValue = $contactData[$fieldName];

                if (in_array($fieldName, $phoneFields)) $fieldValue = self::_convertPhoneNumber($fieldValue);

                $fieldLookupIds = array_filter($contactHashTable[$fieldValue]);

                if (!empty($fieldLookupIds)) {
                    $isCrmContactFound = true;

                    foreach ($fieldLookupIds as $recordId) {
                        $crmContact = $crmContacts[$recordId];

                        if (empty($crmContact)) continue;

                        $compareContact = array_merge(['id' => $recordId], $contactData);
                        $contactDiffs = array_diff(array_filter($crmContact), $compareContact);
                        $isContactUpdated = false;

                        foreach (array_keys($contactDiffs) as $fieldName) {
                            if (
                                in_array($fieldName, ['firstname', 'lastname'])
                                || in_array($fieldName, $contactFields)
                            ) {
                                $isContactUpdated = true;
                                break;
                            }
                        }

                        if ($isContactUpdated) {
                            $outputResult['updated_contacts'][] = $crmContacts[$recordId];
                            unset($crmContacts[$recordId]);
                        }
                        else {
                            $outputResult['existed_contacts'][] = $compareContact;
                            unset($crmContacts[$recordId]);
                        }
                    }
                }
            }

            if (!$isCrmContactFound) $outputResult['ignored_contacts'][] = $contactData;
        }

        $outputResult['new_contacts'] = array_values($crmContacts);

        self::setResponse(200, ['success' => 1, 'result' => $outputResult]);
    }

    static function importContacts(Vtiger_Request $request) {
        global $current_user;

        $data = $request->get('Data');
        $inputContacts = $data['local_contacts'];
        $outputResult = [];
        $outputResult['created_contacts'] = [];
        $outputResult['failed_contacts'] = [];
        
        // Validate request
        if (empty($inputContacts)) self::setResponse(400);
        if (!is_array($inputContacts)) self::setResponse(400);

        foreach ($inputContacts as $inputContact) {
            $accountName = !empty($inputContact['account_name']) ? trim($inputContact['account_name']) : null;
            unset($inputContact['account_name']);

            $saveResult = self::_saveRecord('Contacts', $inputContact);

            if ($saveResult['success'] == 1) {
                $inputContact = array_merge(['id' => $saveResult['id']], $inputContact);
                $outputResult['created_contacts'][] = $inputContact;

                // Handle Contact / Account relation
                if (!empty($accountName)) {
                    $accountRecordModel = Vtiger_Record_Model::getInstanceByConditions('Accounts', ['accountname' => $accountName]);

                    // Create new one if not exist
                    if (empty($accountRecordModel)) {
                        $accountRecordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
                        $accountRecordModel->set('accountname', $accountName);
                        $accountRecordModel->set('assigned_user_id', $current_user->id);
                        $accountRecordModel->set('main_owner_id', $current_user->id);
                        $accountRecordModel->save();
                    }

                    // Link Contact with account
                    $contactRecordModel = Vtiger_Record_Model::getInstanceById($saveResult['id']);
                    $contactRecordModel->set('account_id', $accountRecordModel->getId());
                    $contactRecordModel->set('mode', 'edit');
                    $contactRecordModel->save();
                }
            }
            else {
                $outputResult['failed_contacts'][] = $inputContact;
            }
        }

        self::setResponse(200, ['success' => 1, 'result' => $outputResult]);
    }

    static function getDataForCallLog(Vtiger_Request $request) {
        $data = $request->get('Data');
        $customerId = $data['customer_id'];
        $customerType = $data['customer_type'];
        $customerNumber = $data['customer_number'];
        $extNumber = $data['ext_number'];
        $direction = strtoupper($data['direction']);
        $customerData = [];
        $enumList = [];

        // Validate request
        if (empty($customerNumber) || empty($direction)) self::setResponse(400);

        // Prepair meta data
        $metaFields = ['events_call_direction', 'events_call_purpose', 'events_call_result', 'events_inbound_call_purpose'];
        
        foreach ($metaFields as $fieldName) {
            $enumList[$fieldName] = self::_getPickListValues('Events', $fieldName);
        }

        // Meta for call back time [TODO] Handle this somewhere else to share with call popup too
        $enumList['select_moment'] = [
            [
                'value' => 'this_afternoon',
                'color' => '',
                'assign' => 1,
                'key' => 'this_afternoon',
                'label' => vtranslate('LBL_CALL_POPUP_THIS_AFTERNOON', 'PBXManager'),
            ],
            [
                'value' => 'next_morning',
                'color' => '',
                'assign' => 1,
                'key' => 'next_morning',
                'label' => vtranslate('LBL_CALL_POPUP_NEXT_MORNING', 'PBXManager'),
            ],
            [
                'value' => 'next_afternoon',
                'color' => '',
                'assign' => 1,
                'key' => 'next_afternoon',
                'label' => vtranslate('LBL_CALL_POPUP_NEXT_AFTERNOON', 'PBXManager'),
            ],
        ];

        $enumList['select_time'] = [];

        for ($i = 1; $i < 13; $i++) {
            $timeValue = str_pad($i, 2, 0, STR_PAD_LEFT) . ':00';
            $enumList['select_time'][] = [
                'value' => $timeValue,
                'color' => '',
                'assign' => 1,
                'key' => $timeValue,
                'label' => $timeValue,
            ];
        }

        $enumList['time_start'] = self::_getTimePickerOptions();

        // Fetch customer info
        if (empty($customerId)) {
            $customerInfo = PBXManager_Data_Model::findCustomerByPhoneNumber($customerNumber, $direction == 'OUTBOUND', $extNumber, true, true);
            if (!empty($customerInfo)) {
                $customerData = PBXManager_CallPopup_Model::getCustomerInfo($customerInfo['record_id'], $customerInfo['module_name']);
                if (empty($customerType)) $customerType = getSalesEntityType($customerInfo['record_id']);
            }
        }
        else {
            if (empty($customerType)) $customerType = getSalesEntityType($customerId);
            
            if (empty($customerType)) {
                $customerData = [];
            }
            else {
                $customerData = PBXManager_CallPopup_Model::getCustomerInfo($customerId, $customerType);
            }
        }

        $customerData = decodeUTF8($customerData);

        // Retrieve customer avatar
        if (!empty($customerData)) {
            $customerData['customer_avatar'] = PBXManager_Data_Model::getCustomerAvatarFromArray($customerType, $customerData);
        }

        if (empty($customerData)) {
            $customerData = (object) [];
        }

        $response = [
            'success' => 1,
            'customer_data' => $customerData,
            'metadata' => [
                'enum_list' => $enumList,
                'field_list' => self::_getFieldList('Events'),
            ],
        ];
        
        self::setResponse(200, $response);
    }

    static function writeOutboundCache(Vtiger_Request $request) {
        $data = $request->get('Data');
        $userNumber = $data['ext_number'];
        $recordId = $data['customer_id'];
        $number = $data['customer_number'];
        $callLogId = $data['pbx_call_id'];

        // Store record id in session so that the popup can display accurately
        PBXManager_Logic_Helper::saveOutboundCache($userNumber, $recordId, $number, $callLogId);

        $response = [
            'success' => 1,
        ];
        
        self::setResponse(200, $response);
    }

    static function saveCallLog(Vtiger_Request $request) {
        global $current_user;
        
        $data = $request->get('Data');
        $customerData = $data['customer_data'];
        $callBack = $data['call_back'];

        // Process data to use with call popup data utils
        $data['start_time'] = strtotime($data['start_time']) * 1000;
        $data['end_time'] = strtotime($data['end_time']) * 1000;

        if (!empty($customerData)) {
            if (!empty($customerData['product_ids']) && is_array($customerData['product_ids'])) {
                $customerData['product_ids'] = implode(', ', $customerData['product_ids']);
            }
            if (!empty($customerData['service_ids']) && is_array($customerData['service_ids'])) {
                $customerData['service_ids'] = implode(', ', $customerData['service_ids']);
            }
        }

        if (!empty($callBack)) {
            $data['call_back_time_other'] = $callBack['call_back_time_other'] == 1 ? 'on' : 'off';
            $data['select_moment'] = $callBack['select_moment'];
            $data['select_time'] = $callBack['select_time'];

            if ($data['call_back_time_other'] == 'on' && !empty($callBack['date_start']) && !empty($callBack['time_start'])) {
                $startDateTime = new DateTimeField($callBack['date_start'] . ' ' . $callBack['time_start']);
                $data['date_start'] = $startDateTime->getDisplayDate($current_user);
                $data['time_start'] = $startDateTime->getDisplayTime($current_user);
            }
        }

        // Update customer info
        if ($data['events_call_result'] === 'call_result_customer_interested') {
            // Update customer information
            $customer = PBXManager_CallPopup_Model::updateCustomer($customerData);
            $data['customer_id'] = $customer->getId();
            $data['customer_type'] = $customer->getModule()->get('name');
        }

        // Handle Save call log
        $callLog = PBXManager_CallPopup_Model::saveCallLog($data);

        // [START] Handle call result book call back later result
        if ($data['events_call_result'] === 'call_result_call_back_later') {
            PBXManager_CallPopup_Model::saveCallBackRecord($data);
        }
        // [END] Handle call result book call back later result

        // [START] Handle call result customer interested result
        if ($data['events_call_result'] === 'call_result_customer_interested') {
            // Update relationship with product, service
            PBXManager_CallPopup_Model::updateProductServiceCustomerRelation($customerData);
        }
        // [END] Handle call result customer interested result

        // Send customer info to all clients
        $msg = array(
            'state' => 'COMPLETED',
            'call_id' => $data['pbx_call_id'],
            'receiver_id' => $current_user->id,
        );

        PBXManager_Base_Connector::forwardToCallCenterBridge($msg);

        $response = [
            'success' => 1,
            'id' => $callLog->getId(),
        ];

        // Respond
        self::setResponse(200, $response);
    }

    static function acceptInvitation(Vtiger_Request $request) {
        global $current_user;
        
        $data = $request->get('Data');
        $inviteeId = $current_user->id;
        $inviteeType = 'Users';
        $eventId = $data['activity_id'];

        Events_Invitation_Helper::updateInvitationStatus($inviteeId, $inviteeType, $eventId, 'Accepted');

        $response = [
            'success' => 1,
        ];

        // Respond
        self::setResponse(200, $response);
    }

    protected static function _generatePhoneLookupArray($number) {
        $number = self::_convertPhoneNumber($number);
        $number = substr($number, 1);

        if (empty($number)) return [];

        return [
            $number,
            '0' . $number,
            '84' . $number,
            '+84' . $number,
        ];
    }

    protected static function _convertPhoneNumber($number) {
        if (empty($number)) return '';

        $number = (string) $number;

        if (substr($number, 0, 3) === '+84') $number = $number = substr($number, 3);
        if (substr($number, 0, 2) === '84') $number = substr($number, 2);
        if (substr($number, 0, 1) !== '0') $number = '0' . $number;

        return $number;
    }

    protected static function _getDataMappingForSave($data, $mapper = []) {
        foreach ($mapper as $field => $sourceField) {
            if (isset($data[$sourceField])) $data[$field] = $data[$sourceField]; // Added by Phu Vo on 2021.08.20 to iggnore unset fields
            unset($data[$sourceField]);
        }

        return $data;
    }

    protected static function _saveImageToUpload($imageName, $oldPath = '', $newPath = '') {
        // Init
        $file = $_FILES[$imageName];

        // Validate
        if (!$file || $file['error'] || !$file['type']) return '';

        // Info
        $imageInfo = explode('/', $file['type']);
        if (strtolower($imageInfo[0]) !== 'image') return '';

        // $name = $file['name'];

        // Process
        if ($oldPath) unlink($oldPath);
        $newId = md5(time() . $imageName . rand()); // Update by Phu Vo using md5 with time instead of sugar guid

        if (empty($newPath)) {
            $newPath = 'upload/' . $newId . '.' . $imageInfo[1];
        }
        
        $success = move_uploaded_file($file['tmp_name'], $newPath);

        if (!$success) return '';
        
        return $newPath;
    }
}