<?php

/*
    Shared Calendar Model
    Author: Hieu Nguyen
    Date: 2019-10-30
    Purpose: provide util function to manipulate data for share calendar
*/

class Calendar_SharedCalendar_Model extends Vtiger_Base_Model {

    const CURRENT_USER_FEED_COLOR = '#207bad';

    // This function return data in select2 format
    static function getAvailableUserFeedList($keyword) {
        global $adb, $current_user;

        $sql = "SELECT DISTINCT id, first_name, last_name
            FROM vtiger_users
            WHERE deleted = 0 AND id != {$current_user->id} 
                AND TRIM(CONCAT_WS('' , COALESCE(last_name, ''), COALESCE(first_name))) LIKE ? 
                AND id NOT IN (SELECT shareduserid FROM vtiger_shareduserinfo WHERE userid = {$current_user->id})";
        $params = ["%{$keyword}%"];
        $result = $adb->pquery($sql, $params);
        $userFeedList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['text'] = html_entity_decode(getFullNameFromArray('Users', $row));
            $userFeedList[] = $row;
        }

        return $userFeedList;
    }

    // Check if the calendar host user added current user as one of selected users in his Calendar Sharing settings
    static function isCurrentUserInSelectedUsers($calendarHostUserId) {
        global $adb, $current_user;

        $sqlCheckCalendarShared = "SELECT 1 FROM vtiger_sharedcalendar WHERE userid = ? AND sharedid = ?";
        $result = $adb->getOne($sqlCheckCalendarShared, [$calendarHostUserId, $current_user->id]);

        return !empty($result);
    }

    static function getUserFeedAccessibleStatus($userFeedId) {
        global $adb, $current_user;

        // Check admin user
        /*if (is_admin($current_user)) {    // Admin is considered the same as normal user in Calendar views. He can check all other user's activities in Calendar ListView!
            return true;    // Admin can access all personal calendar
        }*/

        // Check calendar share type
        $sqlGetCalendarSharedType = "SELECT calendarsharedtype FROM vtiger_users WHERE id = ?";
        $sharedType = $adb->getOne($sqlGetCalendarSharedType, [$userFeedId]);

        if ($sharedType == 'private') {
            return 'USER_CALENDAR_IS_PRIVATE';
        }

        // Check calendar shared users
        if ($sharedType == 'selectedusers') {
            $isInSharedUsers = self::isCurrentUserInSelectedUsers($userFeedId);

            if (!$isInSharedUsers) {
                return 'USER_CALENDAR_NOT_SHARED_TO_CURRENT_USER';
            }
        }

        return true;
    }

    static function saveUserFeed($userFeedId, $color, $visible = '1') {
        global $adb, $current_user;
		
        $sqlInsert = "INSERT INTO vtiger_shareduserinfo (userid, shareduserid, color, visible) VALUES(?, ?, ?, ?)";
        $params = [$current_user->id, $userFeedId, $color, $visible];
		$result = $adb->pquery($sqlInsert, $params);
		
		if (empty($result)) {
            $sqlUpdate = "UPDATE vtiger_shareduserinfo SET color = ? WHERE userid = ? AND shareduserid = ?";
            $params = [$color, $current_user->id, $userFeedId];
			$adb->pquery($sqlUpdate, $params);
		}
    }

    static function updateUserFeedVisibility($userFeedId, $visible = '1') {
        global $adb, $current_user;

        // Update all
        if ($userFeedId == 'all') {
            $sql = "UPDATE vtiger_shareduserinfo SET visible = ? WHERE userid = ?";
            $params = [$visible, $current_user->id];
            $adb->pquery($sql, $params);
            return;
        }

        // Current user is a special case. The saved feed info may not exist so we have to double check and create it if needed
        if ($userFeedId == $current_user->id) {
            $savedFeedInfo = self::getSavedUserFeedInfo($userFeedId);

            if (empty($savedFeedInfo)) {
                self::saveUserFeed($userFeedId, self::CURRENT_USER_FEED_COLOR, $visible);
                return;
            }
        }

        // Update visibility status for a specific feed
        $sql = "UPDATE vtiger_shareduserinfo SET visible = ? WHERE userid = ? AND shareduserid = ?";
        $params = [$visible, $current_user->id, $userFeedId];
        $adb->pquery($sql, $params);
    }

    static function getCurrentUserFeedInfo() {
        global $current_user;
        $savedFeedInfo = self::getSavedUserFeedInfo($current_user->id);

        if (empty($savedFeedInfo)) {
            $defaultFeedInfo = [
                'id' => $current_user->id,
                'first_name' => $current_user->first_name,
                'last_name' => $current_user->last_name,
                'color' => self::CURRENT_USER_FEED_COLOR,
                'visible' => '1'
            ];

            return $defaultFeedInfo;
        }
        
        return $savedFeedInfo;
    }

    static function getSavedUserFeedInfo($userFeedId) {
        global $adb, $current_user;

        $sql = "SELECT u.id, u.first_name, u.last_name, su.color, su.visible
            FROM vtiger_users AS u 
            INNER JOIN vtiger_shareduserinfo AS su ON (su.shareduserid = u.id AND su.userid = ? AND su.shareduserid = ?)
            WHERE u.deleted = 0";
        $params = [$current_user->id, $userFeedId];
        $result = $adb->pquery($sql, $params);
        $userFeedInfo = $adb->fetchByAssoc($result);

        if (!empty($userFeedInfo)) {
            $userFeedInfo['name'] = html_entity_decode(getFullNameFromArray('Users', $userFeedInfo));
            return $userFeedInfo;
        }

        return [];
    }

    static function getSavedUserFeedList() {
        global $adb, $current_user;
        $currentUserId = $current_user->id;

        $sql = "SELECT u.id, u.first_name, u.last_name, su.color, su.visible
            FROM vtiger_users AS u 
            INNER JOIN vtiger_shareduserinfo AS su ON (su.shareduserid = u.id AND su.userid = ?)
            WHERE u.deleted = 0 AND u.id != ?";
        $params = [$currentUserId, $currentUserId];
        $result = $adb->pquery($sql, $params);
        $userFeedList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['name'] = html_entity_decode(getFullNameFromArray('Users', $row));
            $userFeedList[] = $row;
        }

        return $userFeedList;
    }

    static function deleteUserFeed($userFeedId) {
        global $adb, $current_user;

        // Delete all
        if ($userFeedId == 'all') {
            $sql = "DELETE FROM vtiger_shareduserinfo WHERE userid = ? AND shareduserid != ?";
            $params = [$current_user->id, $current_user->id];
            $adb->pquery($sql, $params);
            return;
        }

        // Delete a specific feed
        $sql = "DELETE FROM vtiger_shareduserinfo WHERE userid = ? AND shareduserid = ?";
        $adb->pquery($sql, [$current_user->id, $userFeedId]);
    }
}