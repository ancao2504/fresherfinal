<?php

/*
    Class WebhookUtils
    Author: Hieu Nguyen
    Date: 2019-07-03
    Purpose: To provide util functions for handling webhook logic
*/

class WebhookUtils {

    static $logger;

    static function getRequest($isPost = true) {
        if ($isPost) {
            require_once('include/utils/RestfulApiUtils.php');
            parse_str($_SERVER['QUERY_STRING'], $queryStrings);
            $request = RestfulApiUtils::getRequest();

            if (!empty($queryStrings)) {
                foreach($queryStrings as $name => $value) {
                    $request->set($name, $value);
                }
            }

            return $request;
        }
        
        return new Vtiger_Request($_REQUEST, $_REQUEST, true);
    }

    static function getAuthenticatedUser(&$webhookInstance) {
        $user = $webhookInstance->getLogin();

		if (!$user && isset($_SESSION['authenticated_user_id'])) {
			$userId = Vtiger_Session::get('AUTHUSERID', $_SESSION['authenticated_user_id']);

			if ($userId && vglobal('application_unique_key') == $_SESSION['app_unique_key']) {
				$user = CRMEntity::getInstance('Users');
				$user->retrieveCurrentUserInfoFromFile($userId);
				$webhookInstance->setLogin($user);
			}
		}

		return $user;
	}

    static function saveLog($description, $headers = null, $input = null, $response = null) {
        if (!empty(static::$logger)) {
            $logger = LoggerManager::getLogger(static::$logger);
        }
        else {
            $logger = LoggerManager::getLogger('PLATFORM');
        }

        // Save log
        $log = 'Description: ' . $description . " - [IP: {$_SERVER['REMOTE_ADDR']}]" . "\r\n";
        $log .= 'Headers: ' . json_encode($headers, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
        $log .= 'Body: ' . json_encode($input) . "\r\n";
        $log .= 'Response: ' . json_encode($response, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
        $log .= '==============================';

        $logger->info($log);
    }
}