<?php

/*
	Data_Model
	Author: Hieu Nguyen
	Date: 2018-10-02
	Purpose: to handle data manipulating for call popup actions
*/

require_once('include/utils/CallCenterUtils.php');

class PBXManager_Data_Model {

	public static function prepareParamsToFindCustomerByPhoneNumber($phoneNumber, &$condition, &$params) {
		global $countryCodes;
		$matchingCases = [$phoneNumber];
		$checkLeadingCountryCode = strlen($phoneNumber) > 9;
		$hasLeadingCountryCode = false;

		// Check if phone number is leading by country code
		if ($checkLeadingCountryCode) {
			foreach ($countryCodes as $phoneCode => $countryInfo) {
				$codeLength = strlen($phoneCode);

				if (substr($phoneNumber, 0, $codeLength) == $phoneCode) {
					$withoutCountryCode = substr($phoneNumber, $codeLength);    // Remove country code
					$matchingCases[] = $withoutCountryCode;
					$matchingCases[] = '0' . $withoutCountryCode;               // Add leading zero
					$hasLeadingCountryCode = true;
					break;
				}
			}
		}

		// No leading country code
		if (!$hasLeadingCountryCode) {
			// Has leading zero
			if (substr($phoneNumber, 0, 1) == '0') {
				$withouthLeadindZero = ltrim($phoneNumber, '0');    // Remove leading zero
				$matchingCases[] = $withouthLeadindZero;
				$matchingCases[] = '84' . $withouthLeadindZero;     // Add Vietnam country code
			}
			// No leading zero
			else {
				$matchingCases[] = '0' . $phoneNumber;  // Add leading zero
			}
		}

		$condition = "pl.fnumber IN ('". join("', '", $matchingCases) ."')";
	}

	static function getCustomerAvatarFromArray($customerType, $customerData) {
		global $site_URL;
		$customerModel = Vtiger_Record_Model::getCleanInstance($customerType);
		$customerModel->setData($customerData);
		$avatar = $customerModel->getImageDetails();

		if (!empty($avatar[0]['id'])) {
			return "{$site_URL}/{$avatar[0]['path']}_{$avatar[0]['name']}";
		}

		return '';
	}

	// Added by Phu Vo on 2020.02.18 to find customer without having to pass customer number
	static function findCustomerById($customerId, $getAvatar = false) {
		global $adb;

		$sql = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.setype,
				vtiger_users.id AS assigned_user_id, vtiger_users.user_name as assigned_user_name, vtiger_users.phone_crm_extension AS assigned_user_ext
			FROM vtiger_crmentity
			LEFT JOIN vtiger_users ON (vtiger_users.id = vtiger_crmentity.main_owner_id)
			WHERE vtiger_crmentity.crmid = ? AND vtiger_crmentity.setype IN ('Leads', 'Contacts', 'CPTarget', 'Accounts')
			LIMIT 1";

		$result = $adb->pquery($sql, [$customerId]);
		$result = $adb->fetchByAssoc($result);

		if ($result) {
			$customer = Vtiger_Record_Model::getInstanceById($result['crmid'], $result['setype']);

			// Process useful data
			$matchedCustomer = [];
			$matchedCustomer['id'] = $customer->getId();
			$matchedCustomer['type'] = $customer->getModuleName();
			$matchedCustomer['salutation'] = $customer->get('salutation');
			$matchedCustomer['firstname'] = $customer->get('firstname');
			$matchedCustomer['lastname'] = $customer->get('lastname');
			$matchedCustomer['account_id'] = $customer->get('accountid');
			$matchedCustomer['assigned_user_id'] = $result['assigned_user_id'];
			$matchedCustomer['assigned_user_name'] = $result['assigned_user_name'];
			$matchedCustomer['phone_crm_extension'] = $result['phone_crm_extension'];

			// Get full name
			$matchedCustomer['name'] = vtranslate($matchedCustomer['salutation'], 'Vtiger') .' '. trim(getFullNameFromArray($matchedCustomer['type'], $matchedCustomer));

			// Get account name
			if ($matchedCustomer['type'] == 'Contacts' && !empty($matchedCustomer['account_id'])) {
				$matchedCustomer['account_name'] = Vtiger_Functions::getCRMRecordLabel($matchedCustomer['account_id']);
			}
			else if ($matchedCustomer['type'] == 'Leads' || $matchedCustomer['type'] == 'CPTarget') {
				$matchedCustomer['account_name'] = $customer->get('company');
				$matchedCustomer['account_id'] = 0;
			}

			// Get avatar
			if ($matchedCustomer['type'] == 'Contacts' && $getAvatar) {
				$matchedCustomer['avatar'] = self::getCustomerAvatarFromArray('Contacts', $matchedCustomer);
			}

			return $matchedCustomer;
		}

		// Return empty array as default
		return [];
	}

