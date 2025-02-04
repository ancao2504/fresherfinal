<?php

/*
	Logic_Helper
	Author: Hieu Nguyen
	Date: 2020-04-08
	Purpose: to provide util functions for call center integration logic
*/

require_once('include/utils/CallCenterUtils.php');
require_once('libraries/PHP-JWT/src/JWT.php');
use \Firebase\JWT\JWT;

class PBXManager_Logic_Helper {

	static function cleanupPhoneNumber($phoneNumber) {
		$specialChars = ['(', ')', ' ', '+', '-'];
		$phoneNumber = str_replace($specialChars, '', $phoneNumber);
		$phoneNumber = trim($phoneNumber);

		return $phoneNumber;
	}

	static function saveOutboundCache($agentExtNumber, $customerId, $customerPhoneNumber, $callLogId, $targetRecordId, $targetModule, $targetView) {	// Added by Vu Mai on 2022-05-10 to add target module and on 2022-11-03 to add target view to oubound cache
		$customerName = Vtiger_Util_Helper::getRecordName($customerId);
		$customerPhoneNumber = self::cleanupPhoneNumber($customerPhoneNumber);

		$outboundCache = array(
			'customer_id' => $customerId,
			'customer_name' => trim($customerName),
			'customer_phone_number' => $customerPhoneNumber,
			'call_log_id' => $callLogId,			// Determine which call log to update
			'target_record_id' => $targetRecordId,	// Determine which which target record at its ListView
			'target_module' => $targetModule,		// Added by Vu Mai on 2022-05-10 to determine which target module at its ListView
			'target_view' => $targetView,		// Added by Vu Mai on 2022-11-03 to determine which target module at its ListView
			'timestamp' => microtime(true)
		);

		file_put_contents("cache/{$agentExtNumber}_OutboundCall.json", json_encode($outboundCache));
		CallCenterUtils::saveDebugLog("[Call Center] Write outbound cache for {$agentExtNumber}", null, $outboundCache);
	}

	static function getOutboundCacheFile($agentExtNumber) {
		return "cache/{$agentExtNumber}_OutboundCall.json";
	}

	static function getOutboundCache($agentExtNumber) {
		$cacheFile = self::getOutboundCacheFile($agentExtNumber);
		$outboundCache = [];

		if (file_exists($cacheFile)) {
			$cacheData = json_decode(file_get_contents($cacheFile), true);

			if (!empty($cacheData)) {
				$outboundCache = decodeUTF8($cacheData);
			}
		}

		return $outboundCache;
	}

	static function addLeadingZeroToPhoneNumber($phoneNumber) {
		global $countryCodes;

		if (substr($phoneNumber, 0, 1) != '0') {
			$hasLeadingCountryCode = false;

			foreach ($countryCodes as $phoneCode => $countryInfo) {
				if (strpos($phoneNumber, "{$phoneCode}") === 0) {
					$hasLeadingCountryCode = true;
					break;
				}
			}

			if (!$hasLeadingCountryCode) {
				$phoneNumber = '0' . $phoneNumber;
			}
		}
		
		return $phoneNumber;
	}

	static function addVnCountryCodeToPhoneNumber($phoneNumber) {
		if (substr($phoneNumber, 0, 1) === '0') {
			return '84' . substr($phoneNumber, 1);
		}

		if (substr($phoneNumber, 0, 2) != '84') {
			return '84' . $phoneNumber;
		}
		
		return $phoneNumber;
	}

	static function getGatewayName() {
		$serverModel = PBXManager_Server_Model::getInstance();
		$gateway = $serverModel->get('gateway');

		return $gateway;
	}

	static function isClick2CallEnabled($moduleName) {
		global $callCenterConfig;
		$hotlines = PBXManager_Logic_Helper::getOutboundHotlines();
		if (empty($hotlines)) return false;
		return in_array($moduleName, $callCenterConfig['click2call_enabled_modules']);
	}

	static function canMakeCall() {
		global $callCenterConfig;
		if (isForbiddenFeature('CallCenterIntegration')) return false;
		if (!$callCenterConfig['enable']) return false;
		return PBXManager_Server_Model::checkPermissionForOutgoingCall();
	}

	static function canMakeAutoCall() {
		global $callCenterConfig;
		if (isForbiddenFeature('CallCenterIntegration')) return false;
		if (!$callCenterConfig['enable']) return false;
		$serverModel = PBXManager_Server_Model::getInstance();
		$connector = $serverModel->getConnector();
		
		if (method_exists($connector, 'makeAutoCall')) {
			return true;
		}

		return false;
	}

	static function canTransferCall() {
		$serverModel = PBXManager_Server_Model::getInstance();
		$connector = $serverModel->getConnector();
		
		if (method_exists($connector, 'transferCall')) {
			return true;
		}

		return false;
	}

	static function getCallCenterBridgeAccessToken($isPHPClient = false) {
		global $callCenterConfig, $current_user;
		$accessDomain = $callCenterConfig['bridge']['access_domain'];
		$privateKey = $callCenterConfig['bridge']['private_key'];
		$now = time();
			
		$payload = [
			'exp' => $now + ($isPHPClient ? 60 : 86400),
			'domain' => $accessDomain,
			'user_id' => ($isPHPClient ? 'PHP' : $current_user->id),
			'user_ext_number' => ($isPHPClient ? 'PHP' : $current_user->phone_crm_extension),
			'is_php_client' => $isPHPClient,
		];

		$token = JWT::encode($payload, $privateKey, 'HS256');
		return $token;
	}

