<?php

/*
	Telesales.php
	Author: Hieu Nguyen
	Date: 2022-10-21
	Purpose: privide util function to manipulate with Telesales Campaign
*/

class Campaigns_Telesales_Model {

	static function getCampaignInfo($campaignId) {
		global $adb;
		if (empty($campaignId)) return [];
		$sql = "SELECT campaignname AS name, campaignstatus AS status, campaigns_purpose AS purpose, campaigntype AS type,
				call_script, start_date, closingdate AS end_date
			FROM vtiger_campaign
			WHERE campaignid = ?";
		$result = $adb->pquery($sql, [$campaignId]);
		$campaignInfo = $adb->fetchByAssoc($result);

		if (!empty($campaignInfo)) {
			return decodeUTF8($campaignInfo);
		}

		return [];
	}

	// Added by Vu Mai on 2022-16-11 to get campaign ids by purpose
	static function getCampaignIdsByPurpose($purpose, bool $active = false) {
		global $adb;
		if (empty($purpose)) return [];
		$sql = "SELECT campaignid AS id FROM vtiger_campaign WHERE campaigns_purpose = ?";

		if ($active) {
			$sql .= ' AND campaignstatus = "Active"';
		}

		$result = $adb->pquery($sql, [$purpose]);
		$campaignIds = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$campaignIds[] = $row['id'];
		}