	static function findCustomerByPhoneNumber(&$phoneNumber, $forOutbound = false, $agentExtNumber = '', $getAvatar = false, $returnMatchedRecord = false) {
		global $adb;
		$cacheFile = PBXManager_Logic_Helper::getOutboundCacheFile($agentExtNumber);
		$condition = "";
		$params = [];

		// Find specific record that was clicked to call
		if ($forOutbound == true && file_exists($cacheFile)) {
			$outboundCache = PBXManager_Logic_Helper::getOutboundCache($agentExtNumber);

			if (!empty($outboundCache)) {
				$condition = "pl.crmid = ?";
				$params = [$outboundCache['customer_id']];

				// Clear cache file for next request
				file_put_contents($cacheFile, '');
			}
		}

		// Added by Phu Vo on 2019.07.25 to process prefix outbound (Warning: It may modify customer number due to outbound_prefix config)
		if ($forOutbound == true) {
			$callCenterConfig = Settings_Vtiger_Config_Model::loadConfig('callcenter_config'); // Modified 2019.08.01 get config from db

			// Some connector require to add a prefix number before customer number when make call
			$outboundPrefix = $callCenterConfig->outbound_prefix;
			
			// So we will have to due with it here before find customer number in crm db
			if (!empty($outboundPrefix)) {
				if (substr($phoneNumber, 0, 1) == $outboundPrefix) $phoneNumber = substr($phoneNumber, 1);
			}
		}
		// End process Grandstream outbound logic

		// Add leading zero number in case the call center provider does not have it
		$phoneNumber = PBXManager_Logic_Helper::addLeadingZeroToPhoneNumber($phoneNumber);

		if (strpos($condition, 'crmid =') === false) {
			self::prepareParamsToFindCustomerByPhoneNumber($phoneNumber, $condition, $params);
		}

		$sql = "SELECT pl.setype AS module_name, pl.crmid AS record_id, pl.fieldname AS matching_field, 
				us.id AS assigned_user_id, us.user_name as assigned_user_name, us.phone_crm_extension AS assigned_user_ext
			FROM vtiger_pbxmanager_phonelookup AS pl
			INNER JOIN vtiger_crmentity AS en ON (en.crmid = pl.crmid AND en.setype = pl.setype AND en.deleted = 0)
			LEFT JOIN vtiger_users AS us ON (us.id = en.main_owner_id)
			WHERE {$condition} AND pl.setype IN ('Leads', 'Contacts', 'CPTarget', 'Accounts')
			ORDER BY FIELD(pl.setype, 'Contacts', 'Leads', 'CPTarget', 'Accounts')
			LIMIT 1";
		$result = $adb->pquery($sql, $params);
		$matchedRecord = $adb->fetchByAssoc($result);
		if (empty($matchedRecord)) return [];
		if ($returnMatchedRecord) return $matchedRecord;    // Return matched record only, no need to fetch customer info

		$matchedCustomerInfo = self::getCustomerDetails($matchedRecord, $getAvatar);
		
		// Attach cached info into the customer info array
		if (!empty($outboundCache)) {
			if (!empty($outboundCache['call_log_id'])) {
				$matchedCustomerInfo['call_log_id'] = $outboundCache['call_log_id'];
			}

			if (!empty($outboundCache['target_record_id'])) {
				$matchedCustomerInfo['target_record_id'] = $outboundCache['target_record_id'];
			}

			// Added bu Vu Mai on 2022-10-05 to update target module to customer info
			if (!empty($outboundCache['target_module'])) {
				$matchedCustomerInfo['target_module'] = $outboundCache['target_module'];
			}
			// End Vu Mai

			// Added bu Vu Mai on 2022-11-03 to update target view to customer info
			if (!empty($outboundCache['target_view'])) {
				$matchedCustomerInfo['target_view'] = $outboundCache['target_view'];
			}
			// End Vu Mai
		}

		return $matchedCustomerInfo;
	}