	static function getWebPhoneToken() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (empty($currentUserModel->get('phone_crm_extension'))) return '';

		$activeConnector = PBXManager_Server_Model::getActiveConnector();
		$token = '';

		if ($activeConnector) {
			if (method_exists($activeConnector, 'getWebPhoneToken')) {
				$token = $activeConnector->getWebPhoneToken();
			}
		}

		return $token;
	}

	static function getWebPhoneCustomRingToneUrl() {
		global $current_user, $site_URL;
		$ringToneFile = 'upload/webphone_ringtone/ringtone_' . $current_user->id;

		if (file_exists($ringToneFile)) {
			return $site_URL . '/' . $ringToneFile;
		}

		return '';
	}

	static function getOutboundHotlines() {
		global $current_user, $callCenterConfig;
		static $hotlines = null;
		if (!empty($hotlines)) return $hotlines;
		if (empty($callCenterConfig['outbound_routing'])) return '';
		$hotlines = [];

		// Check if current user can use all hotlines
		if (in_array("Users:{$current_user->id}", $callCenterConfig['click2call_users_can_use_all_hotlines'])) {
			$hotlines = array_keys($callCenterConfig['outbound_routing']);
		}
		// Hotline can only be used by matching roles
		else {
			foreach ($callCenterConfig['outbound_routing'] as $hotline => $roleIds) {
				$matched = checkRolesAndSubordinates($current_user, $roleIds);
				if ($matched) $hotlines[] = $hotline;
			}
		}

		return $hotlines;
	}

	static function getDefaultOutboundHotline() {
		$outboundHotlines = self::getOutboundHotlines();
		if (empty($outboundHotlines)) return '';
		return $outboundHotlines[0];
	}

	static function getPreferredOutboundDevice() {
		global $current_user;
		$config = Users_Preferences_Model::loadPreferences($current_user->id, 'callcenter_config', true) ?? [];
	
		if (!empty($config['preferred_outbound_device'])) {
			return $config['preferred_outbound_device'];
		}

		return '';
	}

	/** Implemented by Phu Vo on 2020.07.27 Support crmid or pbx_call_id*/
	static function isAutoCall($callId, $crmRecord = true) {
		global $adb;

		static $cache = [];
		if (isset($cache[$callId])) return $cache[$callId];
		
		$sql = "SELECT 1 FROM vtiger_activity WHERE activitytype = 'Call' AND is_auto_call = 1";
		$params = [];
		
		if ($crmRecord) {
			$sql .= " AND activityid  = ?";
			$params[] = $callId;
		}
		else {
			$sql .= " AND pbx_call_id  = ?";
			$params[] = $callId;
		}

		$result = $adb->getOne($sql, $params);
		$cache[$callId] = $result;

		return $result;
	}

	// For ListView: (account_id ; (Accounts) phone) => true
	static function isRelateModuleField($fieldName) {
		preg_match('/(\w+) ; \((\w+)\) (\w+)/', $fieldName, $matches);

		if (count($matches) > 0) {
			return true;
		}

		return false;
	}

	// For ListView: (account_id ; (Accounts) phone) => Accounts
	static function getModuleNameFromRelateModuleFieldName($relatedModuleFieldName) {
		preg_match('/(\w+) ; \((\w+)\) (\w+)/', $relatedModuleFieldName, $matches);

		if (count($matches) > 0) {
			list($full, $referenceFieldName, $referenceModuleName, $targetFieldName) = $matches;
			return $referenceModuleName;
		}

		return '';
	}

	// For ListView: (account_id ; (Accounts) phone) => 123
	static function getRecordIdFromRelateModuleFieldName($relatedModuleFieldName, $listViewEntry) {
		preg_match('/(\w+) ; \((\w+)\) (\w+)/', $relatedModuleFieldName, $matches);

		if (count($matches) > 0) {
			list($full, $referenceFieldName, $referenceModuleName, $targetFieldName) = $matches;
			$referenceIdColumn = $referenceFieldName . $targetFieldName . '_id';
			return $listViewEntry->getRaw($referenceIdColumn);
		}

		return '';
	}

	// For MiniList: (account_id ; (Accounts) phone) => 09xxxxxxxx
	static function getRelatedModuleFieldValueForMiniList($relatedModuleFieldName, $listViewEntry) {
		preg_match('/(\w+) ; \((\w+)\) (\w+)/', $relatedModuleFieldName, $matches);

		if (count($matches) > 0) {
			list($full, $referenceFieldName, $referenceModuleName, $targetFieldName) = $matches;
			$referenceIdColumn = $referenceFieldName . $targetFieldName;
			return $listViewEntry->getRaw($referenceIdColumn);
		}

		return '';
	}

	// Implemented by Hieu Nguyen on 2022-02-15 to render button call so that it can be applied anywhere
	static function renderButtonCall($phoneNumber, $recordId) {
		if (!self::canMakeCall() || empty($phoneNumber) || empty($recordId)) {
			return '';
		}

		// Cleanup phone number
		$phoneNumber = preg_replace('/[-()\s]/', '', $phoneNumber);

		// Render button call for provided phone number
		$viewer = new Vtiger_Viewer();
		$viewer->assign('PHONE_NUMBER', $phoneNumber);
		$viewer->assign('RECORD_ID', $recordId);
		$btnCallHtml = $viewer->fetch('modules/PBXManager/tpls/ButtonCall.tpl');
		return $btnCallHtml;
	}
}