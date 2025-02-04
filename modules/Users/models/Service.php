<?php

/**
 * Name: Service.php
 * Author: Phu Vo
 * Date: 2021.03.24
 * Description: Handle Users module services
 */

class Users_Service_Model extends Vtiger_Base_Model {

    static function updateUserOnlineStatus() {
        global $adb;
        $sessionLifeTime = ini_get('session.gc_maxlifetime') ?? 3600;   // Modified by Hieu Nguyen on 2022-10-12 to get this value from PHP config

        // We don't have any clue to process
        if ($sessionLifeTime == 0) return;

        $sql = "SELECT id
            FROM
                (
                    SELECT
                        vtiger_users.id,
                        MAX(TIMESTAMP(IFNULL( vtiger_loginhistory.login_time, 0))) AS login_stamp 
                    FROM vtiger_users
                    LEFT JOIN vtiger_loginhistory ON (vtiger_users.user_name = vtiger_loginhistory.user_name)
                    WHERE vtiger_users.user_name IS NOT NULL AND vtiger_users.user_name <> '' 
                    GROUP BY vtiger_users.user_name 
                ) AS temp 
            WHERE
                login_stamp IS NULL 
                OR (NOW() > date_add(login_stamp, INTERVAL ? SECOND))";
        $queryParams = [$sessionLifeTime];

        $userIds = [];
        $result = $adb->pquery($sql, $queryParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $userIds[] = $row['id'];
        }

        $userIdsString = "('" . join("', '", $userIds) . "')";
        $sql = "UPDATE vtiger_users SET is_online = 0 WHERE id IN $userIdsString";
        $result = $adb->pquery($sql);
    }
}