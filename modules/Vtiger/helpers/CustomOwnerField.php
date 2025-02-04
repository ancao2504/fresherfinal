<?php

/*
	Class Vtiger_CustomOwnerField_Helper
	Author: Hieu Nguyen
	Date: 2019-05-22
	Purpose: To provide util functions for handling owner field logic
*/

class Vtiger_CustomOwnerField_Helper {

	const USER_ID_PREFIX = 'Users:';
	const GROUP_ID_PREFIX = 'Groups:';

	static function setOwner(&$entityData) {
		$currentOwnerId = $_REQUEST['current_owner_id'];
		$currentOwnerIdsHash = $_REQUEST['current_assignees_hash'];
		$requestOwnerIds = $entityData->get('assigned_user_id');
		$newOwnerIds = explode(',', $requestOwnerIds);
		$newOwnerIdsHash = self::generateOwnerIdsHash($newOwnerIds);
		$newOwnerId = $currentOwnerId;
		$mainOwnerId = '-1';    // Default value when no main owner specified

		// Workarround to prevent override main_owner_id when record is created from workflow
		if ($entityData->get('owner_populated') === true) {
			return; // Do nothing as owner values are set already
		}

		// Process when user update the owners
		if (strpos($requestOwnerIds, 'Users') !== false || strpos($requestOwnerIds, 'Groups') !== false) {
			// User selected only 1 owner
			if (count($newOwnerIds) == 1) {
				$newOwnerId = self::getOwnerIdFromRequest($newOwnerIds[0]);

				// The selected owner will also be the main owner if it is a user
				if (self::isOwnerUserId($newOwnerIds[0])) {
					$mainOwnerId = $newOwnerId;
				}
			}
			// User selected multiple owners
			else {
				// Find matching group to reuse
				$existingGroupId = self::findCustomGroup($newOwnerIdsHash);

				// Matched group found
				if (!empty($existingGroupId)) {
					$newOwnerId = $existingGroupId;
				}
				// No matched group found
				else {
					// Create a new custom group
					$customGroupId = self::createCustomGroup($newOwnerIds, $newOwnerIdsHash);
					$newOwnerId = $customGroupId;
				}

				// Set the first owner on the list as the main owner if it is a user
				if (self::isOwnerUserId($newOwnerIds[0])) {
					$mainOwnerId = self::getOwnerIdFromRequest($newOwnerIds[0]);
				}
			}

			// Finally, set new owner id and main owner id
			$entityData->set('assigned_user_id', $newOwnerId);
			$entityData->set('main_owner_id', $mainOwnerId);
		}

		// Set main owner id for imported records
		if ($_REQUEST['view'] == 'Import') {
			$entityData->set('main_owner_id', $entityData->get('assigned_user_id'));
		}
		// Set main owner in case record is created from workflow or from custom code
		else if (empty($entityData->getId()) && strpos($requestOwnerIds, ':') === false && $mainOwnerId == '-1') {
			// [CustomOwnerField] Modified by Phu Vo on 2019.12.02 to fix main_owner_id set to 0 when custom code didn't assign assigned_user_id
			// CRMEntity will check and insert record to db with current user id as assigned_user_id but entityData still empty
			if (empty($entityData->get('assigned_user_id'))) $entityData->set('assigned_user_id', vglobal('current_user')->id);
			// End Phu Vo

			$entityData->set('main_owner_id', $entityData->get('assigned_user_id'));
		}
	}

	static function updateMainOwner($entityData) {
		global $adb;
		$recordId = $entityData->getId();
		$mainOwnerId = $entityData->get('main_owner_id');

		$sql = "UPDATE vtiger_crmentity SET main_owner_id = ? WHERE crmid = ?";
		$params = [$mainOwnerId, $recordId];
		$adb->pquery($sql, $params);
	}

	static function removeUnassignableUsers(&$recordModel) {
		global $current_user;
		$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($current_user->roleid);
		$userRecordModel = Users_Privileges_Model::getCurrentUserModel();
		$assignedUserId = $recordModel->get('assigned_user_id');
		if (empty($assignedUserId)) return;

		$assignedUsers = Vtiger_Owner_UIType::getCurrentOwners($assignedUserId, false);
		$remainingUsers = [];

		if ($currentUserRoleModel->get('allowassignedrecordsto') === '2') {
			$assignableUsers = $userRecordModel->getSameLevelUsersWithSubordinates();
		}
		else if ($currentUserRoleModel->get('allowassignedrecordsto') === '3') {
			$assignableUsers = $userRecordModel->getRoleBasedSubordinateUsers();
		}

		// Remove unassignable users
		if (!empty($assignableUsers) && !empty($assignedUsers)) {
			$assignableUserIds = array_keys($assignableUsers);
			
			foreach ($assignedUsers as $index => $userInfo) {
				$userId = self::getOwnerIdFromRequest($userInfo['id']);

				if (in_array($userId, $assignableUserIds)) {
					$remainingUsers[] = $userInfo;
				}
			}
		}

		if (!empty($remainingUsers)) {
			$recordModel->set('assigned_user_id', $remainingUsers);
			$recordModel->set('main_owner_id', self::getOwnerIdFromRequest($remainingUsers[0]['id']));
		}
		else {
			$recordModel->set('assigned_user_id', '');
			$recordModel->set('main_owner_id', '');
		}
	}

