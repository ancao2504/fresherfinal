<?php

/**
 * Calendar Data Model
 * Author: Phu Vo
 * Date: 2020.08.14
 * Description: Provide utils to work with calendar data
 */

class Calendar_Data_Model {

    // Implemented by Hieu Nguyen on 2020-08-31 to render busy activity title
    static function getBusyTitle($feedUserId) {
        $feedUserName = trim(getOwnerName($feedUserId));
        $title = decodeUTF8($feedUserName) .' - '. decodeUTF8(vtranslate('Busy', 'Events')) .'*';
        return $title;
    }
	
	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function pullEvents($start, $end, &$result, $userId = false, $color = null, $textColor = 'white', $calendarView = 'MyCalendar', $conditions = '') {   // Changed isGroup into calendarView by Hieu Nguyen on 2019-11-12 to determine which view this function serve for
        $dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];

		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');

		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();
		require('user_privileges/user_privileges_'.$currentUser->id.'.php');
		require('user_privileges/sharing_privileges_'.$currentUser->id.'.php');

		$moduleModel = Vtiger_Module_Model::getInstance('Events');

        // Added by Hieu Nguyen on 2019-11-12 to set userId = current user's id in case the request sent from MyCalendar view
        $currentUserId = $currentUser->getId();
        if (empty($userId)) $userId = $currentUserId;
        // End Hieu Nguyen

        $params = [];
        $fields = array('subject', 'eventstatus', 'visibility','date_start','time_start','due_date','time_end','assigned_user_id','id','activitytype','recurringtype','assigned_user_id', 'main_owner_id', 'location', 'description', 'createdtime', 'modifiedtime');
        $query = self::generateQueryForCalendar($fields, 'Events', $calendarView, $params, $userId);

		if(!empty($conditions)) {
			$conditions = Zend_Json::decode(Zend_Json::decode($conditions));
			$query .=  ' AND' . self::generateCalendarViewConditionQuery($conditions);
        }

		$query .= " AND ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
        
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
            // Added by Hieu Nguyen on 2019-11-28 to skip unaccepted invited event for current user in My Calendar & Shared Calendar
            $recordVisible = self::isEventVisible($record, $calendarView, $userId, $currentUserId);
            if (!$recordVisible) continue;

