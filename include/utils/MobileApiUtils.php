<?php

/*
*   Class MobileApiUtils
*   Author: Hieu Nguyen
*   Date: 2018-10-22
*   Purpose: A parent class for mobile api
*/

require_once('include/utils/RestfulApiUtils.php');

class MobileApiUtils extends RestfulApiUtils {

    // Implemented by Hieu Nguyen on 2018-11-13
    static function login(Vtiger_Request $request) {
        checkAccessForbiddenFeature('SalesApp');
        $isOpenId = $request->get('IsOpenId');
        $credentials = $request->get('Credentials');

        // Validate request
        if (empty($credentials)) {
            self::setResponse(401);
        }

        // Using open id
        if ($isOpenId == '1') {
            global $adb, $mobileConfig;

            $apiKey = $credentials['api_key'];
            $email = $credentials['email'];
            
            if (empty($apiKey) || empty($email)) {
                self::setResponse(401);
            }

            if ($apiKey != $mobileConfig['api_key']) {
                self::setResponse(401);
            }

            $sql = "SELECT id FROM vtiger_users WHERE email1 = ?";
            $sqlParams = [$email];
            $userId = $adb->getOne($sql, $sqlParams);

            if ($userId) {
                $userRecordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');

                self::auth($userRecordModel->getEntity());
            }
            else {
                self::auth(null);
            }
        }
        // Normal login
        else {
            $credentials = $request->get('Credentials');
            $username = $credentials['username'];
            $password = $credentials['password'];
            
            if (empty($username) || empty($password)) {
                self::setResponse(401);
            }

            $userEntity = CRMEntity::getInstance('Users');
            $userEntity->column_fields['user_name'] = $username;

            if ($userEntity->doLogin($password)) {
                self::auth($userEntity);
            }
            else {
                self::auth(null);
            }
        }
    }

    // Implemented by Hieu Nguyen on 2018-10-22
    protected static function auth($userEntity) {
        // Prevent log into mobile app if this feature is not available in current CRM package
        if (isForbiddenFeature('MobileApp')) {
            self::setResponse(401);
        }

		if (!empty($userEntity)) {
            $sessionId = session_id();
            $username = $userEntity->column_fields['user_name'];
            $userId = $userEntity->retrieve_user_id($username);
            
            self::_setAuthSession($sessionId, $userId);

            // Update current user entity
            if (empty($userEntity->id)) { // For normal login pass an empty user entity
                $userEntity->retrieve_entity_info($userId, $userEntity->module_name);
            }

            vglobal('current_user', $userEntity);

            // Prevent access from api users that is for 3rd system integration only
            checkAccessFromApiUser();

			// Track the login history
            $userModuleModel = Users_Module_Model::getInstance('Users');
            $userModuleModel->saveLoginHistory($username);

            $response = [
                'token' => $sessionId,
                'user_info' => self::_getProfile(),
                'home_screen_config' => Users_Preferences_Model::loadPreferences($userEntity->id, 'home_screen_config'), // Added by Phu Vo on 2021.03.20
                'metadata' => self::_getMetadata(),
                'update_counters' => self::_getCounters($userId),
                'callcenter_info' => self::_getCallCenterInfo()
            ];

			self::setResponse(200, $response);
        } 
        else {
			self::setResponse(401);
		}
    }

