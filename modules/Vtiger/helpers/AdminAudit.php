<?php

/*
	AdminAudit_Helper
	Author: Hieu Nguyen
	Date: 2020-08-02
	Purpose: to provide util functions saving audit log for Admin's actions
*/

class Vtiger_AdminAudit_Helper {

    static function saveLog($category, $description, $logData = null) {
        global $current_user, $loggerConfig;
        $loggerConfig['INFO'] = true;
        $logger = LoggerManager::getLogger('ADMIN_AUDIT');

        $log = "[{$category}] {$description} - [User: {$current_user->user_name}][IP: {$_SERVER['REMOTE_ADDR']}]" . "\r\n";
        $log .= "- REQUEST URL: {$_SERVER['REQUEST_URI']}" . "\r\n";
        if ($_SERVER['REQUEST_METHOD'] == 'POST') $log .= "- POST DATA: ". json_encode($_POST, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
        if ($logData !== null) $log .= '- LOG DATA: ' . json_encode($logData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
        $log .= '==============================';

        $logger->info($log);
    }
}