	static function findAgentByExtNumber($extNumber) {
		global $adb;

		$sql = "SELECT id, user_name, first_name, last_name 
			FROM vtiger_users 
			WHERE phone_crm_extension = ? AND deleted = 0";
		$params = [$extNumber];
		$result = $adb->pquery($sql, $params);
		$matchedAgent = $adb->fetchByAssoc($result);

		if ($matchedAgent) {
			$matchedAgent['name'] = getFullNameFromArray('Users', $matchedAgent);
			return $matchedAgent;
		}
	}

	static function getRoutingByCustomerNumber($customerNumber, $hotline, $mode = 'ext') {
		global $adb, $callCenterConfig;

		if ($mode == 'ext') {
			$customer = self::findCustomerByPhoneNumber($customerNumber);

			if (empty($customer)) return '';

			// Routing by role which was set by config
			if (!empty($callCenterConfig['inbound_routing']) && $callCenterConfig['inbound_routing'][$hotline]) {
				$roleId = $callCenterConfig['inbound_routing'][$hotline];

				// Modified SQL by Phu Vo on 2019.09.04 to Use main owner id to handle Routing by customer number
				$sql = "SELECT u.phone_crm_extension
					FROM vtiger_users AS u
					INNER JOIN vtiger_user2role AS ur ON(ur.userid = u.id)
					INNER JOIN vtiger_role AS r ON(r.roleid = ur.roleid)
					INNER JOIN vtiger_crmentity AS e ON (e.main_owner_id = u.id)
					WHERE u.deleted = 0 AND e.crmid = ? AND r.parentrole LIKE ?";
				$params = [$customer['id'], "%{$roleId}%"];
				// End Phu Vo
			}
			// Normal routing without config
			else {
				// Modified SQL by Phu Vo on 2019.09.04 to Use main owner id to handle Routing by customer number
				$sql = "SELECT u.phone_crm_extension 
					FROM vtiger_users AS u 
					INNER JOIN vtiger_crmentity AS e ON (e.main_owner_id = u.id)
					WHERE e.crmid = ?";
				$params = [$customer['id']];
				// End Phu Vo
			}
			
			$extNumber = $adb->getOne($sql, $params);

			return $extNumber;
		}
	}

	static function isExists($callId, $status) {
		global $adb;

		$sql = "SELECT COUNT(sourceuuid) 
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ? AND callstatus = ?";
		$params = [$callId, $status];
		$isExists = $adb->getOne($sql, $params);

		return $isExists;
	}

	static function isTransferredCall($callId) {
		global $adb;

		$sql = "SELECT transferred 
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ?";
		$params = [$callId];
		$isTransferred = $adb->getOne($sql, $params);

		return $isTransferred == '1';
	}