    // Implemented by Phu Vo on 2019.01.07
    protected static function _getProfile() {
        $userId = self::getCurrentUserId();
        $userInfo = [];

        if (!empty($userId)) {
            $userRecordModel = Vtiger_Record_Model::getInstanceById($userId, 'Users');
        
            $userInfo = $userRecordModel->getData();
            $userInfo['avatar'] = self::_getUserAvatarFromArray($userInfo);

            unset($userInfo['user_password']);
            unset($userInfo['confirm_password']);
            unset($userInfo['accesskey']);
        }

        return $userInfo;
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    protected static function _setAuthSession($sessionId, $userId) {
        global $adb;

        // Check if this session_id is exists
        $sql = "SELECT COUNT(session_id) FROM oauth_sessions WHERE session_id = ? AND user_id = ?";
        $isExist = $adb->getOne($sql, [$sessionId, $userId]);

        if ($isExist) {
            // Update auth_time if this session is already exists
            $sql = "UPDATE oauth_sessions SET auth_time = NOW() WHERE session_id = ?";
            $adb->pquery($sql, [$sessionId]);
        }
        else {
            // Insert this session_id if it is not exists
            $sql = "INSERT INTO oauth_sessions (session_id, user_id, auth_time) VALUES (?, ?, NOW())";
            $adb->pquery($sql, [$sessionId, $userId]);
        }

        // Prevent multiple login
        $sql = "DELETE FROM oauth_sessions WHERE user_id = ? AND session_id != ?";
        $adb->pquery($sql, [$userId, $sessionId]);

        parent::_setAuthSession($userId);
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    static function checkSession($token) {
        global $adb;

        // Check if id on oauth_session
        $sql = "SELECT * FROM oauth_sessions WHERE session_id = ?";
        $result = $adb->pquery($sql, [$token]);
        if ($result) $session = $adb->fetchByAssoc($result); // Added by Phu Vo on 2019.01.07 => Just in case return false

        if (empty($session)) {
            session_destroy();
            self::setResponse(401);
        }

        // Update time
        $sql = "UPDATE oauth_sessions SET auth_time = NOW() WHERE session_id = ?";
        $adb->pquery($sql, [$token]);
        
        // Bug: #-- Bug: #2580 Added by Phu Vo on 2021.05.31 for Backward compatability
        $_SESSION['authenticated_user_id'] = $session['user_id'];
        $_SESSION['app_unique_key'] = vglobal('application_unique_key');
        $_SESSION['authenticated_user_language'] = vglobal('default_language');
        // End Phu Vo

        // if (!isset($_SESSION['authenticated_user_id'])) {
        //     session_destroy();
        //     self::setResponse(401);
        // }

        // // Init currrent user in every request
        // $userId = Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);

        // if (empty($userId)) {
        //     self::setResponse(401);
        // }

        $user = CRMEntity::getInstance('Users');
        $user->retrieveCurrentUserInfoFromFile($session['user_id']);
        vglobal('current_user', $user);

        // Prevent access from api users that is for 3rd system integration only
        checkAccessFromApiUser();
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    protected static function _changePassword($userId, $newPassword) {
        try {
            vimport('~~/include/Webservices/Custom/ChangePassword.php');
            
            $wsUserId = vtws_getWebserviceEntityId('Users', $userId);
            $user = Users::getRootAdminUser();

            vtws_changePassword($wsUserId, '', $newPassword, $newPassword, $user);
            return true;
        }
        catch (Exception $ex) {
            return false;
        }
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    static function logout($sessionId) {
        global $adb;

        if (empty($sessionId)) {
            self::setResponse(400);
        }

        // Clear session
        session_destroy();
        $sql = "DELETE FROM oauth_sessions WHERE session_id = ?";
        $adb->pquery($sql, [$sessionId]);

        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Hieu Nguyen on 2019-12-16
    static function getModuleMetadata(Vtiger_Request $request) {
        $params = $request->get('Params');
        $moduleName = $params['module'];
        
        // Validate request
        if (empty($moduleName)) {
            self::setResponse(400);
        }

        $response = [
            'success' => 1,
            'metadata' => [
                'enum_list' => self::_getEnumList($moduleName),
                'field_list' => self::_getFieldList($moduleName), // Added by Phu Vo on 2021.03.20
                'dependence_list' => self::_getDependenceList($moduleName), // Added by Phu Vo on 2021.03.20
            ]
        ];

        self::setResponse(200, $response);
    }

    static function getMetadata() {
        $response = [
            'success' => 1,
            'metadata' => self::_getMetadata(),
        ];

        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    protected static function _getMetadata() {
        $meta = [
            'user_list' => self::_getUserList(),
            'group_list' => self::_getGroupList(),
            'enum_list' => self::_getEnumList(),
            'dependence_list' => self::_getDependenceList(), // Added by Phu Vo on 2021.03.20
            'forbidden_features' => getForbiddenFeatures(),
            'validation_config' => vglobal('validationConfig'),
            'modules_permissions' => self::_getModulesPermissions(),
        ];

        return $meta;
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    protected static function _getUserList() {
        global $adb;

        $sql = "SELECT id, user_name, first_name, last_name, imagename, email1 FROM vtiger_users WHERE deleted = 0"; // Modified by Phu Vo on 2019.11.18 to add email field
        $result = $adb->pquery($sql);
        $userList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['first_name'] = $row['first_name'];
            $row['last_name'] = $row['last_name'];
            $row['full_name'] = trim(getFullNameFromArray('Users', $row));
            $row['avatar'] = self::_getUserAvatarFromArray($row);
            
            $userList[$row['id']] = $row;
        }

        return $userList;
    }

    // Implemented by Hieu Nguyen on 2018-10-25
    protected static function _getUserAvatarFromArray($userData) {
        $userModel = Vtiger_Record_Model::getCleanInstance('Users');
        $userModel->setData($userData);
        $avatar = $userModel->getImageDetails();

        if (!empty($avatar[0]['id'])) {
            return "/{$avatar[0]['path']}_{$avatar[0]['name']}";
        }

        return '';
    }

    // Implemented by Phu Vo on 2018-10-25. Modified by Hieu Nguyen on 2019-12-16
    protected static function _getSupportedModules() {
        global $mobileConfig, $hiddenModules;
        $forbiddenModules = getForbiddenFeatures('module');
        $supportedModules = array_diff($mobileConfig['supported_modules'], $hiddenModules, $forbiddenModules);
        return $supportedModules;
    }

    // Implemented by Phu Vo on 2018-10-25. Modified by Hieu Nguyen on 2019-12-16
    protected static function _getEnumList($moduleName = 'all') {
        global $adb;
        
        // Get all picklist fields
        $sql = "SELECT DISTINCT t.name AS module_name, f.fieldname AS field_name 
            FROM vtiger_field f
            INNER JOIN vtiger_tab t ON (t.tabid = f.tabid)
            WHERE f.uitype IN (15, 16, 55) AND f.fieldname NOT IN ('firstname') ";
        $params = [];
        
        // Modified by Hieu Nguyen on 2022-06-23 to limit result for supported modules only
        $supportedModules = self::_getSupportedModules();

        if ($moduleName == 'all') {
            $sql .= "AND t.name IN ('" . join("', '", $supportedModules) . "')";
        }
        else {
            $sql .= "AND t.name = ?";
            $params = [$moduleName];
        }
        // End Hieu Nguyen

        $result = $adb->pquery($sql, $params);
        $pickLists = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $pickListValues = self::_getPickListValues($row['module_name'], $row['field_name']);
            $pickLists[$row['module_name']][$row['field_name']] = $pickListValues;
        }

        // Get all active languages
        $sql = "SELECT DISTINCT prefix, label, isdefault
            FROM vtiger_language
            WHERE active = 1";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $pickLists['Vtiger']['languages'][] = [
                'value' => $row['prefix'],
                'key' => $row['prefix'],
                'label' => $row['label'],
                'default' => $row['isdefault'],
            ];
        }

        // Process time picker options for Events and Calendar
        $timePickerOptions = self::_getTimePickerOptions();
        $pickLists['Events']['time_start'] = $timePickerOptions;
        $pickLists['Events']['time_end'] = $timePickerOptions;
        $pickLists['Calendar']['time_start'] = $timePickerOptions;

        if ($moduleName != 'all') {
            return $pickLists[$moduleName];
        }

        return $pickLists;
    }

    // Modified by Hieu Nguyen on 2019-11-06
    protected static function _getGroupList() {
        global $adb;

        $sql = "SELECT groupid AS id, groupname AS name FROM vtiger_groups WHERE is_custom = 0";
        $result = $adb->pquery($sql);
        
        $groups = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $groups[$row['id']] = $row;
        }

        return $groups;
    }

    // Implemented by Hieu Nguyen on 2019-12-16
    protected static function _getFieldList($moduleName) {
        global $adb;

		$sql = "SELECT f.fieldname, f.fieldlabel, f.typeofdata
			FROM vtiger_field AS f 
			INNER JOIN vtiger_tab AS t ON (t.tabid = f.tabid AND t.isentitytype = 1 AND t.name = ?)
            WHERE f.fieldname NOT IN ('starred', 'tags', 'campaignrelstatus')";
		$result = $adb->pquery($sql, [$moduleName]);
		$fieldList = [];

		while ($row = $adb->fetchByAssoc($result)) {
            $isRequired = (strpos($row['typeofdata'], '~M') !== false) ? '1' : '0';
            if ($row['fieldname'] == 'assigned_user_id') $isRequired = '1';

			$fieldList[$row['fieldname']] = [
                'name' => $row['fieldname'],
                'label' => vtranslate($row['fieldlabel'], $moduleName),
                'required' => $isRequired
            ];
		}

		return $fieldList;
    }

    /** Implemented by Phu Vo on 2021.03.24 */
    // Modified by Hieu Nguyen on 2022-06-23 to limit result for only supported modules in config
    protected static function _getDependenceList($moduleName = 'all') {
        global $adb;
        $unsupportedModules = ['Events'];    // Events will handle using Calendar module
        $supportedModules = array_diff(self::_getSupportedModules(), $unsupportedModules);
        $moduleNames = [];
        $dependenceList = [];

        if ($moduleName == 'all') {
            $query = "SELECT name FROM vtiger_tab WHERE isentitytype = 1 AND name IN ('". join("', '", $supportedModules) ."')";

            $result = $adb->pquery($query);

            while ($row = $adb->fetchByAssoc($result)) {
                $moduleNames[] = $row['name'];
            }
        }
        else {
            $moduleNames = [$moduleName];
        }
        
        foreach ($moduleNames as $entityName) {
            $dependenceList[$entityName] = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($entityName);
        }

        if ($moduleName != 'all') {
            return $dependenceList[$moduleName];
        }

        return $dependenceList;
    }

    /** Implemented by Phu Vo on 2021.07.21 */
    protected static function _getModulesPermissions() {
        // Modified by Hieu Nguyen on 2022-06-23 to load supported modules from config
        global $current_user;
        $mobileModules = self::_getSupportedModules();
        // End Hieu Nguyen
        
        $roleId = $current_user->roleid;
        $roleRecordModel = Settings_Roles_Record_Model::getInstanceById($roleId);
        $profileId = $roleRecordModel->getDirectlyRelatedProfileId();
        $modulesPermissions = [];
        $roleProfiles = [];
        $actionModels = Vtiger_Action_Model::getAllBasic(true);

        if ($profileId) {
            $roleProfiles = [Settings_Profiles_Record_Model::getInstanceById($profileId)];
        }
        else {
            $roleProfiles = $roleRecordModel->getProfiles();
        }
        
        foreach ($roleProfiles as $profileRecordModel) {
            foreach ($mobileModules as $moduleName) {
                if (!isset($modulesPermissions[$moduleName])) {
                    $modulesPermissions[$moduleName] = [];
                }
                
                foreach ($actionModels as $actionModel) {
                    $actionName = $actionModel->getName();

                    if (!$profileRecordModel->hasModuleActionPermission($moduleName, $actionModel)) {
                        $modulesPermissions[$moduleName][$actionName] = 0;
                    }

                    if ($modulesPermissions[$moduleName][$actionName] !== 0) {
                        $modulesPermissions[$moduleName][$actionName] = 1;
                    }
                }
            }
        }

        // Added by Hieu Nguyen on 2021-08-30 to check if module Help Desk can be used in mobile app
        if (isForbiddenFeature('CaptureTicketsViaSalesApp')) {
            $modulesPermissions['HelpDesk'] = 0;
        }
        // End Hieu Nguyen

        return $modulesPermissions;
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    protected static function _getRecord($moduleName, $id, $referenceFields = [], $relatedModules = []) {
        global $current_user;

        // Process
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

            // Added by Nghia Nguyen on 2021.07.27
            if ($data['record_module'] === 'Documents') {
                $documentFile = reset(self::_getRelatedDocumentsListByIds([$recordModel->getId()]));
                $data['file_type'] = $documentFile['file_type'];
                $data['file_url'] = $documentFile['file_url'];
            }
            // End Nghia Nguyen

            // Get record avatar default 
            $peopleModules = ['Contacts'];

            if ($data['imagename']) {
                if ($data['record_module'] === 'Contacts') {
                    $data['avatar'] = self::getVtigerRecordImageLinkFromRecordModel($recordModel);
                }
                else {
                    $data['imagename_path'] = self::getVtigerRecordImageLinkFromRecordModel($recordModel);
                }
            }

            if ($data['record_module'] === 'Calendar' && $data['activitytype'] === 'Meeting') {
                $linkFields = ['checkin_salesman_image', 'checkin_customer_image'];

                foreach ($linkFields as $field) {
                    if (!empty($data[$field])) $data[$field] = '/' . $data[$field];
                }
            }

            // Custom logic for module Events
            if ($data['record_module'] === 'Calendar' && $data['activitytype'] === 'Call') {
                $data['can_play_recording'] = PBXManager_CallLog_Model::canPlayRecording($recordModel->getId());
            }

            self::_resolveOwnersName($data);

            if ($data['record_module'] === 'Calendar' || $data['record_module'] === 'Events') {
                self::_resolveCalendarInvitees($data);
            }

            // Process tag list
            $taggedInfo = Vtiger_Tag_Model::getAllAccessible($current_user->id, $data['record_module'], $recordModel->getId());
            $data['tags'] = [];

            foreach ($taggedInfo as $tagModel) {
                $tagData = [
                    'id' => $tagModel->getId(),
                    'tag' => $tagModel->get('tag'),
                    'visibility' => $tagModel->get('visibility'),
                ];

                $data['tags'][] = $tagData;
            }

            // Respond
            $response = [
                'success' => 1,
                'data' => decodeUTF8($data),
                'metadata' => [
                    'enum_list' => self::_getEnumList($moduleName),
                    'dependence_list' => self::_getDependenceList($moduleName), // Added by Phu Vo on 2021.03.20
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
    
    /** Implemented by Phu Vo on 2021.03.12 */
    protected static function _resolveQueryExtraJoin($query, $extraJoins = []) {
        // We need to extract WHERE query string from root $query
        $query = preg_replace('/\s+/', ' ', $query);
        $lowerQuery = strtolower($query);
        $fromPos = strpos($lowerQuery, 'from');
        $wherePos = strrpos($lowerQuery, 'where');
        $prefix = $infix = $postfix = '';
        
        $prefix = trim(substr($query, 0, $fromPos));
        $infix = trim(substr($query, $fromPos, $wherePos - $fromPos));
        $postfix = trim(substr($query, $wherePos));
        $lowerInfix = strtolower($infix);
        $infixComponents = preg_split('/\s+/i', $lowerInfix);

        foreach ($extraJoins as $extraJoin) {
            // Check if extra join is already in infix component
            $extraJoin = trim(preg_replace('/\s+/', ' ', $extraJoin));
            $transformedExtraJoin = strtolower($extraJoin);
            $components = preg_split('/\s+/i', $transformedExtraJoin);
            $joinIndex = array_search('join', $components);
            $joinTable = $components[$joinIndex + 1];
            $isJoinTableAliased = $components[$joinIndex + 2] == 'as'; // Client always have to use as
            $joinAlias = $isJoinTableAliased ? $components[$joinIndex + 3] : '';

            if (empty($joinTable)) continue;

            if (in_array($joinTable, $infixComponents)) {
                // Both have the same alias
                if ($isJoinTableAliased && in_array($joinAlias, $infixComponents)) continue;

                $duplicateIndex = array_search($joinTable, $infixComponents);
                $nextToDuplicate = $infixComponents[$duplicateIndex + 1];
                $isDuplicateAliased = $nextToDuplicate == 'as' || $nextToDuplicate != 'on';

                // Both isn't aliased
                if (!$isDuplicateAliased && !$isJoinTableAliased) continue;
            }

            $infix .= ' ' . $extraJoin;
        }
        
        $query = $prefix . ' ' . $infix . ' ' . $postfix . ' ';

        return $query;
    }

    /** Implemented by Phu Vo on 2021.03.12 */
    protected static function _getModuleCvIdList($moduleName) {
        $allCustomViews = CustomView_Record_Model::getAllByGroup($moduleName);
        $customViews = [];

        $customViews['mine'] = [];
        foreach ($allCustomViews['Mine'] as $customViewModel) {
            $customViewInfo = $customViewModel->getData();
            $customView = [];
            $customView['cv_id'] = $customViewInfo['cvid'];
            $customView['viewname'] = vtranslate($customViewInfo['viewname']);
            $customViews['mine'][] = decodeUTF8($customView);
        }

        $customViews['shared'] = [];
        foreach ($allCustomViews['Shared'] as $customViewModel) {
            $customViewInfo = $customViewModel->getData();
            $customView = [];
            $customView['cv_id'] = $customViewInfo['cvid'];
            $customView['viewname'] = vtranslate($customViewInfo['viewname']);
            $customViews['shared'][] = decodeUTF8($customView);
        }

        return $customViews;
    }

    protected static function _getCounterModuleMap($moduleName) {
        $mapper = [
            'Calendar' => 'activities',
            'Potentials' => 'opportunities',
            'HelpDesk' => 'tickets'
        ];

        if ($mapper[$moduleName]) return $mapper[$moduleName];

        return strtolower($moduleName);
    }

    protected static function _getRelatedCount($recordModel, $relatedModuleName) {
        $relationListModel = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);

        if ($relationListModel->getRelationModel()) {
            return $relationListModel->getRelatedEntriesCount();
        }

        return 0;
    }

    protected static function _getRelatedList($recordModel, $relatedModuleName) {
        $relatedIds = self::_getRelatedIdList($recordModel, $relatedModuleName);
        
        if ($relatedModuleName === 'Calendar') {
            return self::_getRelatedCalendarListByIds($relatedIds);
        }

        if ($relatedModuleName === 'Potentials') {
            return self::_getRelatedPotentialListByIds($relatedIds);
        }

        if ($relatedModuleName === 'Contacts') {
            return self::_getRelatedContactListByIds($relatedIds);
        }

        if ($relatedModuleName === 'HelpDesk') {
            return self::_getRelatedHelpDeskListByIds($relatedIds);
        }

        if ($relatedModuleName === 'ModComments') {
            return self::_getRelatedModCommentsListByIds($relatedIds);
        }

        if ($relatedModuleName === 'Documents') {
            return self::_getRelatedDocumentsListByIds($relatedIds);
        }

        // Added by Nghia Nguyen on 2021.07.27
        if ($relatedModuleName === 'Leads') {
            return self::_getRelatedLeadsListByIds($relatedIds);
        }

        if ($relatedModuleName === 'Accounts') {
            return self::_getRelatedAccountsListByIds($relatedIds);
        }
        // End Nghia Nguyen

        return [];
    }

    protected static function _getRelatedIdList($recordModel, $relatedModuleName) {
        global $adb;
        $relationListModel = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);
        $relatedFocus = CRMEntity::getInstance($relatedModuleName);
        $idColumn = $relatedFocus->table_index;
        
        if ($relationListModel->getRelationModel()) {
            $sql = $relationListModel->getRelationQuery();
            
            $result = $adb->query($sql);
            
            $returnArr = [];

            while ($row = $adb->fetchByAssoc($result)) {
                $id = $row[$idColumn];
                if (empty($id)) $id = $row['crmid'];
                
                $returnArr[] = $id;
            }

            return $returnArr;
        }

        return '';
    }

    protected static function _getRelatedCalendarListByIds($ids) {
        global $adb, $current_user;
        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT a.activityid, a.subject, a.activitytype, a.status AS taskstatus, a.eventstatus, c.contactid, c.firstname, c.lastname, 
            a.date_start, a.time_start,
            IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid 
            FROM vtiger_activity AS a
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_cntactivityrel AS ac ON (ac.activityid = a.activityid)
            LEFT JOIN vtiger_contactdetails AS c ON (c.contactid = ac.contactid)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = a.activityid AND uf.userid = {$current_user->id})
            WHERE a.activityid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;
    }

    protected static function _getRelatedPotentialListByIds($ids) {
        global $adb, $current_user;
        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT p.potentialid, p.potential_no, p.potentialname, p.sales_stage, p.amount, p.closingdate,
            a.accountname, c.firstname, c.lastname, IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid 
            FROM vtiger_potential AS p
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = p.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_account AS a ON (a.accountid = p.related_to)
            LEFT JOIN vtiger_contactdetails AS c ON (c.contactid = p.contact_id)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = p.potentialid AND uf.userid = {$current_user->id})
            WHERE p.potentialid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;
    }

    protected static function _getRelatedContactListByIds($ids) {
        global $adb, $current_user;
        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT c.contactid, c.contact_no, c.firstname, c.lastname, c.salutation, a.accountname, c.phone, c.mobile, 
        ca.mailingstreet AS address, IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid FROM vtiger_contactdetails AS c
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = c.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_account AS a ON (a.accountid = c.accountid)
            LEFT JOIN vtiger_contactaddress AS ca ON (ca.contactaddressid = c.accountid)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = c.contactid AND uf.userid = {$current_user->id})
            WHERE c.contactid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;
    }

    protected static function _getRelatedHelpDeskListByIds($ids) {
        global $adb, $current_user;

        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT t.ticketid, t.ticket_no, t.title, t.category, t.status, t.solution, IFNULL(uf.starred, 0) AS starred,
            IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid 
            FROM vtiger_troubletickets AS t
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = t.ticketid AND vtiger_crmentity.setype = 'HelpDesk' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = t.ticketid AND uf.userid = {$current_user->id})
            WHERE t.ticketid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;        
    }

