<?php

/*
*   Class MauticUtils
*   Author: Phuc Lu
*   Date: 2019-06-25
*   Purpose: A parent class for mautic api
*   Run the following SQL for new custom table
*   Script generate table:
		CREATE TABLE vtiger_mautic_sync_queue (
			id int(19) NOT NULL AUTO_INCREMENT,
			targetlist_id int(19) DEFAULT NULL,
			crm_id int(19) DEFAULT NULL,
			crm_type varchar(100) DEFAULT NULL,
			status varchar(50) DEFAULT '',
			called_times int(1) DEFAULT '0',
			max_times int(1) DEFAULT '3',
			created_time datetime DEFAULT NULL,
			modified_time datetime DEFAULT NULL,
			latest_response text,
			PRIMARY KEY (id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

require_once ('vendor/autoload.php');
use Mautic\MauticApi;
use Mautic\Auth\ApiAuth;

// Refactored by Hieu Nguyen on 2021-11-16
class MauticUtils {

	// Added by Phuc on 2019.06.27 to get an instance of Mautic API
	public static function getApiInstance($context) {
		// Modified by Hieu Nguyen on 2021-11-15 to use Oauth2 to boost performance
		$config = CPMauticIntegration_Config_Helper::loadConfig();
		$credentials = $config['credentials'];

		if (empty($credentials['base_url']) || empty($credentials['access_token']) || empty($credentials['refresh_token'])) {
			return false;
		}

		$settings = [
			'baseUrl' => $credentials['base_url'],
			'version' => 'OAuth2',
			'clientKey' => $credentials['client_id'],
			'clientSecret' => $credentials['client_secret'],
			'accessToken' => $credentials['access_token'],
			'accessTokenExpires' => $credentials['expires'],
			'refreshToken' => $credentials['refresh_token']
		];

		$initAuth = new ApiAuth();
		$auth = $initAuth->newAuth($settings, 'OAuth');
		// End Hieu Nguyen

		// Modified by Hieu Nguyen on 2021-11-17 to get new api instance using new SDK method
		$apiFactory = new MauticApi();
		$apiInstance = $apiFactory->newApi($context, $auth, $settings['baseUrl']);
		return $apiInstance;
		// End Hieu Nguyen
	}
	// End by Phuc

	// Added by Phuc on 2019.06.27 to add record to mautic queue
	// Modified by Hieu Nguyen on 2021-11-25 to optimize queue table
	public static function addToQueue($crmId, $crmType, $action, array $params = []) {
		global $adb;
		$params = json_encode($params);
		$paramsHash = md5($params);

		// Remove all other queues when the action is Delete
		if ($action == 'Delete') {
			$sql = "DELETE FROM vtiger_mautic_sync_queue WHERE crm_id = ? AND crm_type = ?";
			$adb->pquery($sql, [$crmId, $crmType]);
		}

		// Insert queue
		$sql = "INSERT IGNORE INTO vtiger_mautic_sync_queue (crm_id, crm_type, action, params, params_hash) VALUES (?, ?, ?, ?, ?)";
		$adb->pquery($sql, [$crmId, $crmType, $action, $params, $paramsHash]);

		// Set flag to prevent adding multiple queue for the same customer
		if (in_array($action, ['Add_To_Segment', 'Update_Stage', 'Update_Tags'])) {
			$GLOBALS['mautic_queue_sync_customer_skip_' . $crmId] = true;
		}
	}
	// End by Phuc
	
	// Added by Phuc on 2019.06.27 to update record in mautic queue
	// Modified by Hieu Nguyen on 2021-11-25 to optimize queue table
	public static function updateQueue($queueId) {
		global $adb;
		$sql = "UPDATE vtiger_mautic_sync_queue SET attempt_count = attempt_count + 1 WHERE queue_id = ?";
		$adb->pquery($sql, [$queueId]);
	}
	// End by Phuc

	// Added by Hieu Nguyen on 2021-11-25 to remove a queue by its id
	public static function removeQueue($queueId) {
		global $adb;
		$sql = "DELETE FROM vtiger_mautic_sync_queue WHERE queue_id = ?";
		$adb->pquery($sql, [$queueId]);
	}

	// Added by Phuc on 2019.08.14 to remove a queue by conditions
	// Modified by Hieu Nguyen on 2021-11-23 to pass params as the fourth condition to find exactly which queue we want to remove
	public static function removeQueueByConditions($crmId, $crmType, $action, array $params = []) {
		global $adb;
		$sql = "DELETE FROM vtiger_mautic_sync_queue WHERE crm_id = ? AND crm_type = ? AND action = ? AND params = ?";
		$adb->pquery($sql, [$crmId, $crmType, $action, json_encode($params)]);
	}
	// End by Phuc
}