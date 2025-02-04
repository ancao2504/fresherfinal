<?php

/*
	Preferences_Model
	Author: Hieu Nguyen
	Date: 2019-03-18
	Purpose: provide util functions to handle custom user preferences
*/

class Users_Preferences_Model extends Vtiger_Base_Model {

	static function savePreferences($userId, $category, $preferences) {
        global $adb;

        $sql = "SELECT 1 FROM vtiger_user_preferences WHERE user_id = ? AND category = ?";
        $params = [$userId, $category];
        $isExists = $adb->getOne($sql, $params);

        if($isExists) {
            $sql = "UPDATE vtiger_user_preferences SET value = ? WHERE user_id = ? AND category = ?";
            $params = [json_encode($preferences), $userId, $category];
        }
        else {
            $sql = "INSERT INTO vtiger_user_preferences(user_id, category, value) VALUES(?, ?, ?)";
            $params = [$userId, $category, json_encode($preferences)];
        }

        $adb->pquery($sql, $params);
    }

    static function loadPreferences($userId, $category, $toArray = false) {
        global $adb;

        $sql = "SELECT value FROM vtiger_user_preferences WHERE user_id = ? AND category = ?";
        $params = [$userId, $category];
        $preferences = $adb->getOne($sql, $params);

        return json_decode($preferences, $toArray);
    }
}