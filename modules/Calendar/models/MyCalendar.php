<?php

/*
    My Calendar Model
    Author: Hieu Nguyen
    Date: 2019-12-19
    Purpose: provide util function to manipulate data for my calendar
*/

class Calendar_MyCalendar_Model extends Vtiger_Base_Model {

    /**
	* To get the lists of sharedids and colors
	* @param $id --  user id
	* @returns <Array> $sharedUsers
	*/
    // Moved here from Module Model by Hieu Nguyen on 2019-12-19
	public static function getCalendarViewTypes($id){
		$db = PearDatabase::getInstance();

        // Modified query by Hieu Nguyen on 2019-12-18 to get calendar view id
		$query = "SELECT uat.id AS calendar_view_id, uat.*, dat.* 
            FROM vtiger_calendar_user_activitytypes AS uat
			INNER JOIN vtiger_calendar_default_activitytypes AS dat ON (dat.id = uat.defaultid)
			WHERE uat.userid = ?";
        // End Hieu Nguyen

		$result = $db->pquery($query, array($id));
		$rows = $db->num_rows($result);

		$calendarViewTypes = Array();
		for($i=0; $i<$rows; $i++){
			$activityTypes = $db->query_result_rowdata($result, $i);
			$moduleInstance = Vtiger_Module_Model::getInstance($activityTypes['module']);
			//If there is no module view permission, should not show in calendar view
			if($moduleInstance === false || !$moduleInstance->isPermitted('Detail')) {
				continue;
			}
			$type = '';
			if(in_array($activityTypes['module'], array('Events','Calendar')) && $activityTypes['isdefault']) {
				$type = $activityTypes['module'].'_'.$activityTypes['isdefault'];
			}
			$fieldNamesList = Zend_Json::decode(html_entity_decode($activityTypes['fieldname']));
			$fieldLabelsList = array();
			foreach ($fieldNamesList as $fieldName) {
				$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);
				if ($fieldInstance) {
					//If there is no field view permission, should not show in calendar view
					if (!$type && !$fieldInstance->isViewableInDetailView()) {
						$fieldLabelsList = array();
						break;
					}
					$fieldLabelsList[$fieldName] = $fieldInstance->label;
				}
			}

			$conditionsName = '';
			if (!empty($activityTypes['conditions'])) {
				$conditions = Zend_Json::decode(decode_html($activityTypes['conditions']));
				$conditions = Zend_Json::decode($conditions);
				$conditionsName = $conditions['value'];
			}

            // Modified by Hieu Nguyen on 2019-12-18 to identify calendar view by its id 
			$fieldInfo = [
                'id' => $activityTypes['calendar_view_id'],
                'module' => $activityTypes['module'],
                'fieldname' => implode(',', array_keys($fieldLabelsList)),
                'fieldlabel'=> self::translateCaledarViewTypeLabels($fieldLabelsList, $activityTypes['module']), // [Calendar] Request #421: Modified by Phu Vo on 2020.03.16
                'visible' => $activityTypes['visible'],
                'color' => $activityTypes['color'],
                'type' => $type,
                'conditions' => [
                    'name' => $conditionsName,
                    'rules' => $activityTypes['conditions']
                ]
            ];
            // End Hieu Nguyen

            // Modified by Hieu Nguyen on 2019-11-14 to load all saved calendar views
			$calendarViewTypes[] = $fieldInfo;
            // End Hieu Nguyen
		}
		return $calendarViewTypes;
	}

    // Moved here from Module Model by Hieu Nguyen on 2019-12-19
    function getCalendarViewTypesToAdd($userId) {
		$calendarViewTypes = self::getCalendarViewTypes($userId);
		$moduleViewTypes = Calendar_Module_Model::getDateFieldModulesList();    // Modified by Hieu Nguyen on 2019-12-19 to call this function from Module Model

		$visibleList = $calendarViewTypes;  // Modified by Hieu Nguyen on 2019-11-14 to load correct available calendar views
		if(is_array($visibleList)) {
			foreach($visibleList as $list) {
				$fieldsListArray = $moduleViewTypes[$list['module']];
				if(count($fieldsListArray) == 1) {
					if($list['module'] !== 'Events') {
						unset($fieldsListArray[$list['fieldname']]);
					}
				}
				if(!empty($fieldsListArray)) {
					$moduleViewTypes[$list['module']] = $fieldsListArray;
				} else {
					unset($moduleViewTypes[$list['module']]);
				}
			}
		}
		return $moduleViewTypes;
	}

    // Moved here from Module Model and renamed from getVisibleCalendarViewTypes to getCalendarViewTypesForEdit by Hieu Nguyen on 2019-12-19
    function getCalendarViewTypesForEdit($userId) {
		$db = PearDatabase::getInstance();

        // Modified by Hieu Nguyen on 2019-12-19 to get all existing calendar views
		$query = "SELECT * FROM vtiger_calendar_user_activitytypes 
			INNER JOIN vtiger_calendar_default_activitytypes ON (vtiger_calendar_default_activitytypes.id = vtiger_calendar_user_activitytypes.defaultid)
			WHERE vtiger_calendar_user_activitytypes.userid = ?";
        $result = $db->pquery($query, [$userId]);
        // End Hieu Nguyen

		$rows = $db->num_rows($result);

		$calendarViewTypes = Array();
		for($i=0; $i<$rows; $i++) {
			$activityTypes = $db->query_result_rowdata($result, $i);
			$moduleInstance = Vtiger_Module_Model::getInstance($activityTypes['module']);
			//If there is no module view permission, should not show in calendar view
			if(!$moduleInstance->isPermitted('Detail')) {
				continue;
			}

			$fieldNamesList = Zend_Json::decode(html_entity_decode($activityTypes['fieldname']));
			$fieldLabelsList = array();
			foreach ($fieldNamesList as $fieldName) {
				$fieldInstance = Vtiger_Field_Model::getInstance($fieldName, $moduleInstance);
				if ($fieldInstance) {
					//If there is no field view permission, should not show in calendar view
					if (!$fieldInstance->isViewableInDetailView()) {
						$fieldLabelsList = array();
						break;
					}
					$fieldLabelsList[$fieldName] = vtranslate($fieldInstance->label, $activityTypes['module']); // Modified by Hieu Nguyen on 2020-03-26 to get translated labels
				}
			}
			if(!empty($fieldLabelsList)) {
				$calendarViewTypes[$activityTypes['module']][implode(',', array_keys($fieldLabelsList))] = implode(',' , $fieldLabelsList);
			}
		}
		return $calendarViewTypes;
	}

    /**
	 *  Function to check duplicate activity view while adding
	 * @return <boolean>
	 */
    // Moved here from Module Model by Hieu Nguyen on 2019-12-19
	public function checkDuplicateView(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$userId = $currentUser->getId();
		$viewmodule = $request->get('viewmodule');
		$fieldName = $request->get('viewfieldname');
		$conditions = $request->get('viewConditions','');
		$viewfieldname = Array();
		$viewfieldname = Zend_Json::encode(explode(',',$fieldName));
		$db = PearDatabase::getInstance();

		$queryResult = $db->pquery('SELECT id FROM vtiger_calendar_default_activitytypes WHERE module=? AND fieldname=? AND conditions=?', array($viewmodule, $viewfieldname,$conditions));
		if($db->num_rows($queryResult) > 0) {
			$defaultId = $db->query_result($queryResult, 0, 'id');

			$query = $db->pquery('SELECT 1 FROM vtiger_calendar_user_activitytypes WHERE defaultid=? AND userid=? AND visible=?', array($defaultId, $userId, '1'));
			if($db->num_rows($query) > 0) {
				return true;
			}
		}
		return false;
	}

    /**
	 *  Function to get all calendar view conditions
	 * @return <string>
	 */
    // Moved here from Module Model by Hieu Nguyen on 2019-12-19
	public function getCalendarViewConditions() {
		$eventsModuleModel = Vtiger_Module_Model::getInstance('Events');
		$eventTypePicklistValues = $eventsModuleModel->getField('activitytype')->getPicklistValues();
		$eventsModuleConditions = array();

		foreach($eventTypePicklistValues as $picklistValue=>$picklistLabel) {
			$eventsModuleConditions[$picklistLabel] = array('fieldname' => 'activitytype','operator' => 'e','value'=>$picklistValue);
		}

		$conditions = array(
			'Events' => $eventsModuleConditions
		);

		return $conditions;
	}

    public function saveCalendarView(Vtiger_Request $request) {
        global $adb, $current_user;
		$userId = $current_user->id;
		$viewModule = $request->get('viewmodule');
		$fieldName = $request->get('viewfieldname');
		$viewColor = $request->get('viewColor');
		$viewConditions = $request->get('viewConditions', '');
		$viewFieldName = [];
		$viewFieldName = Zend_Json::encode(explode(',', $fieldName));
		$calendarViewId = $request->get('calendar_view_id');
        $type = '';

        // Check for calendar view heading
        $sqlCheckHeading = "SELECT id, isdefault FROM vtiger_calendar_default_activitytypes WHERE module = ? AND fieldname = ? AND conditions = ?";
		$queryResult = $adb->pquery($sqlCheckHeading, [$viewModule, $viewFieldName, $viewConditions]);
		
        // Heading exist
		if($adb->num_rows($queryResult) > 0) {
			$defaultId = $adb->query_result($queryResult, 0, 'id');
			$isDefault = $adb->query_result($queryResult, 0, 'isdefault');

			if (in_array($viewModule, ['Events', 'Calendar']) && $isDefault) {
				$type = $viewModule .'_'. $isDefault;
			}

            // Check for calendar view
			$calendarViewExist = $adb->getOne("SELECT 1 FROM vtiger_calendar_user_activitytypes WHERE userid = ? AND id = ?", [$userId, $calendarViewId]);
			
            // Calendar view exist, update the color
            if ($calendarViewExist) {
				$adb->pquery("UPDATE vtiger_calendar_user_activitytypes SET color = ? WHERE userid = ? AND id = ?", [$viewColor, $userId, $calendarViewId]);
			}
            // Calendar view not exist, insert a new one 
            else {
                $calendarViewId = $adb->getUniqueID('vtiger_calendar_user_activitytypes');
                $sqlInsertCalendarView = "INSERT INTO vtiger_calendar_user_activitytypes (id, defaultid, userid, color) VALUES (?, ?, ?, ?)";
				$params = [$calendarViewId, $defaultId, $userId, $viewColor];
			    $adb->pquery($sqlInsertCalendarView, $params);
			}
		}
        // Heading not exist 
        else {
            // Insert a new heading
			$defaultId = $adb->getUniqueID('vtiger_calendar_default_activitytypes');
            $sqlInsertHeading = "INSERT INTO vtiger_calendar_default_activitytypes (id, module, fieldname, defaultcolor, isdefault, conditions) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$defaultId, $viewModule, $viewFieldName, $viewColor, '0', $viewConditions];
			$adb->pquery($sqlInsertHeading, $params);

            // Then insert the new calendar view
            $calendarViewId = $adb->getUniqueID('vtiger_calendar_user_activitytypes');
            $sqlInsertCalendarView = "INSERT INTO vtiger_calendar_user_activitytypes (id, defaultid, userid, color) VALUES (?, ?, ?, ?)";
            $params = [$calendarViewId, $defaultId, $userId, $viewColor];
			$adb->pquery($sqlInsertCalendarView, $params);
		}

		return ['type' => $type, 'id' => $calendarViewId];
	}

    public function updateCalendarViewVisibility($calendarViewId, $visible = '1') {
        global $adb, $current_user;

        // Update all
        if ($calendarViewId == 'all') {
            $sql = "UPDATE vtiger_calendar_user_activitytypes SET visible = ? WHERE userid = ?";
            $params = [$visible, $current_user->id];
            $adb->pquery($sql, $params);
            return;
        }

        // Update visibility status for a specific feed
        $sql = "UPDATE vtiger_calendar_user_activitytypes SET visible = ? WHERE userid = ? AND id = ?";
        $params = [$visible, $current_user->id, $calendarViewId];
        $adb->pquery($sql, $params);
    }

	public function deleteCalendarView($calendarViewId) {
        global $adb, $current_user;

        // Delete all
        if ($calendarViewId == 'all') {
            $sql = "DELETE FROM vtiger_calendar_user_activitytypes WHERE userid = ?";
            $adb->pquery($sql, [$current_user->id]);
            return;
        }

        // Delete a specific calendar view
        $sql = "DELETE FROM vtiger_calendar_user_activitytypes WHERE userid = ? AND id = ?";
        $params = [$current_user->id, $calendarViewId];
        $adb->pquery($sql, $params);
	}

    // Get all user ids that not directly shared calendar to current user
    public function getIndirectlySharedUserIds() {
        global $adb, $current_user;
        $currentUserId = $current_user->id;

        $sql = "SELECT id FROM vtiger_users 
            WHERE deleted = 0 AND id != {$currentUserId} AND calendarsharedtype IN ('public', 'private')

            UNION ALL
            
            SELECT id FROM vtiger_users 
            WHERE deleted = 0 AND id != {$currentUserId} AND calendarsharedtype = 'selectedusers' 
                AND id NOT IN (SELECT userid FROM vtiger_sharedcalendar WHERE sharedid = {$currentUserId})";
        $result = $adb->pquery($sql, []);
        $userIds = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $userIds[] = $row['id'];
        }

        return $userIds;
	}
	
	// [Calendar] Request #421 Added by Phu Vo on 2020.03.19
	static function translateCaledarViewTypeLabels($fieldLabelsList, $moduleName) {
		foreach ($fieldLabelsList as $fieldName => $fieldLabel) {
			$fieldLabelsList[$fieldName] = vtranslate($fieldLabel, $moduleName);
		}

		return implode(',', $fieldLabelsList);
	}
}