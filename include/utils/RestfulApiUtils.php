<?php

/*
*   Class RestfulApiUtils
*   Author: Hieu Nguyen
*   Date: 2018-10-02
*   Purpose: A parent class for restful api
*/

$app_strings = return_application_language('en_us');

class RestfulApiUtils {

    protected static $logger;

    // Implemented by Hieu Nguyen on 2018-11-13
    static function getCurrentUserId() {
        return Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);
    }

    // Implemented by Hieu Nguyen on 2018-11-12
    static function getReferenceNameFromId($moduleModel, $referenceFieldName, $referenceId) {
        $referenceName = $moduleModel->getField($referenceFieldName)->getEditViewDisplayValue($referenceId);
        
        return trim($referenceName); // Modified by Phu Vo on 2019.11.04 to remove spaces
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    static function setResponse($code, $data = '') {
        if (!function_exists('http_response_code')) {
            header('HTTP/1.1 '. $code);
        }
        else {
            http_response_code($code);
        }

        // Added by Phu Vo on 2019.11.04 to decode output
        $data = decodeUTF8($data);
        // End Phu Vo

        if (is_array($data)) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        else {
            echo $data;
        }

        exit;
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    static function getRequest() {
        $request = $_REQUEST;
		$json = file_get_contents('php://input');
		$input = json_decode($json, true);

		if (!is_array($input)) {
			parse_str($json, $input);
		}

		if (!empty($input)) {
			$request = array_merge($request, $input);
		}

        // Handle multiplart form data
        if ($request['IsMultiPartData'] == '1') {
            foreach ($request as $field => $value) {
                $jsonValue = json_decode(html_entity_decode($value), true);

                if ($jsonValue != null) {
                    $request[$field] = $jsonValue;
                }
                else {
                    $request[$field] = $value;
                }
            }
        }

        return new Vtiger_Request($request, $request, true);
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    static function logout($sessionId) {
        if (empty($sessionId)) {
            self::setResponse(400);
        }

        // Clear session
        session_destroy();

        self::setResponse(200, array('success' => 1));
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    protected static function _setAuthSession($userId) {
        Vtiger_Session::set('AUTHUSERID', $userId);

        // For Backward compatability
        $_SESSION['authenticated_user_id'] = $userId;
        $_SESSION['app_unique_key'] = vglobal('application_unique_key');
        $_SESSION['authenticated_user_language'] = vglobal('default_language');

        // Enabled session variable for KCFINDER 
        $_SESSION['KCFINDER'] = array(); 
        $_SESSION['KCFINDER']['disabled'] = false; 
        $_SESSION['KCFINDER']['uploadURL'] = 'test/upload'; 
        $_SESSION['KCFINDER']['uploadDir'] = '../test/upload';
        $_SESSION['KCFINDER']['deniedExts'] = implode(' ', vglobal('upload_badext'));
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    static function checkSession($token) {
        session_id($token);

        if (!isset($_SESSION['authenticated_user_id'])) {
            session_destroy();
            self::setResponse(401);
        }

        // Init currrent user in every request
        $userId = Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);

        if (empty($userId)) {
            self::setResponse(401);
        }

        $user = CRMEntity::getInstance('Users');
        $user->retrieveCurrentUserInfoFromFile($userId);
        vglobal('current_user', $user);
    }

    // Implemented by Hieu Nguyen on 2018-10-24
    protected static function _checkRecordAccessPermission($moduleName, $actionName, $id) {
        global $adb, $app_strings;

        if (!Users_Privileges_Model::isPermitted($moduleName, $actionName, $id)) {
            self::setResponse(200, array('success' => 0, 'message' => 'ACCESS_DENIED'));
        }

        $focus = CRMEntity::getInstance($moduleName);
        $sql = "SELECT COUNT({$focus->table_index}) 
            FROM {$focus->table_name}
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = {$focus->table_index} AND e.setype = '{$moduleName}' AND e.deleted = 0)
            WHERE {$focus->table_index} = ?";
        $params = array($id);
        $recordExists = $adb->getOne($sql, $params);

        if (!$recordExists) {
            throw new Exception($app_strings['LBL_RECORD_NOT_FOUND']);
        }
    }

    // Implemented by Hieu Nguyen on 2018-10-02
    protected static function _getResponseWithPaging($entryList, $offset, $count, $totalCount) {
        $nextOffset = $offset + $count; // Offset starts at 0

        $response = array(
            'success' => 1,
            'entry_list' => $entryList,
            'paging' => array(
                'result_count' => $count,
                'total_count' => intval($totalCount),
                'next_offset' => $nextOffset
            )
        );

        if ($nextOffset >= $totalCount) {
            unset($response['paging']['next_offset']);
        }

        return $response;
    }

    // Implemented by Phu Vo on 2019-02-20
    // Modified by Hieu Nguyen on 2020-07-06 to get the same search result as CRM by Global Search config
    static function globalSearch(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $keyword = $params['keyword'];

        if (empty($keyword)) {
            self::setResponse(200, ['success' => 1, 'entry_list' => [], 'paging' => ['next_offset' => 0]]);
        }

        // Process
        $queryFields = ['id', 'createdtime', 'assigned_user_id', 'main_owner_id'];
        $processor = function (&$row, $moduleName) {
            $row['record_type'] = $moduleName;
            self::_resolveOwnersName($row);
        };

        $matchingRecords = Vtiger_GlobalSearch_Helper::search($keyword, $queryFields, $processor);

        // Return result
        self::setResponse(200, [
            'success' => 1,
            'entry_list' => $matchingRecords
        ]);
    }

    // Implemented by Hieu Nguyen on 2019-11-06
    protected static function _getNotificationsCount() {
        global $current_user;

        return CPNotifications_Data_Model::getNotificationCountsByTypes($current_user->id);
    }

    // Implemented by Hieu Nguyen on 2019-11-06
    static function getNotificationList(Vtiger_Request $request) {
        $params = $request->get('Params');
        $type = $params['type'];
        $subType = $params['sub_type'];
        $paging = $params['paging'];

        // Validate request
        if (empty($params) || empty($paging)) {
            self::setResponse(400);
        }

        // Process
        $result = CPNotifications_Data_Model::loadNotifications($type, $subType, $paging['offset']);

        // Modified by Phu Vo on 2021.03.20 to fix issue next_offset have empty value
        $response = [
            'success' => 1,
            'entry_list' => $result['data'],
            'counts' => $result['counts'],
            'paging' => [
                'counts' => $result['counts'],
                'next_offset' => $result['next_offset']
            ],
        ];

        if ($result['next_offset'] >= $result['counts'] || empty($result['next_offset'])) {
            unset($response['paging']['next_offset']);
        }

        // Return result
        self::setResponse(200, $response);
        // End Phu Vo
    }

    // Implemented by Hieu Nguyen on 2019-11-06
    static function markNotificationsAsRead(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $target = $params['target'];

        if (empty($params) || empty($target)) {
            self::setResponse(400);
        }

        // Process
        CPNotifications_Data_Model::markAsRead($target);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Hieu Nguyen on 2019-11-07
    static function savePushClientToken(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $token = $params['token'];

        if (empty($params) || empty($token)) {
            self::setResponse(400);
        }

        // Process
        CPNotifications_Data_Model::saveClientToken($token);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Hieu Nguyen on 2019-11-07
    static function removePushClientToken(Vtiger_Request $request) {
        // Validate request
        $params = $request->get('Params');
        $token = $params['token'];

        if (empty($params) || empty($token)) {
            self::setResponse(400);
        }

        // Process
        CPNotifications_Data_Model::removeClientToken($token);
        
        // Respond
        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Phu Vo on 2019.11.07
    static function loadSettings() {
        global $adb, $current_user;

        $sql = "SELECT category, value FROM vtiger_user_preferences WHERE user_id = ? ";

        // Declare ignore categories
        $ignoreCategories = ['push_client_tokens'];
        $sql .= "AND category NOT IN ('" . implode("','", $ignoreCategories) . "') "; 

        $result = $adb->pquery($sql, [$current_user->id]);
        $userPreferences = [];
        $userPreferences['calendar_settings'] = Calendar_Settings_Model::getUserSettings();

        while ($row = $adb->fetchByAssoc($result)) {
            $userPreferences[$row['category']] = json_decode(decodeUTF8($row['value']), true);
        }

        self::setResponse(200, ['success' => 1, 'user_preferences' => $userPreferences]);
    }

    // Implemented by Phu Vo on 2019.11.07
    static function saveSettings(Vtiger_Request $request) {
        global $current_user;

        $data = $request->get('Data');

        // Validate input data
        if (empty($data) || !is_array($data)) self::setResponse(400);

        // Declare ignore categories
        $ignoreCategories = ['push_client_tokens'];

        // Perform save action
        foreach ($data as $category => $value) {
            if (in_array($category, $ignoreCategories)) {
                continue;
            }

            Users_Preferences_Model::savePreferences($current_user->id, $category, $value);
        }

        self::setResponse(200, ['success' => 1]);
    }

    // Implemented by Hieu Nguyen on 2019-12-16
    protected static function _getPicklistValues($moduleName, $picklistName) {
        global $adb, $current_user;
        $picklistValues = [];
        $currentUserRoleId = $current_user->roleid;

        $isRestricted = $adb->getOne("SELECT 1 FROM vtiger_picklist WHERE name = ?", [$picklistName]);

        if ($isRestricted) {
            $subRoleIds = getRoleSubordinates($currentUserRoleId);
            $roleIds = array_merge([$currentUserRoleId], $subRoleIds);

            $sql = "SELECT DISTINCT {$picklistName} AS value, color, IF(rp.picklistvalueid, 1, 0) AS assigned FROM vtiger_{$picklistName} AS p
                LEFT JOIN vtiger_role2picklist AS rp ON (rp.picklistvalueid = p.picklist_valueid AND rp.roleid IN (". generateQuestionMarks($roleIds) ."))
                ORDER BY sortorderid";
            $result = $adb->pquery($sql, $roleIds);

            while ($row = $adb->fetchByAssoc($result)) {
                $row['key'] = decodeUTF8($row['value']);
                $row['label'] = vtranslate($row['key'], $moduleName);
                $picklistValues[] = $row;
            }
        }
        else {
            $sql = "SELECT {$picklistName} AS value, color, '1' AS assigned FROM vtiger_{$picklistName} ORDER BY sortorderid";
            $result = $adb->pquery($sql, []);

            while ($row = $adb->fetchByAssoc($result)) {
                $row['key'] = decodeUTF8($row['value']);
                $row['label'] = vtranslate($row['key'], $moduleName);
                $picklistValues[] = $row;
            }
        }
        
        return $picklistValues;
    }

    /** Implemented by Phu Vo on 2021.03.12 */
    // Moved here and refactored by Hieu Nguyen on 2022-12-29
    protected static function _getSqlByCvId($moduleName, $cvId, $paging) {
        if (empty($cvId) || $cvId == '0' || strtolower($cvId) == 'all') {
            $customView = new CustomView();
            $cvId = $customView->getViewIdByName('All', $moduleName);
        }

        $listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
        $pagingModel = new Vtiger_Paging_Model();
        $page = round($paging['offset'] / $paging['max_results']);

        $pagingModel->set('page', $page);
        $pagingModel->set('limit', $paging['max_results']);
        $pagingModel->set('viewid', $cvId);

        if (!empty($paging['order_by'])) {
            $listViewModel->set('orderby', $paging['order_by']);
            $listViewModel->set('sortorder', $paging['sort_order'] ?? 'DESC');
        }

		$moduleName = $listViewModel->getModule()->get('name');
		$queryGenerator = $listViewModel->get('query_generator');
        $searchParams = $listViewModel->get('search_params');
        
		if (empty($searchParams)) {
			$searchParams = [];
        }
        
		$glue = '';

		if (count($queryGenerator->getWhereFields()) > 0 && (count($searchParams)) > 0) {
			$glue = QueryGenerator::$AND;
        }
        
		$queryGenerator->parseAdvFilterList($searchParams, $glue);

		$searchKey = $listViewModel->get('search_key');
		$searchValue = $listViewModel->get('search_value');
        $operator = $listViewModel->get('operator');
        
		if (!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(['search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator]);
		}

		$orderBy = $listViewModel->get('orderby');

		if (!empty($orderBy)) {
			$queryGenerator = $listViewModel->get('query_generator');
			$fieldModels = $queryGenerator->getModuleFields();
			$orderByFieldModel = $fieldModels[$orderBy];

			if (
                $orderByFieldModel && ($orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE ||
				$orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::OWNER_TYPE)
            ) {
				$queryGenerator->addWhereField($orderBy);
			}
        }
        
        $listQuery = $listViewModel->getQuery();
        return $listQuery;
    }

    /** Implemented by Phu Vo on 2021.03.12 */
    // Moved here and refactored by Hieu Nguyen on 2022-12-29
    protected static function _getFromAndWhereSqlByCvId($moduleName, $cvId = '0', $paging) {
        $listQuery = self::_getSqlByCvId($moduleName, $cvId, $paging);
        $queryComponents = preg_split('/ FROM /i', $listQuery, 2);
        $fromAndWhere = ' FROM ' . $queryComponents[1] . ' ';
        return $fromAndWhere;
    }

    // Implemented by Hieu Nguyen on 2019-11-06
    protected static function _resolveOwnersName(&$record) {
        $assignedOwners = [];
        $currentOwners = Vtiger_Owner_UIType::getCurrentOwners($record['smownerid'] ?? $record['assigned_user_id'], false, true);

        foreach ($currentOwners as $owner) {
            $assignedOwners[] = [
                'id' => $owner['id'],
                'name' => $owner['text']
            ];
        }

        $record['main_owner_name'] = trim(getOwnerName($record['main_owner_id']));
        $record['assigned_owners'] = $assignedOwners;
    }

    static function saveLog($description, $info = null) {
        if (!empty(static::$logger)) {
            $logger = LoggerManager::getLogger(static::$logger);
        }
        else {
            $logger = LoggerManager::getLogger('WEBSERVICE');
        }

        // Save log
        $log = 'Description: ' . $description . " - [IP: {$_SERVER['REMOTE_ADDR']}]" . "\r\n";
        $log .= 'Info: ' . json_encode($info, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
        $log .= '==============================';

        $logger->info($log);
    }

    static function saveFullLog($description, $headers = null, $input = null, $response = null) {
        if (!empty(static::$logger)) {
            $logger = LoggerManager::getLogger(static::$logger);
        }
        else {
            $logger = LoggerManager::getLogger('WEBSERVICE');
        }

        // Save log
        $log = 'Description: ' . $description . " - [IP: {$_SERVER['REMOTE_ADDR']}]" . "\r\n";
        $log .= 'Headers: ' . json_encode($headers) . "\r\n";
        $log .= 'Body: ' . json_encode($input) . "\r\n";
        $log .= 'Response: ' . json_encode($response) . "\r\n";
        $log .= '==============================';

        $logger->info($log);
    }
}