	// This function returns full customer info with required format for RINGING event data
	static function getCustomerDetails(array $rawRecord, bool $getAvatar = false) {
		$customerRecordModel = Vtiger_Record_Model::getInstanceById($rawRecord['record_id'], $rawRecord['module_name']);
		if (empty($customerRecordModel)) return [];

		if (in_array($rawRecord['module_name'], ['CPTarget', 'Leads', 'Contacts'])) {
			$accountName = $customerRecordModel->get('company');

			if ($rawRecord['module_name'] == 'Contacts') {
				$accountName = strip_tags($customerRecordModel->getField('account_id')->getDisplayValue($customerRecordModel->get('account_id')));
			}

			$customerInfo = [
				'id' => $rawRecord['record_id'],
				'type' => $rawRecord['module_name'],
				'salutation' => $customerRecordModel->get('salutationtype'),
				'firstname' => $customerRecordModel->get('firstname'),
				'lastname' => $customerRecordModel->get('lastname'),
				'account_id' => $customerRecordModel->get('account_id'),
				'account_name' => trim($accountName),
				'assigned_user_id' => $rawRecord['assigned_user_id'],
				'assigned_user_name' => $rawRecord['assigned_user_name'],
				'assigned_user_ext' => $rawRecord['assigned_user_ext'],
			];

			$customerInfo['name'] = vtranslate($customerInfo['salutation'], 'Vtiger') .' '. trim(getFullNameFromArray($rawRecord['module_name'], $customerInfo));

			if ($getAvatar && $rawRecord['module_name'] == 'Contacts') {
				$customerInfo['avatar'] = self::getCustomerAvatarFromArray('Contacts', $customerInfo);
			}
		}

		if ($rawRecord['module_name'] == 'Accounts') {
			$customerInfo = [
				'id' => $rawRecord['record_id'],
				'type' => $rawRecord['module_name'],
				'name' => $customerRecordModel->get('accountname'),
				'assigned_user_id' => $rawRecord['assigned_user_id'],
				'assigned_user_name' => $rawRecord['assigned_user_name'],
				'assigned_user_ext' => $rawRecord['assigned_user_ext'],
			];
		}
		
		return decodeUTF8($customerInfo);
	}

	static function getCustomerFromCall($callId) {
		global $adb;
		$sql = "SELECT c.setype AS module_name, c.crmid AS record_id, u.id AS assigned_user_id, 
				u.user_name AS assigned_user_name, u.phone_crm_extension AS assigned_user_ext
			FROM vtiger_crmentity AS c
			INNER JOIN vtiger_pbxmanager AS p ON (p.customer = c.crmid)
			INNER JOIN vtiger_crmentity pe ON (pe.crmid = p.pbxmanagerid AND pe.deleted = 0)
			LEFT JOIN vtiger_users AS u ON (u.id = c.main_owner_id)
			WHERE c.deleted = 0 AND p.sourceuuid = ?";
		$params = [$callId];
		$result = $adb->pquery($sql, $params);
		$rawRecord = $adb->fetchByAssoc($result);
		if (empty($rawRecord)) return [];

		$customerInfo = self::getCustomerDetails($rawRecord);
		return $customerInfo;
	}

	static function getAgentUserIdFromCall($callId) {
		global $adb;

		$sql = "SELECT user 
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ?";
		$params = [$callId];
		$userId = $adb->getOne($sql, $params);

		return $userId;
	}

	static function getExtraDataFromCall($callId) {
		global $adb;

		$sql = "SELECT extra_data 
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ?";
		$params = [$callId];
		$extraData = $adb->getOne($sql, $params);

		return json_decode($extraData, true) ?? [];
	}

	static function getCallDirection($callId) {
		global $adb;

		$sql = "SELECT direction
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ?";
		$params = [$callId];
		$direction = $adb->getOne($sql, $params);

		return $direction;
	}

	static function getCallStatus($callId) {
		global $adb;

		$sql = "SELECT callstatus
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE sourceuuid = ?";
		$params = [$callId];
		$status = $adb->getOne($sql, $params);

		return $status;
	}

	static function getLatestSubCallId($parentCallId) {
		global $adb;

		$sql = "SELECT sourceuuid AS call_id
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE parent_call_id = ?
			ORDER BY pbxmanagerid DESC
			LIMIT 1";
		$params = [$parentCallId];
		$callId = $adb->getOne($sql, $params);

		return $callId;
	}

	static function getCallData($callId, $isParentCallId = false) {
		global $adb;

		$condition = ($isParentCallId) ? "parent_call_id = ?" : "sourceuuid = ?";

		$sql = "SELECT *
			FROM vtiger_pbxmanager
			INNER JOIN vtiger_crmentity ON (crmid = pbxmanagerid AND deleted = 0)
			WHERE {$condition}
			LIMIT 1";
		$params = [$callId];
		$result = $adb->pquery($sql, $params);
		$callData = $adb->fetchByAssoc($result);

		if (!empty($callData)) {
			return $callData;
		}

		return [];
	}