		return $campaignIds;
	}

	static function getNewCustomerStatusForCampaign($campaignId) {
		static $cache = [];
		if (!empty($cache[$campaignId])) return $cache[$campaignId];

		$campaignInfo = self::getCampaignInfo($campaignId);
		$customerStatusList = CPTelesales_Config_Helper::loadConfigByTableType($campaignInfo['purpose'], 'status_list');
		$result = '';

		foreach ($customerStatusList as $status => $info) {
			if ($info['is_new']) {
				$result = $status;
				break;
			}
		}

		$cache[$campaignId] = $result;
		return $result;
	}

	static function getSelectedMKTListIds($campaignId) {
		global $adb;
		$sqlGetMKTLists = "SELECT GROUP_CONCAT(mkle.crmid) AS mkt_list_id
			FROM vtiger_crmentityrel AS rel
			INNER JOIN vtiger_crmentity AS mkle ON (mkle.crmid = rel.relcrmid AND mkle.deleted = 0)
			WHERE rel.crmid = ? AND rel.relmodule = 'CPTargetList'";
		$mktListIdsStr = $adb->getOne($sqlGetMKTLists, [$campaignId]);
		$mktListIds = explode(',', $mktListIdsStr);
		return $mktListIds;
	}

	static function getSelectedMKTListsToEdit($campaignId) {
		// Get selected MKT lists
		$mktListIds = self::getSelectedMKTListIds($campaignId);

		// Get statistics for each MKT List
		$result = [];

		foreach ($mktListIds as $mktListId) {
			$result[] = self::getMKTListInfoWithStatistics($mktListId, $campaignId);
		}

		return $result;
	}

	// Get MKT Lists info to be inserted into the MKT Lists table in New Telesales Campaign form
	static function getMKTListsInfo(array $mktListIds) {
		global $adb;
		if (empty($mktListIds)) return [];
		$sql = "SELECT tle.crmid AS id, tl.name, tle.description, tl.cptargetlist_status AS status, tl.customers_count
			FROM vtiger_cptargetlist as tl
			INNER JOIN vtiger_crmentity AS tle ON (tle.crmid = tl.cptargetlistid AND tle.deleted = 0)
			WHERE tl.cptargetlistid IN (". join(', ', $mktListIds) .")";
		$result = $adb->pquery($sql, []);
		$mktLists = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row['status'] = vtranslate($row['status'], 'CPTargetList');
			$mktLists[$row['id']] = decodeUTF8($row);
		}

		return $mktLists;
	}

	// Get MKT List info and statistics to be inserted into the MKT Lists table in Edit Telesales Campaign Form
	static function getMKTListInfoWithStatistics($mktListId, $campaignId) {
		global $adb;
		if (empty($mktListId) || empty($campaignId)) return [];
		$sql = "SELECT tle.crmid AS id, tl.name, tle.description, tl.cptargetlist_status AS status, tl.customers_count AS total_customers_count, COUNT(tcd.id) AS distributed_customers_count
			FROM vtiger_cptargetlist as tl
			INNER JOIN vtiger_crmentity AS tle ON (tle.crmid = tl.cptargetlistid AND tle.deleted = 0)
			LEFT JOIN vtiger_telesales_campaign_distribution AS tcd ON (tcd.mkt_list_id = tl.cptargetlistid AND tcd.campaign_id = ?)
			WHERE tl.cptargetlistid = ?
			GROUP BY tle.crmid";
		$result = $adb->pquery($sql, [$campaignId, $mktListId]);
		$mktList = $adb->fetchByAssoc($result);
		$mktList['status'] = vtranslate($mktList['status'], 'CPTargetList');
		$totalCustomersCount = $mktList['total_customers_count'];
		$distributedCustomersCount = $mktList['distributed_customers_count'];
		$remainingCustomersCount = $totalCustomersCount - $distributedCustomersCount;
		unset($mktList['total_customers_count']);
		unset($mktList['distributed_customers_count']);

		$mktList['statistics'] = [
			'total_customers_count' => intval($totalCustomersCount),
			'distributed_customers_count' => intval($distributedCustomersCount),
			'remaining_customers_count' => intval($remainingCustomersCount),
		];

		return decodeUTF8($mktList);
	}

	// Get mobile number with duplicated count across MKT Lists
	static function getDuplicatedMobileNumbersFromMKTLists(array $mktListIds) {
		global $adb;
		if (empty($mktListIds)) return 0;
		$tempTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($mktListIds);

		$sql = "SELECT
			temp.number,
			COUNT(temp.number) AS count
		FROM (
			SELECT DISTINCT ce.crmid, fnumber AS number
			FROM vtiger_crmentity AS ce
			INNER JOIN {$tempTableCrmentityrel} AS rel ON (rel.relcrmid = ce.crmid AND rel.crmid IN (". join(', ', $mktListIds) ."))
			INNER JOIN vtiger_pbxmanager_phonelookup AS pl ON (pl.crmid = ce.crmid AND pl.fieldname = 'mobile')
			WHERE ce.deleted = 0 AND ce.setype IN('Leads', 'Contacts', 'CPTarget')
		) AS temp
		GROUP BY temp.number
		HAVING COUNT(temp.number) > 1";
		$result = $adb->pquery($sql, []);
		$numbers = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$numbers[$row['number']] = $row['count'];
		}

		return $numbers;
	}

	// Count customers without mobile number across MKT Lists (including records duplicated by ID)
	static function countCustomersWithoutMobileNumberFromMKTLists(array $mktListIds) {
		global $adb;
		if (empty($mktListIds)) return 0;
		$tempTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($mktListIds);

		$sql = "SELECT COUNT(ce.crmid)
			FROM vtiger_crmentity AS ce
			INNER JOIN {$tempTableCrmentityrel} AS rel ON (rel.relcrmid = ce.crmid AND rel.crmid IN (". join(', ', $mktListIds) ."))
			WHERE ce.deleted = 0 AND ce.setype IN('Leads', 'Contacts', 'CPTarget') AND ce.crmid NOT IN (
				SELECT crmid FROM vtiger_pbxmanager_phonelookup WHERE fieldname = 'mobile'
			)";
		$count = $adb->getOne($sql, []);
		return $count;
	}

	// Count unique customers with mobile number across MKT Lists (exclude records duplicated by ID or mobile)
	static function countDistributableCustomersFromMKTLists(array $mktListIds, $campaignId = '') {
		global $adb;
		if (empty($mktListIds)) return 0;
		$tempTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($mktListIds);

		$sql = "SELECT COUNT(DISTINCT ce.crmid)
			FROM vtiger_crmentity AS ce
			INNER JOIN {$tempTableCrmentityrel} AS rel ON (rel.relcrmid = ce.crmid AND rel.crmid IN (". join(', ', $mktListIds) ."))
			INNER JOIN vtiger_pbxmanager_phonelookup AS pl ON (pl.crmid = ce.crmid AND pl.fieldname = 'mobile')
			WHERE ce.deleted = 0 AND ce.setype IN('Leads', 'Contacts', 'CPTarget')";
		$params = [];

		// Modified by Vu Mai on 2023-02-16 remove customer duplicated mobile
		$duplicatedCustomersIds = self::getDuplicatedCustomersByMobileNumber($mktListIds, true);

		if (!empty($duplicatedCustomersIds)) {
			$sql .= " AND ce.crmid NOT IN (". join(', ', $duplicatedCustomersIds) .")";
		}
		// End Vu Mai

		if (!empty($campaignId)) {
			$sql .= " AND ce.crmid NOT IN (SELECT customer_id FROM vtiger_telesales_campaign_distribution WHERE campaign_id = ?)";
			$params[] = $campaignId;
		}

		return $adb->getOne($sql, $params);
	}

	// Get data statistics across MKT Lists
	static function getDataStatistics(array $mktListIds, $campaignId = '') {
		$duplicatedMobileNumbers = self::getDuplicatedMobileNumbersFromMKTLists($mktListIds);
		$duplicateMobileCount = array_sum(array_values($duplicatedMobileNumbers));

		$result = [
			'duplicate_mobile_count' => $duplicateMobileCount,
			'empty_mobile_count' => self::countCustomersWithoutMobileNumberFromMKTLists($mktListIds),
			'distributable_count' => self::countDistributableCustomersFromMKTLists($mktListIds, $campaignId)
		];

		return $result;
	}

	// Get list duplicated customers by mobile number across MKT Lists to export
	static function getDuplicatedCustomersByMobileNumber(array $mktListIds, bool $returnIds = false) {
		global $adb;
		$duplicatedMobileNumbers = self::getDuplicatedMobileNumbersFromMKTLists($mktListIds);
		$duplicatedNumbers = array_keys($duplicatedMobileNumbers);
		if (count($duplicatedNumbers) == 0) return [];
		$tempTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($mktListIds);
		
		$sql = "SELECT DISTINCT ce.setype AS module, ce.crmid as id, ce.label AS full_name, fnumber AS mobile, el.value AS email
			FROM vtiger_crmentity AS ce
			INNER JOIN {$tempTableCrmentityrel} AS rel ON (rel.relcrmid = ce.crmid AND rel.crmid IN (". join(', ', $mktListIds) ."))
			INNER JOIN vtiger_pbxmanager_phonelookup AS pl ON (pl.crmid = ce.crmid AND pl.fieldname = 'mobile')
			LEFT JOIN vtiger_emailslookup AS el ON (el.crmid = ce.crmid AND el.fieldid IN (SELECT fieldid FROM vtiger_field WHERE fieldname = 'email'))
			WHERE ce.deleted = 0 AND ce.setype IN('Leads', 'Contacts', 'CPTarget') AND pl.fnumber IN ('". join("', '", $duplicatedNumbers) ."')";
		$result = $adb->pquery($sql, []);
		$duplicatedCustomers = [];
		$duplicatedCustomerIds = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$duplicatedCustomers[] = decodeUTF8($row);
			$duplicatedCustomerIds[] = $row['id'];
		}

		if ($returnIds) {
			return $duplicatedCustomerIds;
		}
		else {
			return $duplicatedCustomers;
		}
	}

	// Update selected user ids to the specific campaign
	static function updateSelectedUsers($campaignId, array $userIds) {
		global $adb;
		if (empty($campaignId) || empty($userIds)) return;
		$sql = "UPDATE vtiger_campaign SET selected_user_ids = ? WHERE campaignid = ?";
		$adb->pquery($sql, [join(',', $userIds), $campaignId]);
	}

	static function getSelectedUserIds($campaignId) {
		global $adb;
		$sql = "SELECT selected_user_ids FROM vtiger_campaign WHERE campaignid = ?";
		$userIdsStr = $adb->getOne($sql, [$campaignId]);
		if (empty( $userIdsStr)) return [];

		$userIds = explode(',', $userIdsStr);
		return $userIds;
	}

	// Get selected user list from the specific campaign
	static function getSelectedUsers($campaignId) {
		global $adb;
		if (empty($campaignId)) return [];
		$selectedUserIds = self::getSelectedUserIds($campaignId);

		$userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
		$sql = "SELECT id, TRIM({$userNameSql}) AS full_name, email1 AS email FROM vtiger_users WHERE id IN (". join(', ', $selectedUserIds) .")";
		$result = $adb->pquery($sql, []);
		$users = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$users[] = [
				'id' => $row['id'],
				'name' => "{$row['full_name']} ({$row['email']})",
			];
		}

		return $users;
	}

	static function getSelectedUsersToEdit($campaignId) {
		$selectedUserIds = self::getSelectedUserIds($campaignId);
		$usersInfo = [];

		foreach ($selectedUserIds as $userId) {
			$usersInfo[$userId] = self::getSelectedUserInfoWithStatistics($userId, $campaignId);
		}

		return $usersInfo;
	}

	// Get User info and statistics to be inserted into the Selected Users table in Edit Telesales Campaign Form
	static function getSelectedUserInfoWithStatistics($userId, $campaignId) {
		global $adb;
		if (empty($userId) || empty($campaignId)) return [];

		// Get user info
		$userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
		$sqlUserInfo = "SELECT id, TRIM({$userNameSql}) AS full_name, email1 AS email FROM vtiger_users WHERE id = ?";
		$result = $adb->pquery($sqlUserInfo, [$userId]);
		$userInfo = $adb->fetchByAssoc($result);
		if (empty($userInfo)) return [];
		$userInfo['name'] = "{$userInfo['full_name']} ({$userInfo['email']})";

		// Get statistics
		$newCustomerStatus = self::getNewCustomerStatusForCampaign($campaignId);	// Customer with status = New means there is no calls yet
		$sqlBaseCustomersCount = "SELECT COUNT(cd.id)
			FROM vtiger_telesales_campaign_distribution AS cd
			INNER JOIN vtiger_crmentity AS ce ON (ce.crmid = cd.customer_id AND ce.deleted = 0)
			WHERE cd.assigned_user_id = ? AND cd.campaign_id = ?";

		$sqlCalledCustomersCount = $sqlBaseCustomersCount . "  AND cd.status != ?";
		$calledCustomersCount = $adb->getOne($sqlCalledCustomersCount, [$userId, $campaignId, $newCustomerStatus]);

		$sqlNotCalledCustomersCount = $sqlBaseCustomersCount . "  AND cd.status = ?";
		$notCalledCustomersCount = $adb->getOne($sqlNotCalledCustomersCount, [$userId, $campaignId, $newCustomerStatus]);

		$userInfo['statistics'] = [
			'all_distributed_count' => intval($calledCustomersCount + $notCalledCustomersCount),
			'already_called_count' => intval($calledCustomersCount),
			'not_called_count' => intval($notCalledCustomersCount),
		];

		return decodeUTF8($userInfo);
	}

	static function updateDistributionOptions($campaignId, array $distributionOptions) {
		global $adb;
		$sql = "UPDATE vtiger_campaign SET distribution_options = ? WHERE campaignid = ?";
		$adb->pquery($sql, [json_encode($distributionOptions), $campaignId]);
	}

	static function getDistributionOptions($campaignId) {
		global $adb;
		$sql = "SELECT distribution_options FROM vtiger_campaign WHERE campaignid = ?";
		$value = $adb->getOne($sql, [$campaignId]);
		$distributionOptions = json_decode($value, true) ?? [];
		return $distributionOptions;
	}

	static function getUserInfo($userId) {
		global $adb;
		$userNameSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
		$sql = "SELECT id, TRIM({$userNameSql}) AS full_name, email1 AS email FROM vtiger_users WHERE id = ?";
		$result = $adb->pquery($sql, [$userId]);
		$userInfo = $adb->fetchByAssoc($result);
		return $userInfo ?: [];
	}

	static function distribute($campaignId = '', array $mktListIds, array $selectedUserIds, array $distributionOptions, bool $saveDistributionResult = false) {
		$distributableCustomers = self::getDistributableCustomersFromMKTLists($campaignId, $mktListIds);
		$lastDistributedUserId = (!empty($campaignId)) ? self::getLastDistributedUserId($campaignId) : '';
		$distributeUserIndex = null;	// To track which user index to be considered
		$fullQuotaUserIds = [];			// To track users that reach the quota limit
		
		// Init distribution result array
		$distributionResult = [];

		foreach ($selectedUserIds as $userId) {
			$distributionResult[$userId] = [];
		}

		// Init statistic array
		$statistics = [
			'summary' => [
				'distributable_count' => count($distributableCustomers),
				'distributed_count' => 0,
				'skipped_count' => 0,
			],
			'detail_by_user' => []
		];
		
		foreach ($selectedUserIds as $userId) {
			$statistics['detail_by_user'][$userId] = [
				'current_data_count' => 0,
				'new_data_count' => 0,
				'final_data_count' => 0,
			];

			if (!empty($campaignId)) {
				$userInfo = self::getSelectedUserInfoWithStatistics($userId, $campaignId);
				$statistics['detail_by_user'][$userId]['current_data_count'] = $userInfo['statistics']['all_distributed_count'];
			}
		}

		// Find the right user index to distribute
		if (empty($lastDistributedUserId) || !in_array($lastDistributedUserId, $selectedUserIds)) {
			$distributeUserIndex = 0;
		}
		else {
			$distributeUserIndex = array_search($lastDistributedUserId, $selectedUserIds) + 1;

			// Reset index when it reachs the top
			if ($distributeUserIndex == count($selectedUserIds)) {
				$distributeUserIndex = 0;
			}
		}

		// Iterate each customer to find the suitable user to assign
		foreach ($distributableCustomers as $customerId => $customerInfo) {
			// Stop here when all users reached their quota limits or it will loop through subsequence records without doing anything!
			if (count($fullQuotaUserIds) == count($selectedUserIds)) {
				break;	// Save performance
			}

			// Auto distribute to assigned user
			if ($distributionOptions['auto_distribution_priority'] == 'user_currently_assigned_to_customer') {
				$ownerUserId = $customerInfo['main_owner_id'];

				if (!empty($ownerUserId) && in_array($ownerUserId, $selectedUserIds)) {
					if (self::checkUserQuota($ownerUserId, $statistics, count($distributionResult[$ownerUserId]), $distributionOptions)) {
						$distributionResult[$ownerUserId][] = $customerInfo;
						$lastDistributedUserId = $ownerUserId;
						continue;	// It's done, pass to next customer
					}
				}
			}

			// Auto distribute to user with latest telesales outbound call
			if ($distributionOptions['auto_distribution_priority'] == 'user_has_latest_telesales_call_to_customer') {
				$callerUserId = self::getLatestTelesalesCallUserId($customerId, $customerInfo['customer_type']);

				if (!empty($callerUserId) && in_array($callerUserId, $selectedUserIds)) {
					if (self::checkUserQuota($callerUserId, $statistics, count($distributionResult[$callerUserId]), $distributionOptions)) {
						$distributionResult[$callerUserId][] = $customerInfo;
						$lastDistributedUserId = $callerUserId;
						continue;	// It's done, pass to next customer
					}
				}
			}

			// Distribute to users in list by distribution options
			$done = false;

			while (!$done) {
				// Reset distribute user index when it reached the max list length
				if ($distributeUserIndex == count($selectedUserIds)) {
					$distributeUserIndex = 0;
				}

				// Loop until we found the suitable user to assign data
				for ($i = 0; $i < count($selectedUserIds); $i++) {
					// Skip until we get the right user index to distribute
					if ($i != $distributeUserIndex) {
						continue;
					}

					$userId = $selectedUserIds[$i];
					$distributeUserIndex++;

					// Skip this user when it is in the full quota list
					if (in_array($userId, $fullQuotaUserIds)) {
						continue;
					}

					// Check user quota
					if (!self::checkUserQuota($userId, $statistics, count($distributionResult[$userId]), $distributionOptions)) {
						// Add this user into the full quota list
						if (!in_array($userId, $fullQuotaUserIds)) $fullQuotaUserIds[] = $userId;

						// Then skip this user when it reached the distribution limit
						continue;
					}

					// Distribute current customer to current user
					$distributionResult[$userId][] = $customerInfo;
					$lastDistributedUserId = $userId;
					$done = true;

					// Break here as we found the suitable user for the customer
					break;
				}

				// Stop here if all users reached their quota limits or it will be stuck in the while loop forever!
				if (count($fullQuotaUserIds) == count($selectedUserIds)) {
					break;
				}
			}
		}

		// Save distribution result
		if ($saveDistributionResult) {
			// Insert distribution record for each customer
			foreach ($distributionResult as $userId => $customers) {
				foreach ($customers as $customer) {
					self::insertNewDistribution($campaignId, $customer['mkt_list_id'], $customer['customer_id'], $customer['customer_type'], $userId);
				}
			}

			// Save last distributed user id so that the distribute process can restart next time
			self::saveLastDistributedUserId($campaignId, $lastDistributedUserId);
		}

		// Calculate final summary
		foreach ($distributionResult as $userId => $customers) {
			$statistics['detail_by_user'][$userId]['new_data_count'] = count($customers);
			$statistics['detail_by_user'][$userId]['final_data_count'] = $statistics['detail_by_user'][$userId]['current_data_count'] + $statistics['detail_by_user'][$userId]['new_data_count'];
			$statistics['summary']['distributed_count'] += $statistics['detail_by_user'][$userId]['new_data_count'];
		}

		$statistics['summary']['skipped_count'] = $statistics['summary']['distributable_count'] - $statistics['summary']['distributed_count'];

		return $statistics;
	}

	// Get distributable customers across MKT List (unique customers with mobile number and not inserted into distribution table yet)
	protected static function getDistributableCustomersFromMKTLists($campaignId, array $mktListIds) {
		global $adb;
		$tempTableCrmentityrel = CRMEntity::setupTemporaryTableCrmentityrel($mktListIds);

		// Keyword DISTINCT is not applicable as column mkt_list_id make all rows distinct to each other even when the customer_id is the same
		$sql = "SELECT ce.crmid AS customer_id, ce.setype AS customer_type, ce.main_owner_id, rel.crmid AS mkt_list_id
			FROM vtiger_crmentity AS ce
			INNER JOIN {$tempTableCrmentityrel} AS rel ON (rel.relcrmid = ce.crmid AND rel.crmid IN (". join(', ', $mktListIds) ."))
			INNER JOIN vtiger_pbxmanager_phonelookup AS pl ON (pl.crmid = ce.crmid AND pl.fieldname = 'mobile')
			WHERE ce.deleted = 0 AND ce.setype IN('Leads', 'Contacts', 'CPTarget') AND ce.crmid NOT IN (
				SELECT customer_id FROM vtiger_telesales_campaign_distribution WHERE campaign_id = ?
			)";

		// Modified by Vu Mai on 2023-02-16 remove customer duplicated mobile
		$duplicatedCustomersIds = self::getDuplicatedCustomersByMobileNumber($mktListIds, true);

		if (!empty($duplicatedCustomersIds)) {
			$sql .= " AND ce.crmid NOT IN (". join(', ', $duplicatedCustomersIds) .")";
		}
		// End Vu Mai

		$result = $adb->pquery($sql, [$campaignId]);
		$customers = [];

		while ($row = $adb->fetchByAssoc($result)) {
			if (!isset($customers[$row['customer_id']])) { // Use customer_id as key to help remove duplicate records across MKT Lists
				$customers[$row['customer_id']] = $row;
			}
		}

		return $customers;
	}

	protected static function getLatestTelesalesCallUserId($customerId, $customerType) {
		global $adb;
		if (empty($customerId) || empty($customerType)) return null;
		if (!in_array($customerType, ['CPTarget', 'Leads', 'Contacts'])) return null;

		if ($customerType == 'CPTarget') {
			$sql = "SELECT ae.main_owner_id AS caller_user_id
				FROM vtiger_activity AS a
				INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = a.activityid AND ae.deleted = 0)
				INNER JOIN vtiger_seactivityrel AS rel ON (rel.activityid = a.activityid AND rel.crmid = ?)
				INNER JOIN vtiger_campaign AS c ON (c.campaignid = a.related_campaign AND c.campaigntype = 'Telesales')
				ORDER BY ae.createdtime DESC
				LIMIT 1";
		}

		if ($customerType == 'Leads') {
			$sql = "SELECT ae.main_owner_id AS caller_user_id
				FROM vtiger_activity AS a
				INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = a.activityid AND ae.deleted = 0)
				INNER JOIN vtiger_campaign AS c ON (c.campaignid = a.related_campaign AND c.campaigntype = 'Telesales')
				WHERE a.related_lead = ?
				ORDER BY ae.createdtime DESC
				LIMIT 1";
		}

		if ($customerType == 'Contacts') {
			$sql = "SELECT ae.main_owner_id AS caller_user_id
				FROM vtiger_activity AS a
				INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = a.activityid AND ae.deleted = 0)
				INNER JOIN vtiger_cntactivityrel AS rel ON (rel.activityid = a.activityid AND rel.contactid = ?)
				INNER JOIN vtiger_campaign AS c ON (c.campaignid = a.related_campaign AND c.campaigntype = 'Telesales')
				ORDER BY ae.createdtime DESC
				LIMIT 1";
		}

		$callerUserId = $adb->getOne($sql, [$customerId]);
		return $callerUserId;
	}

	protected static function checkUserQuota($userId, array $statistics, $distributedCustomersCount, array $distributionOptions) {
		$totalDistributedCustomersCount = $statistics['detail_by_user'][$userId]['current_data_count'] + $distributedCustomersCount;

		// Check quota limit
		if ($distributionOptions['apply_quota'] && $distributionOptions['quota_limit'] > 0) {
			if ($totalDistributedCustomersCount >= $distributionOptions['quota_limit']) {
				return false;	// User reached the quota limit
			}
		}

		// Check manual distribution limit
		if ($distributionOptions['distribution_method'] == 'manual' && !empty($distributionOptions['manual_distribution_config'])) {
			if ($totalDistributedCustomersCount >= $distributionOptions['manual_distribution_config'][$userId]) {
				return false;	// User reached the manual distribution limit
			}
		}

		return true;
	}

	protected static function getLastDistributedUserId($campaignId) {
		global $adb;
		if (empty($campaignId)) return null;
		$sql = "SELECT last_distributed_user FROM vtiger_campaign WHERE campaignid = ?";
		$lastDistributedUserId = $adb->getOne($sql, [$campaignId]);
		return $lastDistributedUserId;
	}

	protected static function saveLastDistributedUserId($campaignId, $lastDistributedUserId) {
		global $adb;
		if (empty($campaignId)) return;
		$sql = "UPDATE vtiger_campaign SET last_distributed_user = ? WHERE campaignid = ?";
		$adb->pquery($sql, [$lastDistributedUserId, $campaignId]);
	}

	protected static function insertNewDistribution($campaignId, $mktListId, $customerId, $customerType, $userId) {
		global $adb;

		// Insert new row
		try {
			$sql = "INSERT INTO vtiger_telesales_campaign_distribution(campaign_id, mkt_list_id, customer_id, customer_type, assigned_user_id, status) VALUES(?, ?, ?, ?, ?, ?)";
			$status = self::getNewCustomerStatusForCampaign($campaignId);	// Status may null when user doesn't config for this Campaign Purpose yet
			$adb->pquery($sql, [$campaignId, $mktListId, $customerId, $customerType, $userId, $status]);
		}
		catch (Exception $ex) {
			saveLog('PLATFORM', '[Campaigns_Telesales_Model::insertNewDistribution] Query error:' . $ex->getMessage(), $ex->getTrace());
		}
	}

	static function saveRelatedMKTLists($campaignId, array $mktListIds) {
		global $adb;
		if (empty($campaignId) || empty($mktListIds)) return;

		foreach ($mktListIds as $mktListId) {
			try {
				$sqlCheck = "SELECT 1 FROM vtiger_crmentityrel WHERE crmid = ? AND relcrmid = ?";
				$exists = $adb->getOne($sqlCheck, [$campaignId, $mktListId]);

				if (!$exists) {
					$sqlInsert = "INSERT INTO vtiger_crmentityrel(crmid, module, relcrmid, relmodule) VALUES(?, ?, ?, ?)";
					$adb->pquery($sqlInsert, [$campaignId, 'Campaigns', $mktListId, 'CPTargetList']);
				}
			}
			catch (Exception $ex) {
				saveLog('PLATFORM', '[Campaigns_Telesales_Model::insertNewDistribution] Query error:' . $ex->getMessage(), $ex->getTrace());
			}
		}
	}

	static function removeMKTList($mktListId, $campaignId) {
		global $adb;
		$mktListInfo = self::getMKTListInfoWithStatistics($mktListId, $campaignId);
		$result = ['success' => true];

		// Cannot remove a MKT List when it has distributed customers
		if ($mktListInfo['statistics']['distributed_customers_count'] > 0) {
			$result = [
				'success' => false,
				'code' => 'CANNOT_REMOVE',
				'message' => vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_PANEL_SELECT_MKT_LIST_REMOVE_MKT_LIST_WITH_DISTRIBUTED_CUSTOMERS_ERROR_MSG', 'Campaigns')
			];

			return $result;
		}

		// Delete relationship between specified specified campaign and MKT List
		$sql = "DELETE FROM vtiger_crmentityrel WHERE crmid = ? AND relcrmid = ?";
		$adb->pquery($sql, [$campaignId, $mktListId]);

		return $result;
	}

	static function transferData($campaignId, $sourceUserId, $targetUserId, $dataType, $transferNumber = 'all', bool $removeSourceUser = false) {
		global $adb;
		$sourceUserInfo = self::getSelectedUserInfoWithStatistics($sourceUserId, $campaignId);
		$result = ['success' => true];

		// Action is Transfer, but the source user has no not called customers to transfer
		if ($dataType == 'not_called_customers' && $sourceUserInfo['statistics']['not_called_count'] == 0) {
			$result = [
				'success' => false,
				'code' => 'NO_NOT_CALLED_CUSTOMERS',
				'message' => vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_TRANSFER_DATA_MODAL_SOURCE_USER_HAS_NO_VALID_DATA_ERROR_MSG', 'Campaigns', ['user_name' => $sourceUserInfo['full_name']]),
			];

			return $result;
		}

		// Transfer data from source user to target user
		$newCustomerStatus = self::getNewCustomerStatusForCampaign($campaignId);
		$sql = "UPDATE vtiger_telesales_campaign_distribution SET assigned_user_id = ? WHERE assigned_user_id = ? AND campaign_id = ?";
		$params = [$targetUserId, $sourceUserId, $campaignId];

		if ($dataType == 'not_called_customers') {
			$sql .= " AND status = ?";
			$params[] = $newCustomerStatus;

			if ($transferNumber != 'all' && intval($transferNumber) > 0) {
				$transferNumber = $adb->sql_escape_string($transferNumber);	// Prevent SQL injection
				$sql .= " LIMIT {$transferNumber}";
			}
		}

		$adb->pquery($sql, $params);

		// Add target user to selected users
		$selectedUserIds = self::getSelectedUserIds($campaignId);

		if (!in_array($targetUserId, $selectedUserIds)) {
			$selectedUserIds[] = $targetUserId;
		}

		// Remove source user
		if ($removeSourceUser) {			
			$selectedUserIds = array_diff($selectedUserIds, [$sourceUserId]);
		}

		// Update final selected users
		self::updateSelectedUsers($campaignId, $selectedUserIds);

		return $result;
	}

	// Added by Vu Mai on 2023-02-15
	static function currentUserCanCreateOrRedistribute() {
		global $businessManagersConfig;
		$currentUsersModel = Users_Record_Model::getCurrentUserModel();
		$isTelesaleManager = in_array('Users:' . $currentUsersModel->getId(), $businessManagersConfig['telesales_campaign']);

		if ($currentUsersModel->isAdminUser() || $isTelesaleManager) {
			return true;
		}

		return false;
	}

	// Added by Vu Mai on 2023-03-03
	static function getTelesalesCampaignList() {
		global $adb;

		$sql = "SELECT campaignid AS id, campaignname AS name 
		FROM vtiger_campaign AS c
		INNER JOIN vtiger_crmentity as e ON e.crmid = c.campaignid AND e.deleted = 0
		WHERE c.campaigntype = 'Telesales'";
		$result = $adb->pquery($sql, []);
		$telesalesCampaignList = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$telesalesCampaignList[$row['id']]['id'] = $row['id'];
			$telesalesCampaignList[$row['id']]['name'] = $row['name'];
		}

		return $telesalesCampaignList;
	}
}