    protected static function _getRelatedModCommentsListByIds($ids) {
        global $adb, $current_user;

        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT vtiger_modcomments.*, IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred, createdtime, smcreatorid, smownerid 
            FROM vtiger_modcomments
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_modcomments.modcommentsid)
            LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_modcomments.modcommentsid AND vtiger_crmentity_user_field.userid = {$current_user->id})
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_modcomments.modcommentsid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        while ($row = $adb->fetchByAssoc($result)) {
            // Get attachments info
            $recordModel = Vtiger_Record_Model::getCleanInstance('ModComments');
            $row['attachments'] = $recordModel->getFileNameAndDownloadURL($row['modcommentsid']);
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr; 
    }

    protected static function _getDownloadFileUrl($recordId, $moduleName) {
        global $site_URL;

        if (empty($recordId) || empty($moduleName)) return '';
        return "{$site_URL}/entrypoint.php?name=DownloadFile&module={$moduleName}&record={$recordId}";
    }

    /** Implemented by Phu Vo on 2021.07.21 */
    protected static function _getRelatedDocumentsListByIds($ids) {
        global $adb, $current_user, $site_URL;

        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT vtiger_notes.*, vtiger_seattachmentsrel.attachmentsid, IFNULL(vtiger_crmentity_user_field.starred, 0) AS starred, createdtime, smcreatorid, smownerid 
            FROM vtiger_notes
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_notes.notesid)
            LEFT JOIN vtiger_crmentity_user_field ON (vtiger_crmentity_user_field.recordid = vtiger_notes.notesid AND vtiger_crmentity_user_field.userid = {$current_user->id})
            LEFT JOIN vtiger_seattachmentsrel ON (vtiger_notes.notesid = vtiger_seattachmentsrel.crmid)
            WHERE vtiger_crmentity.deleted = 0 AND vtiger_notes.notesid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            // Get attachments info
            $row['file_url'] = $row['filename'];
            $row['file_type'] = 'url';

            if (!empty($row['attachmentsid'])) {
                $row['file_url'] = self::_getDownloadFileUrl($row['notesid'], 'Documents');
                $row['file_type'] = 'file';
            }
            
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr; 
    }

    // Implemented by Nghia Nguyen on 2021-07-28
    protected static function _getRelatedLeadsListByIds($ids) {
        global $adb, $current_user;
        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT l.leadid, l.lead_no, l.firstname, l.lastname, l.salutation, ca.campaignname, la.phone, la.mobile, 
        la.lane AS address, IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid FROM vtiger_leaddetails AS l
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = l.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_campaign AS ca ON (ca.campaignid = l.related_campaign)
            LEFT JOIN vtiger_leadaddress AS la ON (la.leadaddressid = l.leadid)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = l.leadid AND uf.userid = {$current_user->id})
            WHERE l.leadid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];

        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;
    }

    // Implemented by Nghia Nguyen on 2021-07-28
    protected static function _getRelatedAccountsListByIds($ids) {
        global $adb, $current_user;
        $idStrings = "'" . implode("','", $ids) . "'";

        $sql = "SELECT a.accountid, a.account_no, a.accountname, a.account_type, a.phone, a.otherphone, a.email1, a.website, aa.accountname AS member_of,
        ba.bill_street, ba.bill_city AS address, IFNULL(uf.starred, 0) AS starred, createdtime, smcreatorid, smownerid FROM vtiger_account AS a
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = a.accountid AND vtiger_crmentity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN vtiger_account AS aa ON (aa.accountid = a.parentid)
            LEFT JOIN vtiger_accountbillads AS ba ON (ba.accountaddressid = a.accountid)
            LEFT JOIN vtiger_crmentity_user_field AS uf ON (uf.recordid = a.accountid AND uf.userid = {$current_user->id})
            WHERE a.accountid IN ({$idStrings})";

        $result = $adb->query($sql);

        $returnArr = [];
        
        while ($row = $adb->fetchByAssoc($result)) {
            self::_resolveOwnersName($row); // Added by Phu Vo on 2019.11.06 to resolve custom owner name
            $returnArr[] = $row;
        }

        return $returnArr;
    }

    // Implemented by Hieu Nguyen on 2018-11-13. This function accepts $processCallback and $saveCallback as annonymous functions
    protected static function _saveRecord($moduleName, $data, $processCallback = null, $saveCallback = null) {
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
                self::_checkRecordAccessPermission($moduleName, 'Save', $id);
                $recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
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

    // Implemented by Hieu Nguyen on 2018-11-13
    protected static function _getCounters($userId) {
        global $adb;

        // Modified by Phu Vo on 2019.09.30 to get counter base on permission
        // Get leads count
        $aclQuery = CRMEntity::getListViewSecurityParameter('Leads');
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            WHERE deleted = 0 {$aclQuery} AND setype = 'Leads'";
        $leadsCount = $adb->getOne($sql);

        // Get accounts count
        $aclQuery = CRMEntity::getListViewSecurityParameter('Accounts');
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            WHERE deleted = 0 {$aclQuery} AND setype = 'Accounts'";
        $accountsCount = $adb->getOne($sql);

        // Get contacts count
        $aclQuery = CRMEntity::getListViewSecurityParameter('Contacts');
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            WHERE deleted = 0 {$aclQuery} AND setype = 'Contacts'";
        $contactsCount = $adb->getOne($sql);

        // Get opportunities count
        $aclQuery = CRMEntity::getListViewSecurityParameter('Potentials');
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            WHERE deleted = 0 {$aclQuery} AND setype = 'Potentials'";
        $opportunitiesCount = $adb->getOne($sql);

        // Get tickets count
        $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');
        $sql = "SELECT count(crmid) 
            FROM vtiger_crmentity 
            WHERE deleted = 0 {$aclQuery} AND setype = 'HelpDesk'";
        $ticketsCount = $adb->getOne($sql);

        // Get notifications count
        $notificationsCount = self::_getNotificationsCount();
        
        $result = [
            'leads_count' => $leadsCount,
            'accounts_count' => $accountsCount,
            'contacts_count' => $contactsCount,
            'opportunities_count' => $opportunitiesCount,
            'tickets_count' => $ticketsCount,
            'notifications_count' => $notificationsCount,
        ];

        return $result;
    }

    // Implemented by Hieu Nguyen on 2018-11-13
    protected static function _getUniqueDistricts($moduleName) {
        global $adb;
        $districtList = [];
        
        if ($moduleName == 'Leads') {
            $sql = "SELECT DISTINCT(city) AS district FROM vtiger_leadaddress WHERE city IS NOT NULL AND city != ''";
        }

        if ($moduleName == 'Accounts') {
            $sql = "SELECT DISTINCT(bill_city) AS district FROM vtiger_accountbillads WHERE bill_city IS NOT NULL AND bill_city != ''";
        }
        
        if ($moduleName == 'Contacts') {
            $sql = "SELECT DISTINCT(mailingcity) AS district FROM vtiger_contactaddress WHERE mailingcity IS NOT NULL AND mailingcity != ''";
        }
        
        $result = $adb->query($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $districtList[] = html_entity_decode($row['district']);
        }

        return $districtList;
    }

    // Created by Phu Vo on 2018.02.28 => Map api mobile file request with vtiger input format, use for default saving image logic
    protected static function mapImageFileForSaving(&$data, $imageFieldMapping = ['Avatar' => 'imagename'], $replace = true) {
        // Mark delete old image
        if ($replace) $data['imgDelete'] = true;
        
        if ($_FILES) {
            $files = [];

            foreach ($_FILES as $fileName => $file) {
                $files[$fileName] = [];
                foreach ($file as $info => $value) {
                    if (is_array($value)) {
                        $files[$fileName][$info] = $value;
                    }
                    else {
                        $files[$fileName][$info] = [$value];
                    }
                }
            }

            $_FILES = $files;
        }

        if ($imageFieldMapping && is_array($imageFieldMapping)) {
            foreach ($imageFieldMapping as $field => $mapped) {
                if ($_FILES[$field]) {
                    $_FILES[$mapped] = $_FILES[$field];
                    unset($_FILES[$field]);

                    $data[$mapped] = $_FILES[$mapped]['name'][0];
                }
            }
        }
    }

    // Created by Phu Vo on 2018.02.28 get vtiger attachment image
    protected static function getVtigerRecordImageLinkFromRecordModel($recordModel) {
        $detail = $recordModel->getImageDetails();

        if (!empty($detail[0]['id'])) {
            return "/{$detail[0]['path']}_{$detail[0]['name']}";
        }

        return '';
    }

    // Implemented by Phu Vo on 2020.03.31
    protected static function _getTimePickerOptions() {
        global $current_user;
        
        $userTimeFormat = $current_user->hour_format == '12' ? 'h:i A' : 'H:i';
        $dateTimeObject = new DateTime();
        $result = [];

        for ($i = 0; $i < 24; $i += 1) {
            for ($j = 0; $j < 2; $j += 1) {
                $minute = $j == 0 ? 0 : 30;
                $dateTimeObject->setTime($i, $minute);

                $result[] = [
                    'value' => $dateTimeObject->format('H:i'),
                    'color' => '',
                    'assigned' => '1',
                    'key' => $dateTimeObject->format('H:i'),
                    'label' => $dateTimeObject->format($userTimeFormat),
                ];
            }
        }

        return $result;
    }

    protected static function _resolveCalendarInvitees(&$data) {
        $contactInvitees = Events_Invitation_Helper::getInviteesForDisplay($data['id'], 'Contacts');
        $userInvitees = Events_Invitation_Helper::getInviteesForDisplay($data['id'], 'Users');

        foreach ($contactInvitees as $index => $contactInvitee) {
            $contactInvitees[$index]['display_status'] = vtranslate($contactInvitee['status'], 'Calendar');
        }

        foreach ($userInvitees as $index => $userInvitee) {
            $userInvitees[$index]['display_status'] = vtranslate($userInvitee['status'], 'Calendar');
        }
        $data['contact_invitees'] = $contactInvitees;
        $data['users_invitees'] = $userInvitees;
    } 

    // Implemented by Hieu Nguyen on 2020-07-24
    protected static function _getCallCenterInfo() {
        global $current_user, $site_URL; // Added by Phu Vo on 2020.07.29
        $activeConnector = PBXManager_Server_Model::getActiveConnector();

        if ($activeConnector) {
            $hotline = PBXManager_Logic_Helper::getDefaultOutboundHotline();
            $info = [
                'gateway' => $activeConnector->getGatewayName(),
                'hotline' => !empty($hotline) ? PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($hotline) : '',
            ];
            
            if (method_exists($activeConnector, 'getWebPhoneToken')) {
                $token = PBXManager_Logic_Helper::getWebPhoneToken();
                $info['softphone_type'] = 'WebRTC';
                $info['softphone_token'] = $token;

                // Modified by Phu Vo on 2020.07.29 to return custom ringtone
                $configs = Users_Preferences_Model::loadPreferences($current_user->id, 'callcenter_config', true);
                $info['custom_ringtone'] = $configs['custom_ringtone'] ? "{$site_URL}/upload/webphone_ringtone/ringtone_{$current_user->id}" : '';
                // End Phu Vo
            }

            if (method_exists($activeConnector, 'getSIPCredentials')) {
                $credentials = $activeConnector->getSIPCredentials();
                $info['softphone_type'] = 'SIP';
                $info['softphone_credentials'] = $credentials;
            }

            return $info;
        }

        return [];
    }

    // Implemented by Hieu Nguyen on 2020-09-04
    static function getReportList(Vtiger_Request $request) {
        require_once('modules/Reports/Reports.php');
        global $adb;

        // Validate request
        $params = $request->get('Params');
        $folderId = $params['folder_id'];
        $keyword = strtoupper($params['keyword']);
        $paging = $params['paging'];

        if (empty($params) || empty($folderId) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $paramsList = ['searchParams' => [['reportname', 'c', $keyword]]];
        $reportListQuery = Reports::getReportListQuery($folderId, $paramsList);
        $sql = $reportListQuery['sql'];
        $sqlParams = $reportListQuery['params'];

        $select = "SELECT vtiger_report.reportid AS id, vtiger_report.reportname AS name, vtiger_reportfolder.foldername AS folder_name, 
            vtiger_reportmodules.primarymodule AS primary_module, vtiger_report.owner AS main_owner_id ";
        $fromKeywordPos = strpos($sql, 'FROM');
        $fromAndwhere = substr($sql, $fromKeywordPos) . " AND (vtiger_report.has_chart = 1 OR vtiger_report.reporttype = 'chart') ";

        // Sorting
        $orderBy = "ORDER BY vtiger_report.reportid DESC ";   // Default sort is required

        if (!empty($paging['order_by'])) {
            $orderBy = "ORDER BY vtiger_report.{$paging['order_by']} ";
        }

        // Paging
        $paginate = "LIMIT {$paging['offset']}, {$paging['max_results']} ";

        // Main query
        $sql = $select . $fromAndwhere . $orderBy . $paginate;

        $result = $adb->pquery($sql, $sqlParams);
        $entryList = [];
        $count = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $row['folder_name'] = vtranslate($row['folder_name'], 'Reports');
            $row['primary_module'] = vtranslate($row['primary_module'], 'Vtiger');
            self::_resolveOwnersName($row);
            $entryList[] = decodeUTF8($row);
            $count++;
        }

        // Count total
        $sqlTotalCount = "SELECT COUNT(vtiger_report.reportid) AS total_count {$fromAndwhere}";
        $totalCount = $adb->getOne($sqlTotalCount, $sqlParams);

        // Respond
        $response = self::_getResponseWithPaging($entryList, $paging['offset'], $count, $totalCount);
        $response['enum_list'] = ['folder_list' => self::_getReportFolderList()];
        
        self::setResponse(200, $response);
    }

    // Implemented by Hieu Nguyen on 2020-09-04
    protected static function _getReportFolderList() {
        global $adb;
        $sql = "SELECT folderid AS id, foldername AS name FROM vtiger_reportfolder ORDER BY folderid";
        $result = $adb->pquery($sql, []);
        $folderList = [
            ['id' => 'All', 'text' => vtranslate('LBL_ALL_REPORTS', 'Reports')],
            ['id' => 'shared', 'text' => vtranslate('LBL_SHARED_REPORTS', 'Reports')]
        ];

        while ($row = $adb->fetchByAssoc($result)) {
            $folderList[] = [
                'id' => $row['id'],
                'text' => vtranslate($row['name'], 'Reports'),
            ];
        }

        return $folderList;
    }

    protected static function _resolveOrderingSql(array $orderFields, $moduleName) {
        global $adb;

        $columnsString = "('" . join("', '", array_keys($orderFields)) . "')";

        $sql = "SELECT vtiger_field.columnname, vtiger_field.tablename
            FROM vtiger_field
            INNER JOIN vtiger_tab ON (vtiger_field.tabid = vtiger_tab.tabid)
            WHERE
                vtiger_tab.name = ? 
                AND columnname IN $columnsString";

        $result = $adb->pquery($sql, [$moduleName]);
        $columnTableMapping = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $columnTableMapping[$row['columnname']] = $row['tablename'];
        }

        $counter = 0;
        $orderBySql = "ORDER BY ";

        foreach ($orderFields as $orderField => $orderDirection) {
            if (empty($columnTableMapping[$orderField])) continue;
            if ($counter != 0) $orderBySql .= ', ';
            
            $orderBySql .= "{$columnTableMapping[$orderField]}.{$orderField} {$orderDirection} ";
            $counter++;
        }

        return $orderBySql;
    }
}