            $recordBusy = self::isRecordBusy($record, $calendarView, $userId, $currentUserId);

			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$activitytype = $record['activitytype'];
			$status = $record['eventstatus'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			$item['activitytype'] = $activitytype;
			$item['status'] = $status;
			$item['assigned_user_id'] = $record['smownerid'];

            // Modified by Hieu Nguyen on 2019-11-08 to show more information in the event popover
            if ($recordBusy) {
                // Display event as busy when the event owner is not current user and the event visibility is Private
				$item['title'] = self::getBusyTitle($userId);
				$item['url'] = '';
				$item['busy'] = 1;
			} 
            else {
                // Public or shared events
				$item['title'] = decodeUTF8($record['subject']);
				$item['url'] = getRecordDetailUrl($crmid, 'Calendar');
			}

            if (!empty($record['main_owner_id'])) {
                $item['main_owner_name'] = decodeUTF8(trim(getOwnerName($record['main_owner_id'])));
            }

            $item['status_label'] = vtranslate($record['eventstatus'], 'Calendar');
            $item['location'] = decodeUTF8($record['location']);
			$item['description'] = $record['description']; // Added by Phu Vo on 2021.07.20
			$item['short_description'] = getShortString($record['description'], 100);
			$item['createdtime'] = $record['createdtime'];
			$item['modifiedtime'] = $record['modifiedtime'];
            // End Hieu Nguyen

			$dateTimeFieldInstance = new DateTimeField($record['date_start'].' '.$record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d.since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'].' '.$record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d.since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$item['module'] = $moduleModel->getName();
			$recurringCheck = false;
			if($record['recurringtype'] != '' && $record['recurringtype'] != '--None--') {
				$recurringCheck = true;
			}
			$item['recurringcheck'] = $recurringCheck;
			$item['userid'] = $userId;
			$item['fieldName'] = 'date_start,due_date';
			$item['conditions'] = '';
			if(!empty($conditions)) {
				$item['conditions'] = Zend_Json::encode(Zend_Json::encode($conditions));
			}

            // Modified by Hieu Nguyen on 2020-02-26 to unify the return format
			$result[$item['id']] = $item;
            // End Hieu Nguyen
		}
    }

	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function pullMultipleEvents($start, $end, &$result, $data) {

		foreach ($data as $id=>$backgroundColorAndTextColor) {
			$userEvents = array();
			$colorComponents = explode(',',$backgroundColorAndTextColor);
			self::pullEvents($start, $end, $userEvents ,$id, $colorComponents[0], $colorComponents[1], $colorComponents[2]);
			$result[$id] = $userEvents;
		}
	}

	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function pullTasks($start, $end, &$result, $color = null,$textColor = 'white', $calendarView = 'MyCalendar') {   // Added param calendarView by Hieu Nguyen on 2019-11-12 to determine which view this function serve for
		$user = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
        
        $params = [];
        $fields = array('activityid','subject', 'taskstatus','activitytype', 'date_start','time_start','due_date','time_end','id','assigned_user_id', 'main_owner_id', 'location', 'description', 'createdtime', 'modifiedtime');
        $query = self::generateQueryForCalendar($fields, 'Tasks', $calendarView, $params, $user);
        
		$query.= " AND ((date_start >= '$start' AND due_date < '$end') OR ( due_date >= '$start'))";


		$queryResult = $db->pquery($query,$params);

		while($record = $db->fetchByAssoc($queryResult)){
			$record = decodeUTF8($record); // Added by Phu Vo on 2021.07.21 to decode query result
			$item = array();
			$crmid = $record['activityid'];
			$item['title'] = decode_html($record['subject']).' - ('.decode_html(vtranslate($record['status'],'Calendar')).')';
			$item['status'] = $record['status'];
			$item['activitytype'] = $record['activitytype'];
			$item['id'] = $crmid;
			$dateTimeFieldInstance = new DateTimeField($record['date_start'].' '.$record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d.since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['end']   = $record['due_date'];
			$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$item['module'] = $moduleModel->getName();
			$item['allDay'] = true;
			$item['fieldName'] = 'date_start,due_date';
			$item['conditions'] = '';
			$item['assigned_user_id'] = $record['smownerid'];

            // Modified by Hieu Nguyen on 2019-11-08 to show more information in the event popover
            if (!empty($record['main_owner_id'])) {
                $item['main_owner_name'] = decodeUTF8(trim(getOwnerName($record['main_owner_id'])));
            }

            $item['status_label'] = vtranslate($record['status'], 'Calendar');
            $item['location'] = decodeUTF8($record['location']);
			$item['description'] = $record['description']; // Added by Phu Vo on 2021.07.20
			$item['short_description'] = getShortString($record['description'], 100);
			$item['createdtime'] = $record['createdtime'];
            // End Hieu Nguyen

			$result[$item['id']] = $item;   // Modified by Hieu Nguyen on 2019-11-14 to unify the return format
		}
    }

	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function pullDetails($start, $end, &$result, $type, $fieldName, $color = null, $textColor = 'white', $conditions = '') {
		$moduleModel = Vtiger_Module_Model::getInstance($type);
		$nameFields = $moduleModel->getNameFields();
		foreach($nameFields as $i => $nameField) {
			$fieldInstance = $moduleModel->getField($nameField);
			if(!$fieldInstance->isViewable()) {
				unset($nameFields[$i]);
			}
		}
		$nameFields = array_values($nameFields);
		$selectFields = implode(',', $nameFields);		
		$fieldsList = explode(',', $fieldName);
		if(count($fieldsList) == 2) {
			$db = PearDatabase::getInstance();
			$user = Users_Record_Model::getCurrentUserModel();
			$userAndGroupIds = array_merge(array($user->getId()),self::getGroupsIdsForUsers($user->getId()));
			$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);
			$meta = $queryGenerator->getMeta($moduleModel->get('name'));

            // Added main_owner_id and description into the display field list by Hieu Nguyen on 2019-11-15
            $fieldsList = array_merge($fieldsList, ['main_owner_id', 'description']);
            // End Hieu Nguyen

			$queryGenerator->setFields(array_merge(array_merge($nameFields, array('id')), $fieldsList));
			$query = $queryGenerator->getQuery();
			$query.= " AND (($fieldsList[0] >= ? AND $fieldsList[1] < ?) OR ($fieldsList[1] >= ?)) ";
			$params = array($start,$end,$start);
			$query.= " AND vtiger_crmentity.smownerid IN (".generateQuestionMarks($userAndGroupIds).")";
			$params = array_merge($params, $userAndGroupIds);
			$queryResult = $db->pquery($query, $params);

			$records = array();
			while($rowData = $db->fetch_array($queryResult)) {
				$records[] = DataTransform::sanitizeDataWithColumn($rowData, $meta);
			}
		} else {
			if($fieldName == 'birthday') {
				$startDateComponents = split('-', $start);
				$endDateComponents = split('-', $end);

				$year = $startDateComponents[0];
				$db = PearDatabase::getInstance();
				$user = Users_Record_Model::getCurrentUserModel();
				$userAndGroupIds = array_merge(array($user->getId()),self::getGroupsIdsForUsers($user->getId()));
				$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);
				$meta = $queryGenerator->getMeta($moduleModel->get('name'));

                // Added main_owner_id and description into the display field list by Hieu Nguyen on 2019-11-15
                $fieldsList = array_merge($fieldsList, ['main_owner_id', 'description']);
                // End Hieu Nguyen

				$queryGenerator->setFields(array_merge(array_merge($nameFields, array('id')), $fieldsList));
				$query = $queryGenerator->getQuery();
				$query.= " AND ((CONCAT('$year-', date_format(birthday,'%m-%d')) >= ? AND CONCAT('$year-', date_format(birthday,'%m-%d')) <= ? )";
				$params = array($start,$end);
				$endDateYear = $endDateComponents[0]; 
				if ($year !== $endDateYear) {
					$query .= " OR (CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) >= ?  AND CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) <= ? )"; 
					$params = array_merge($params,array($start,$end));
				} 
				$query .= ")";
				$query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($userAndGroupIds).")";
				$params = array_merge($params,$userAndGroupIds);
				$queryResult = $db->pquery($query, $params);
				$records = array();
				while($rowData = $db->fetch_array($queryResult)) {
					$records[] = DataTransform::sanitizeDataWithColumn($rowData, $meta);
				}
			} else {
                // Modified query by Hieu Nguyen on 2019-11-15 to get main_owner_id and description
                $additionFields = array_merge($fieldsList, ['main_owner_id']);
                
                if ($moduleModel->getField('description')) {
                    $additionFields[] = 'description';  // Not all modules have this field
                }

				$query = "SELECT {$selectFields}, ". join(', ', $additionFields) ." FROM {$type}";
                // End Hieu Nguyen

				$query.= " WHERE $fieldsList[0] >= '$start' AND $fieldsList[0] <= '$end' ";


				if(!empty($conditions)) {
					$conditions = Zend_Json::decode(Zend_Json::decode($conditions));
					$query .=  'AND '. self::generateCalendarViewConditionQuery($conditions);
				}

				if($type == 'PriceBooks') {
					$records = self::queryForRecords($query, false);
				} else {
					$records = self::queryForRecords($query);
				}
			}
		}
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record[$nameFields[0]]);
			if(count($nameFields) > 1) {
				$item['title'] = decode_html(trim($record[$nameFields[0]].' '.$record[$nameFields[1]]));
			}
			if(!empty($record[$fieldsList[0]])) {
				$item['start'] = $record[$fieldsList[0]];
			} else {
				$item['start'] = $record[$fieldsList[1]];
			}
			if(count($fieldsList) == 2) {
				$item['end'] = $record[$fieldsList[1]];
			}
			if($fieldName == 'birthday') {
				$recordDateTime = new DateTime($record[$fieldName]); 

				$calendarYear = $year; 
				if($recordDateTime->format('m') < $startDateComponents[1]) { 
						$calendarYear = $endDateYear; 
				} 
				$recordDateTime->setDate($calendarYear, $recordDateTime->format('m'), $recordDateTime->format('d'));
				$item['start'] = $recordDateTime->format('Y-m-d');
			}

			$urlModule = $type;
			if ($urlModule === 'Events') {
				$urlModule = 'Calendar';
			}
			$item['url']   = sprintf('index.php?module='.$urlModule.'&view=Detail&record=%s', $crmid);
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$item['module'] = $moduleModel->getName();
			$item['sourceModule'] = $moduleModel->getName();
			$item['fieldName'] = $fieldName;
			$item['conditions'] = '';
			if(!empty($conditions)) {
				$item['conditions'] = Zend_Json::encode(Zend_Json::encode($conditions));
			}

            // Added by Hieu Nguyen on 2019-11-15 to show more info in event popover
            if (!empty($record['main_owner_id'])) {
                $ownerId = end(explode('x', $record['main_owner_id']));
                $item['main_owner_name'] = decodeUTF8(trim(getOwnerName($ownerId)));
            }

			$item['description'] = $record['description']; // Added by Phu Vo on 2021.07.20
            $item['short_description'] = getShortString($record['description'], 100);
            // End Hieu Nguyen

			$result[$item['id']] = $item;   // Modified by Hieu Nguyen on 2019-11-14 to unify the return format
		}
	}

	/** Implemented by Phu Vo on 2020.08.17 */
    static function pullCalendarEventDates(&$result, $view, $userId, $type, $startDate = null, $endDate = null) {
        global $adb, $current_user;

        $params = [];
        $currentUserId = $current_user->id;
        $query = Calendar_Data_Model::generateQueryForCalendar(['date_start'], $type, $view, $params, $userId);

		if (!empty($startDate)) {
			$query .= ' AND DATE(vtiger_activity.date_start) >= ? ';
			$params[] = $startDate;
		}

		if (!empty($endDate)) {
			$query .= ' AND DATE(vtiger_activity.date_start) <= ? ';
			$params[] = $endDate;
		}

        $queryResult = $adb->pquery($query, $params);

        if (empty($result)) $result = [];

        while ($record = $adb->fetchByAssoc($queryResult)) {
            $record = decodeUTF8($record);

            if ($type == 'Events' && !Calendar_Data_Model::isEventVisible($record, $view, $userId, $currentUserId)) {
                continue;
            }

            if (!in_array($record['date_start'], $result)) $result[] = $record['date_start'];
        }
    }
	
	/** Implemented by Phu Vo on 2020.08.17 */
    static function generateQueryForCalendar($fields, $type = 'Events', $view = '', &$params = [], $userId = null) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        if (empty($userId)) $userId = $currentUser->getId();

        $moduleName = 'Calendar';
        $hideCompleted = $currentUser->get('hidecompletedevents');
        $fields = array_merge(['id', 'assigned_user_id', 'main_owner_id'], $fields);

        $queryGenerator = new QueryGenerator($moduleName, $currentUser);
        $queryGenerator->setFields($fields);

        $query = $queryGenerator->getQuery();

        if ($type == 'Events') {
            $query .= " AND vtiger_activity.activitytype NOT IN ('Emails', 'Task')";

            $query .= " AND (vtiger_crmentity.main_owner_id = ? OR ? IN (SELECT inviteeid FROM vtiger_invitees WHERE invitee_type = 'Users' AND activityid = vtiger_activity.activityid))";
            $params[] = $userId;
            $params[] = $userId;

            if ($hideCompleted) $query .= " AND vtiger_activity.eventstatus <> 'HELD'";

            if ($view == 'SharedCalendar') {
                $selectedActivityTypes = explode(',', $currentUser->get('shared_calendar_activity_types'));
                $query .= " AND vtiger_activity.activitytype IN ('" . join("', '", $selectedActivityTypes) . "')";
            }
        }
        else if ($type == 'Tasks') {
            $query .= " AND vtiger_activity.activitytype = 'Task'";

            if ($hideCompleted) $query .= " AND vtiger_activity.status <> 'Completed'";

            if ($view == 'MyCalendar') {
                $indirectlySharedUserIds = Calendar_MyCalendar_Model::getIndirectlySharedUserIds();
                $query .= " AND vtiger_crmentity.main_owner_id NOT IN ('" . join("', '", $indirectlySharedUserIds) . "')";
            }
        }

        return $query;
    }

    // Renamed this function by Hieu Nguyen on 2020-08-28 to make it more understandable 
    static function isEventVisible($record, $view, $userId, $currentUserId) {
        $eventOwnerUserId = $record['main_owner_id'];
        $feedUserIsCurrentUser = ($userId == $currentUserId);
        $currentUserIsEventOwner = ($currentUserId == $eventOwnerUserId);

        // Added by Hieu Nguyen on 2019-11-28 to skip unaccepted invited event for current user in My Calendar & Shared Calendar
        if (
            ($view == 'MyCalendar' || ($view == 'SharedCalendar' AND $feedUserIsCurrentUser))
            && !$currentUserIsEventOwner
            && Events_Invitation_Helper::isInvitee($currentUserId, 'Users', $record['activityid']) 
            && !Events_Invitation_Helper::isInvitationAccepted($currentUserId, 'Users', $record['activityid'])
        ) {
            return false;
        }

        // Added by Hieu Nguyen on 2020-02-24 to skip event that current user is not the owner and not the invitee in My Calendar & Shared Calendar
        if (
            ($view == 'MyCalendar' || ($view == 'SharedCalendar' AND $feedUserIsCurrentUser))
            && !$currentUserIsEventOwner 
            && !Events_Invitation_Helper::isInvitee($currentUserId, 'Users', $record['activityid'])
        ) {
            return false;
        }

        // Added by Hieu Nguyen on 2020-02-25 to skip event that the feed user not accepted the invitation in Shared Calendar
        if (
            $view == 'SharedCalendar' AND !$feedUserIsCurrentUser
            && Events_Invitation_Helper::isInvitee($userId, 'Users', $record['activityid'])
            && !Events_Invitation_Helper::isInvitationAccepted($userId, 'Users', $record['activityid'])
        ) {
            return false;
        }

        return true;
    }

    // Moved this logic here by Hieu Nguyen on 2020-08-28 to make it reusable
    static function isRecordBusy($record, $calendarView, $userId, $currentUserId) {
        $eventOwnerUserId = $record['main_owner_id'];
        $feedUserIsCurrentUser = ($userId == $currentUserId);
        $feedUserIsEventOwner = ($userId == $eventOwnerUserId);
        $currentUserIsEventOwner = ($currentUserId == $eventOwnerUserId);

        // Modified by Hieu Nguyen on 2020-02-25 to check case feed user is not the event owner but accepted event invitation (this is the most complicated rule of this module!!!)
        if (
            $calendarView == 'SharedCalendar' AND !$feedUserIsCurrentUser && !$feedUserIsEventOwner
            && Events_Invitation_Helper::isInvitee($userId, 'Users', $record['activityid']) 
            && Events_Invitation_Helper::isInvitationAccepted($userId, 'Users', $record['activityid'])
        ) {
            $eventOwnerCalendarSharedType = Calendar_Module_Model::getSharedType($eventOwnerUserId);

            if ($eventOwnerCalendarSharedType == 'selectedusers') {
                if (Calendar_SharedCalendar_Model::isCurrentUserInSelectedUsers($eventOwnerUserId)) {
                    $feedUserCalendarSharedType = Calendar_Module_Model::getSharedType($userId);
                    
                    if ($feedUserCalendarSharedType == 'selectedusers') {
                        if ($record['visibility'] == 'Private') {
                            return true;    // Display event as busy as the event sharing mode is Private
                        }
                    }
                }
                else {
                    return true;    // Display event as busy as the event owner is not shared calendar to current user
                }
            }
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2020-03-05 to check case feed user is not the current user but is the event owner and set his calendar sharing mode to Private
        if (
            !$feedUserIsCurrentUser && $feedUserIsEventOwner && !$currentUserIsEventOwner 
            && Calendar_Module_Model::getSharedType($eventOwnerUserId) == 'private'
        ) {
            if ($record['visibility'] == 'Private') {
                return true;    // Display event as busy as the event sharing mode is Private
            }
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2020-03-05 to check case feed user is not the current user but is the event owner and set his calendar sharing mode to Selected users
        if (
            !$feedUserIsCurrentUser && $feedUserIsEventOwner && !$currentUserIsEventOwner 
            && Calendar_Module_Model::getSharedType($eventOwnerUserId) == 'selectedusers'
        ) {
            if ($record['visibility'] == 'Public') {
                if (!Calendar_SharedCalendar_Model::isCurrentUserInSelectedUsers($eventOwnerUserId)) {
                    return true;    // Display event as busy as current user is not in Selected Users of event owner's calendar sharing mode
                }
            }
            else {
                return true;    // Display event as busy as the event sharing mode is Private
            }
        }
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2020-02-26 to check case feed user is not the current user and not the event owner but the event sharing mode is Private
        if (!$feedUserIsCurrentUser && !$currentUserIsEventOwner && $record['visibility'] == 'Private') {
            return true;
        }
        // End Hieu Nguyen

        return false;
    }

	static function generateCalendarViewConditionQuery($conditions) {
		$conditionQuery = $operator = '';
		switch ($conditions['operator']) {
			case 'e' : $operator = '=';
		}

		if(!empty($operator) && !empty($conditions['fieldname']) && !empty($conditions['value'])) {
			$conditionQuery = ' '.$conditions['fieldname'].$operator.'\'' .$conditions['value'].'\' ';
		}
		return $conditionQuery;
    }

	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function getGroupsIdsForUsers($userId) {
		vimport('~~/include/utils/GetUserGroups.php');

		$userGroupInstance = new GetUserGroups();
		$userGroupInstance->getAllUserGroups($userId);
		return $userGroupInstance->user_groups;
	}

	/** Move from Calendar Feed by Phu Vo on 2020.08.17 */
	static function queryForRecords($query, $onlymine=true) {
		$user = Users_Record_Model::getCurrentUserModel();
		if ($onlymine) {
			$groupIds = self::getGroupsIdsForUsers($user->getId());
			$groupWsIds = array();
			foreach($groupIds as $groupId) {
				$groupWsIds[] = vtws_getWebserviceEntityId('Groups', $groupId);
			}
			$userwsid = vtws_getWebserviceEntityId('Users', $user->getId());
			$userAndGroupIds = array_merge(array($userwsid),$groupWsIds);
			$query .= " AND assigned_user_id IN ('".implode("','",$userAndGroupIds)."')";
		}
		// TODO take care of pulling 100+ records
		return vtws_query($query.';', $user);
	}

	/** Implemented by Phu Vo on 2020.08.31 */
    static function getDisplayConfigForActivitiesRelatedList() {
		$configs = Settings_Vtiger_Config_Model::loadConfig('related_activities_config', true);

        return $configs;
    }
}