	// Init new call
	static function handleStartupCall($data) {
		// Tell call popup the addition info of the call
		self::forwardAdditionInfoToCallPopup($data);

		// Then save the pbx log
		$moduleModel = Vtiger_Module_Model::getInstance('PBXManager');
		$recordModel = Vtiger_Record_Model::getCleanInstance('PBXManager');

		foreach ($data as $fieldName => $value) {
			$recordModel->set($fieldName, $value);
		}

		$moduleModel->saveRecord($recordModel);
	}

	// Forward addition info to call popup
	// Modified by Vu Mai on 2022-10-05 to get right module add to addition info
	// Modified by Vu Mai on 2022-11-03 to get right view add to addition info
	static function forwardAdditionInfoToCallPopup(array $data) {
		$customer = $GLOBALS['customer'];
		$targetRecordId = $customer['target_record_id'];
		$targetModule = $customer['target_module'];
		$targetView = $customer['target_view'];

		if (!empty($targetRecordId)) {
			$msg = [
				'call_id' => $data['sourceuuid'],
				'receiver_id' => $data['user'],
				'state' => 'ADDITION_INFO',
				'addition_info' => [
					'target_record_id' => $targetRecordId,  # Target record id at its ListView
					'target_module' => $targetModule,  # Target module at its ListView
					'target_view' => $targetView,  # Target module at its ListView
					// TODO: Add more info if any
				]
			];

			PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
		}
	}
	// End Vu Mai

	// Update call
	static function updateCall($callId, $data, $skipCompletedCall = true) {
		global $adb;

		if (self::getCallStatus($callId) == 'completed' && $skipCompletedCall) {
			// Do not update the call that is already ended
			return;
		}

		$fieldList = [];

		foreach ($data as $fieldName => $value) {
			$fieldList[] = "{$fieldName} = ?";
		}

		$sql = "UPDATE vtiger_pbxmanager SET ". join(', ', $fieldList) ." WHERE sourceuuid = ?";
		$params = array_values($data);
		$params[] = $callId;
		$adb->pquery($sql, $params);
	}

	// Update call status
	static function updateCallStatus($callId, $data) {
		global $adb;

		if (self::getCallStatus($callId) == 'completed') {
			// Do not update the call that is already ended
			return;
		}

		$sql = "UPDATE vtiger_pbxmanager SET callstatus = ? WHERE sourceuuid = ?";
		$params = [$data['callstatus'], $callId];
		$adb->pquery($sql, $params);
	}

	// Mark a call as transfered
	static function markCallAsTransferred($callId, $curAgentExt, $destAgentExt) {
		global $adb;
		// Get transferrer user name
		$userFullNameSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
		$sql = "SELECT {$userFullNameSql} FROM vtiger_users WHERE phone_crm_extension = ?";
		$transferrerName = $adb->getOne($sql, [$curAgentExt]);

		// Get transferred user name
		$userFullNameSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
		$sql = "SELECT {$userFullNameSql} FROM vtiger_users WHERE phone_crm_extension = ?";
		$transferredName = $adb->getOne($sql, [$destAgentExt]);

		$endTime = date('Y-m-d H:i:s');
		$transferInfo = [
			'transferrer_name' => $transferrerName,
			'transferrer_ext' => $curAgentExt,
			'transferred_name' => $transferredName,
			'transferred_ext' => $destAgentExt,
		];

		// Update call info
		$sql = "UPDATE vtiger_pbxmanager 
			SET transferred = 1, endtime = ?, extra_data = ?,
				totalduration = TIME_TO_SEC(TIMEDIFF('{$endTime}', starttime)), 
				billduration = TIME_TO_SEC(TIMEDIFF('{$endTime}', starttime))
			WHERE sourceuuid = ?";
		$params = [$endTime, json_encode($transferInfo), $callId];
		$adb->pquery($sql, $params);

		// Update call description
		$sql = "UPDATE vtiger_crmentity AS e
			INNER JOIN vtiger_pbxmanager AS p ON (p.pbxmanagerid = e.crmid AND p.sourceuuid = ?)
			SET e.description = ?";
		$params = [$callId, "Transferred from {$curAgentExt} to {$destAgentExt}"];
		$adb->pquery($sql, $params);
	}

