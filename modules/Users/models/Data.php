<?php

/**
 * Name: Data.php
 * Author: Phu Vo
 * Date: 2021.08.10
 */

class Users_Data_Model {
    
    static function updateUserOnlineStatus($userId, $isOnline) {
        global $adb;

        $isOnline = $isOnline == true ? 1 : 0;

        $sql = "UPDATE vtiger_users SET is_online = ? WHERE id = ?";
        $queryParams = [$isOnline, $userId];

        $adb->pquery($sql, $queryParams);
    }

    // Added by Hieu Nguyen on 2022-10-12 to check if user is online or not according to its online status
    static function isUserOnline($userId) {
        global $adb;
        $sql = "SELECT 1 FROM vtiger_users WHERE id = ? AND is_online = 1";
        $isOnline = $adb->getOne($sql, [$userId]);
        return $isOnline == 1;
    }

    static function getUserLanguage($userId) {
        global $adb;

        $sql = "SELECT language FROM vtiger_users WHERE id = ?";
        $queryParams = [$userId];

        return $adb->getOne($sql, $queryParams);
    }

    static function updateUserLanguage($userId, $language) {
        global $adb;

        $sql = "UPDATE vtiger_users SET language = ? WHERE id = ?";
        $queryParams = [$language, $userId];

        $adb->pquery($sql, $queryParams);

        // Need to update user data in privileges file too
		require_once('modules/Users/CreateUserPrivilegeFile.php');
        createUserPrivilegesfile($userId);
		Vtiger_AccessControl::clearUserPrivileges($userId);
    }
}