	static function generateOwnerIdsHash($ownerIds) {
		sort($ownerIds);
		return md5(join(',', $ownerIds));
	}

	static function generateOwnerUserName($fullName, $email) {
		return "{$fullName} ({$email})";
	}

	static function isOwnerUserId($ownerId) {
		if (strpos($ownerId, self::USER_ID_PREFIX) !== false) {
			return true;
		}

		return false;
	}

	static function isOwnerGroupId($ownerId) {
		if (strpos($ownerId, self::GROUP_ID_PREFIX) !== false) {
			return true;
		}

		return false;
	}

	static function getOwnerIdFromRequest($ownerId) {
		// This is a user
		if (self::isOwnerUserId($ownerId)) {
			$ownerId = str_replace(self::USER_ID_PREFIX, '', $ownerId);
		}
		// This is a group
		else {
			$ownerId = str_replace(self::GROUP_ID_PREFIX, '', $ownerId);
		}

		return $ownerId;
	}

	static function getOwnerIdsFromRequest($ownerIdsString) {
		$ownerIdsStringArr = explode(',', $ownerIdsString);
		$ownerIds = [];

		foreach ($ownerIdsStringArr as $ownerIdString) {
			$ownerIds[] = self::getOwnerIdFromRequest($ownerIdString);
		}

		return $ownerIds;
	}

	static function getOwnerGroup($groupId, $groupLabel = '') {
		$text = $groupLabel;

		if (empty($text)) {
			$text = getOwnerName($groupId);
		}

		return ['id' => self::GROUP_ID_PREFIX . $groupId, 'text' => trim($text)];
	}

	static function getUserNameConcatForSql($tableAlias = '') {
		$firstNameCol = 'first_name';
		$lastNameCol = 'last_name';

		if (!empty($tableAlias)) {
			$firstNameCol = "{$tableAlias}.{$firstNameCol}";
			$lastNameCol = "{$tableAlias}.{$lastNameCol}";
		}

		$params = [
			'first_name' => $firstNameCol, 
			'last_name' => $lastNameCol
		];
		
		$concatSql = getSqlForNameInDisplayFormat($params, 'Users');
		return $concatSql;
	}

	static function getOwnerUser($userId, $userLabel = '', $withUniqueInfo = true) {
		$text = $userLabel;

		if (empty($text)) {
			global $adb;

			$userNameConcatSql = self::getUserNameConcatForSql();
			$sql = "SELECT id, TRIM({$userNameConcatSql}) AS name, email1 AS email
				FROM vtiger_users WHERE id = ?";
			$result = $adb->pquery($sql, [$userId]);
			$row = $adb->fetchByAssoc($result);
			$text = decodeUTF8($row['name']);

			if ($withUniqueInfo) {
				$text = self::generateOwnerUserName($row['name'], $row['email']);
			}
		}
		
		return ['id' => self::USER_ID_PREFIX . $userId, 'text' => trim($text)];
	}

	static function getOwnerList($keyword, $userOnly = false, $assignableUsersOnly = false, $skipCurrentUser = false, $skipUsers = [], $skipGroups = []) {
		global $adb, $current_user;

		// Fetch all users that can access the target module
		$userNameConcatSql = self::getUserNameConcatForSql();
		$sql = "SELECT DISTINCT id, TRIM({$userNameConcatSql}) AS name, email1 AS email
			FROM vtiger_users
			WHERE deleted = 0 AND status = 'Active' AND TRIM({$userNameConcatSql}) LIKE ? ";
		$params = ["%{$keyword}%"];

		// Check assignable users based on role config
		if ($assignableUsersOnly) {
			$currentUserRoleModel = Settings_Roles_Record_Model::getInstanceById($current_user->roleid);
			$userRecordModel = Users_Privileges_Model::getCurrentUserModel();
	
			if ($currentUserRoleModel->get('allowassignedrecordsto') === '2') {
				$assignableUsers = $userRecordModel->getSameLevelUsersWithSubordinates();
			}
			else if ($currentUserRoleModel->get('allowassignedrecordsto') === '3') {
				$assignableUsers = $userRecordModel->getRoleBasedSubordinateUsers();
			}

			if (!empty($assignableUsers)) {
				$assignableUserIds = array_keys($assignableUsers);
				$sql .= "AND id IN ('". join("','", $assignableUserIds) ."') ";
			}
		}

		// Skip current user
		if ($skipCurrentUser) {
			$sql .= "AND id != ? ";
			$params[] = $current_user->id;
		}

		// Skip users in the list
		if (!empty($skipUsers)) {
			$sql .= "AND id NOT IN (". join(',', $skipUsers) .") ";
		}
		
		// Execute query
		$result = $adb->pquery($sql, $params);
		$userList = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row['name'] = decodeUTF8($row['name']);
			$userList[] = self::getOwnerUser($row['id'], self::generateOwnerUserName($row['name'], $row['email'])); 
		}