	static function deleteCall($callId) {
		global $adb;
		$sql = "DELETE FROM vtiger_pbxmanager WHERE sourceuuid = ?";
		$adb->pquery($sql, [$callId]);
	}

	// End call
	static function handleHangupCall($callId, $data) {
		global $adb;

		if ($data['callstatus'] == 'completed') {
			$totalDurationMins = (!empty($data['totalduration'])) ? round($data['totalduration'] / 60, 2) : '';
			$billDurationMins = (!empty($data['billduration'])) ? round($data['billduration'] / 60, 2) : '';

			$sql = "UPDATE vtiger_pbxmanager SET callstatus = ?, endtime = ?, totalduration = ?, total_duration_minutes = ?, billduration = ?, bill_duration_minutes = ?, recordingurl = ? WHERE sourceuuid = ?";
			$params = [$data['callstatus'], $data['endtime'], $data['totalduration'], $totalDurationMins, $data['billduration'], $billDurationMins, $data['recordingurl'], $callId];
		}
		else {
			if (self::getCallStatus($callId) == 'completed') {
				// Do not update the call that is already ended
				return;
			}

			$sql = "UPDATE vtiger_pbxmanager SET callstatus = ? WHERE sourceuuid = ?";
			$params = [$data['callstatus'], $callId];
		}
		
		$adb->pquery($sql, $params);
	}

	// Implement by Phu Vo on 2020.01.21
	static function saveMissedCall(array $data, $customer, $agent, array $userIds) {
		// Default values
		$data['direction'] = 'inbound';
		$data['callstatus'] = 'hangup';

		// Assign agent id
		$agentId = !empty($agent) ? $agent['id'] : '';
		$data['user'] = $agentId ?? '';

		// Assign owner id
		$assignedUserId = $agentId;
		if (empty($assignedUserId) && !empty($customer)) $assignedUserId = $customer['assigned_user_id'];
		if (empty($assignedUserId)) $assignedUserId = $userIds[0];
		if (empty($assignedUserId)) $assignedUserId = 1; // In case empty config, assign to admin

		$data['assigned_user_id'] = $assignedUserId;
		
		$callId = $data['sourceuuid'];
		$existedCall = Vtiger_Record_Model::getInstanceByConditions('PBXManager', ['sourceuuid' => $callId]);

		if (!empty($existedCall)) {
			// Process in case we already receive a ringing event
			foreach ($data as $fieldName => $value) {
				$existedCall->set($fieldName, $value);
			}

			$existedCall->set('mode', 'edit');
			$existedCall->save();
		}
		else {
			// Create new record
			$moduleModel = Vtiger_Module_Model::getInstance('PBXManager');
			$recordModel = Vtiger_Record_Model::getCleanInstance('PBXManager');

			foreach ($data as $fieldName => $value) {
				$recordModel->set($fieldName, $value);
			}
	
			$moduleModel->saveRecord($recordModel);
		}
	}

	/** Implemented by Phu Vo on 2020.07.27 */
	static function saveAutoCallLog($callId, $customerNumber, $autoCallText, $relatedRecordModule, $relatedRecordId) {
		global $current_user, $adb;

		// Init customer module list
		// [TODO] Eliminate this customer modules hacking some day
		$customerModules = ['Contacts', 'Leads', 'CPTarget'];
		$customerModulesString = "'" . join("', '", $customerModules) . "'";
		
		$description = "Cuộc gọi tự động đến số điện thoại {$customerNumber} với nội dung: \"{$autoCallText}\".";

		// Process start and end date time
		$startDateTime = date('Y-m-d H:i:s');
		list($startDate, $startTime) = explode(' ', $startDateTime);
		$endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + 60); // Default duration 1 min, will update later
		list($endDate, $endTime) = explode(' ', $endDateTime);

		// Create auto call log
		$callLog = Vtiger_Record_Model::getCleanInstance('Events');
		$callLog->set('module', 'Events');
		$callLog->set('action', 'SaveAjax');
		$callLog->set('activitytype', 'Call');
		$callLog->set('pbx_call_id', $callId);
		$callLog->set('eventstatus', 'Planned');
		$callLog->set('is_auto_call', true);
		$callLog->set('description', $description);
		$callLog->set('date_start', DateTimeField::convertToUserFormat($startDate));
		$callLog->set('time_start', $startTime);
		$callLog->set('due_date', DateTimeField::convertToUserFormat($endDate));
		$callLog->set('time_end', $endTime);
		$callLog->set('events_call_direction', 'Outbound');
		$callLog->set('visibility', 'Public');
		$callLog->set('assigned_user_id', $current_user->id);
		$callLog->set('main_owner_id', $current_user->id);

		// Default link with input related record
		$parentId = $relatedRecordId;

		// First we will try to link auto call log with customer
		if (!in_array($relatedRecordModule, $customerModules)) {
			// When input relatedRecordId is not a customer id, try to find if it related with any customer
			$relatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedRecordModule);
			$relatedModuleModel = $relatedRecordModel->getModule();
			$relatedReferenceFields = $relatedModuleModel->getFieldsByType('reference');
			$referenceIds = [];

			// Fetch all reference ids from related record
			foreach ($relatedReferenceFields as $fieldModel) {
				$fieldName = $fieldModel->getName();
				$fieldValue = $relatedRecordModel->get($fieldName);
				if (!empty($fieldValue)) $referenceIds[] = $fieldValue;
			}

			// Use custom sql to check if there is any customer id in reference ids
			$referenceIdsString = generateQuestionMarks($referenceIds);
			$queryParams = $referenceIds;

			$sql = "SELECT crmid FROM vtiger_crmentity
				WHERE crmid IN ({$referenceIdsString}) AND crmid > 0 AND setype IN ({$customerModulesString})
				ORDER BY FIELD(setype, {$customerModulesString})";

			$customerId = $adb->getOne($sql, $queryParams);

			// Link with found customer id or keep input related record
			if (!empty($customerId)) $parentId = $customerId;
		}

		$callLog->set('parent_id', $parentId);

		// Convert to user display value
		$dateTimeUIType = new Vtiger_Datetime_UIType();
		$formatedStartDateTime = $dateTimeUIType->getDisplayValue($startDateTime);

		// Process subject
		$parentName = trim(Vtiger_Functions::getCRMRecordLabel($parentId));
		$callLog->set('subject', "[Auto Call] {$formatedStartDateTime} - {$customerNumber} - {$parentName}");

		$callLog->save();

		return $callLog;
	}

	/** Implemented by Phu Vo on 2020.07.27 */
	static function updateAutoCallStatus($callId, $status, $duration = 0, $responseKey = null) {        
		$callLog = Vtiger_Record_Model::getInstanceByConditions('Calendar', ['pbx_call_id' => $callId]);

		// Something did not right
		if (empty($callLog)) return false;

		$status = strtoupper($status);
		$dateTimeNow = date('d/m/Y H:i:s');
		$description = $callLog->get('description');
		$startDateTime = $callLog->get('date_start') . ' ' . $callLog->get('time_start');
		$endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime) + $duration);
		list($endDate, $endTime) = explode(' ', $endDateTime);

		// Process call log description base on status
		if ($status == 'BUSY') {
			$description .= "\n[{$dateTimeNow}] Khách hàng từ chối nghe máy";
		}

		if ($status == 'ANSWERED') {
			$description .= "\n[{$dateTimeNow}] Khách hàng nghe máy";
			if ($responseKey != '' && $responseKey !== null) $description .= " và ấn phím {$responseKey}";
		}

		// Perform update call log
		$callLog->set('eventstatus', 'Held');
		$callLog->set('due_date', DateTimeField::convertToUserFormat($endDate));
		$callLog->set('time_end', $endTime);
		$callLog->set('description', trim($description));
		$callLog->set('mode', 'edit');

		$callLog->save();

		return $callLog;
	}
}