		// Fetch all non-custom groups
		if (!$userOnly) {
			$sql = "SELECT groupid AS id, groupname AS name FROM vtiger_groups WHERE is_custom = 0 AND groupname LIKE ? ";
			$params = ["%{$keyword}%"];

			// Skip groups in the list
			if (!empty($skipGroups)) {
				$sql .= "AND id NOT IN (". join(',', $skipGroups) .") ";
			}

			$result = $adb->pquery($sql, $params);
			$groupList = [];

			while ($row = $adb->fetchByAssoc($result)) {
				$groupList[] = self::getOwnerGroup($row['id'], decodeUTF8($row['name']));
			}
		}

		// Respond
		$result = [];

		if (!empty($userList)) {
			$result[] = ['text' => 'Users', 'children' => $userList];
		}

		if (!empty($groupList)) {
			$result[] = ['text' => 'Groups', 'children' => $groupList];
		}

		return $result;
	}

	static function getGroupMemberIdsHash($groupId) {
		global $adb;
		$sql = "SELECT member_ids_hash FROM vtiger_groups WHERE groupid = ?";
		$memberIdsHash = $adb->getOne($sql, [$groupId]);

		return $memberIdsHash;
	}

	static function isCustomGroup($groupId) {
		global $adb;
		$sql = "SELECT is_custom FROM vtiger_groups WHERE groupid = ?";
		$isCustom = $adb->getOne($sql, [$groupId]);

		return $isCustom == 1;
	}

	static function findCustomGroup($memberIdsHash) {
		global $adb;
		$sql = "SELECT groupid FROM vtiger_groups WHERE is_custom = 1 AND member_ids_hash = ?";
		$groupId = $adb->getOne($sql, [$memberIdsHash]);

		return $groupId;
	}

	static function createCustomGroup($memberIds, $memberIdsHash) {
		sort($memberIds);

		$groupRecordModel = new Settings_Groups_Record_Model();
		$groupRecordModel->set('groupname', 'Custom Group: '. join(', ', $memberIds));
		$groupRecordModel->set('description', 'Custom Group');
		$groupRecordModel->set('group_members', $memberIds);
		$groupRecordModel->set('is_custom', 1);
		$groupRecordModel->set('member_ids_hash', $memberIdsHash);
		$groupRecordModel->save();

		return $groupRecordModel->get('groupid');
	}

	static function deleteCustomGroupIfNotUsed($groupId) {
		global $adb;
		
		if (empty($groupId)) return;

		// Added by Phu Vo to backup custom group to use later
		if (empty($GLOBALS['deleted_custom_groups'])) $GLOBALS['deleted_custom_groups'] = [];

		$GLOBALS['deleted_custom_groups'][$groupId] = [
			'id' => $groupId,
			'owners' => Vtiger_Owner_UIType::getCurrentOwners($groupId, false),
			'member_ids' => getGroupMemberIds($groupId),
			'label' => Vtiger_Owner_UIType::getCurrentOwnersForDisplay($groupId, false),
		];
		// End Phu Vo

		$sql = "DELETE FROM vtiger_groups WHERE is_custom = 1 AND groupid = ? 
			AND groupid NOT IN (SELECT DISTINCT smownerid FROM vtiger_crmentity WHERE deleted = 0)";
		$adb->pquery($sql, [$groupId]);
	}

	// Check if the provided user is one of the assigned users of a record
	static function isInAssignedUsers($userId, $recordAssignedUserId) {
		static $cache = [];
		$cacheKey = $userId .'-'. $recordAssignedUserId;
		if (isset($cache[$cacheKey])) return $cache[$cacheKey];

		$assignedUsers = Vtiger_Owner_UIType::getCurrentOwners($recordAssignedUserId, false);
		$result = false;

		foreach ($assignedUsers as $userInfo) {
			if ($userInfo['id'] == "Users:{$userId}") {
				$result = true;
				break;
			}
		}

		$cache[$cacheKey] = $result;
		return $result;
	}

	/**
	 * Get Owner Id from request string
	 * @param String $string Request string Owner
	 * @return Number Owner Id (User / Normal Group / Custom Group)
	 * @author Phu Vo (2019.07.09)
	 */
	static function findOwnerIdFromRequestString($string) {
		$ownerIds = explode(',', $string);
		$ownerId = false;

		if (count($ownerIds) === 1) {
			$ownerInfo = explode(':', $ownerIds[0]);
			$ownerId = $ownerInfo[1];
		}
		else {
			$hash = self::generateOwnerIdsHash($ownerIds);
			$ownerId = self::findCustomGroup($hash);
		}

		return $ownerId;
	}
}