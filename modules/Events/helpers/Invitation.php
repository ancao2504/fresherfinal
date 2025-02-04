<?php

/*
	Invitation_Helper
	Author: Hieu Nguyen
	Date: 2019-11-22
	Purpose: to provide util functions for invitation handling
*/

class Events_Invitation_Helper {

	static function saveInvitations($eventId, $inviteeIds, $eventData = [], $isRecurrence = false) {
		global $adb, $current_user; // Modified by Vu Mai on 2022-12-14 to change logic to auto accept with current user instead of main owner
		if (empty($eventId)) return;
		$status = 'Queued';

		// Finished event and mandatory event will only save invitation as accepted
		if (in_array($eventData['eventstatus'], ['Held', 'Not Held']) || $eventData['mandatory'] == '1') {
			$status = 'Accepted';
		}

		// Remove all invitees in case user empty the invitee list
		if (!is_array($inviteeIds) || empty($inviteeIds)) {
			$adb->pquery('DELETE FROM vtiger_invitees WHERE activityid = ?', [$eventId]);
			return;
		}

		// Remove all invitees that are not in current list any more
		$sql = "DELETE FROM vtiger_invitees 
			WHERE activityid = ? AND CONCAT(invitee_type, ':', inviteeid) NOT IN ('". join("', '", $inviteeIds) ."')";
		$adb->pquery($sql, [$eventId]);

		// Insert new invitees into the queue, existing invitees will be skips according to the unique key check
		foreach ($inviteeIds as $value) {
			list($inviteeType, $inviteeId) = explode(':', $value);

			// Modified by Tung Nguyen on 2022-10-31: Ticket #6778 - Auto accept invite when Invitee is record main owner
			$statusEachInvite = $status;

			// Modified by Vu Mai on 2022-12-14 to change logic to auto accept with current user instead of main owner
			if ($inviteeType == 'Users' &&  $inviteeId == $current_user->id) {
				$statusEachInvite = 'Accepted';
			}
			// End Vu Mai

			$sql = "INSERT INTO vtiger_invitees(activityid, inviteeid, invitee_type, status, is_recurrence) VALUES (?, ?, ?, ?, ?)";
			$params = [$eventId, $inviteeId, $inviteeType, $statusEachInvite, (int)$isRecurrence];
			$adb->pquery($sql, $params);
			// Ended by Tung Nguyen
		}
	}

	static function updateInvitationStatus($inviteeId, $inviteeType, $eventId, $status) {
		global $adb;

		// Update status of target invitation
		$sql = "UPDATE vtiger_invitees SET status = ? WHERE activityid = ? AND inviteeid = ? AND invitee_type = ?";
		$adb->pquery($sql, [$status, $eventId, $inviteeId, $inviteeType]);

		// Update status of invitations from the recurrence events
		$sql = "UPDATE vtiger_invitees SET status = ? WHERE inviteeid = ? AND invitee_type = ? 
			AND activityid IN (SELECT recurrenceid FROM vtiger_activity_recurring_info WHERE activityid = ?)";
		$adb->pquery($sql, [$status, $inviteeId, $inviteeType, $eventId]);

		// Added by Phu Vo on 2021.01.04 to update accepted status in notification as well
		if ($status == 'Accepted' && $inviteeType == 'Users') {
			require_once('modules/Calendar/CalendarCommon.php');
			acceptInviteNotification($inviteeId, $eventId);
		}
		// End Phu Vo
	}

	// Get invitees for edit in EditView
	static function getInviteesForEdit($eventId, $inviteeType) {
		$invitees = self::getInviteesForDisplay($eventId, $inviteeType);
		$formattedInvitees = [];

		foreach ($invitees as $invitee) {
			$formattedInvitees[] = [
				'id' => ($inviteeType == 'Users') ? 'Users:' . $invitee['id'] : $invitee['id'],
				'text' => ($inviteeType == 'Users') ? $invitee['name'] . ' ('. $invitee['email'] .')' : $invitee['name'],
			];
		}

		return $formattedInvitees;
	}

	// Get invitees for display in DetailView and ListView
	static function getInviteesForDisplay($eventId, $inviteeType) {
		global $adb;

		if ($inviteeType == 'Contacts') {
			$nameColumn = getSqlForNameInDisplayFormat(['firstname' => 'c.firstname', 'lastname' => 'c.lastname'], 'Contacts');
			$sql = "SELECT c.contactid AS id, {$nameColumn} AS name, c.email, i.status, i.failed_reason
				FROM vtiger_contactdetails AS c
				INNER JOIN vtiger_crmentity AS ce ON (ce.crmid = c.contactid AND ce.setype = 'Contacts' AND ce.deleted = 0)
				INNER JOIN vtiger_invitees AS i ON (i.inviteeid = c.contactid AND i.activityid = ? AND i.invitee_type = ?)";
		}

		if ($inviteeType == 'Users') {
			$nameColumn = getSqlForNameInDisplayFormat(['first_name' => 'u.first_name', 'last_name' => 'u.last_name'], 'Users');
			$sql = "SELECT u.id, {$nameColumn} AS name, u.email1 AS email, i.status, i.failed_reason
				FROM vtiger_users AS u
				INNER JOIN vtiger_invitees AS i ON (i.inviteeid = u.id AND i.activityid = ? AND i.invitee_type = ?)";
		}
		
		$params = [$eventId, $inviteeType];
		$result = $adb->pquery($sql, $params);
		$invitees = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$invitees[] = decodeUTF8($row);
		}

		return $invitees;
	}

	static function isInvitee($inviteeId, $inviteeType, $eventId) {
		global $adb;
		$sql = "SELECT 1 FROM vtiger_invitees WHERE activityid = ? AND inviteeid = ? AND invitee_type = ?";
		$isInvitee = $adb->getOne($sql, [$eventId, $inviteeId, $inviteeType]);

		return !empty($isInvitee);
	}

	static function isInvitationAccepted($inviteeId, $inviteeType, $eventId) {
		global $adb;
		$sql = "SELECT 1 FROM vtiger_invitees WHERE activityid = ? AND inviteeid = ? AND invitee_type = ? AND status = 'Accepted'";
		$isAccepted = $adb->getOne($sql, [$eventId, $inviteeId, $inviteeType]);

		return !empty($isAccepted);
	}

	static function getInviteeInfo($inviteeId, $inviteeType) {
		global $adb;

		if ($inviteeType == 'Users') {
			$nameConcatSql = getSqlForNameInDisplayFormat(['first_name' => 'first_name', 'last_name' => 'last_name'], 'Users');
			$sql = "SELECT TRIM({$nameConcatSql}) AS name, email1 AS email, language, time_zone
				FROM vtiger_users WHERE deleted = 0 AND status = 'Active' AND id = ?";
		}

		if ($inviteeType == 'Contacts') {
			$nameConcatSql = getSqlForNameInDisplayFormat(['firstname' => 'firstname', 'lastname' => 'lastname'], 'Contacts');
			$sql = "SELECT TRIM({$nameConcatSql}) AS name, email FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity AS e ON (e.crmid = contactid AND e.deleted = 0 AND e.setype = 'Contacts')
				WHERE contactid = ?";
		}

		$result = $adb->pquery($sql, [$inviteeId]);
		$inviteeInfo = $adb->fetchByAssoc($result);

		if (empty($inviteeInfo)) return null;
		$inviteeInfo['id'] = $inviteeId;
		$inviteeInfo['type'] = $inviteeType;

		return decodeUTF8($inviteeInfo);
	}

	static function getInvitationEmailBody($eventRecordModel, array $invitationData, array $inviteeInfo) {
		require_once('include/utils/utils.php');
		global $adb;

		$inviteeId = $inviteeInfo['id'];
		$inviteeType = $inviteeInfo['type'];
		$startDate = new DateTimeField($invitationData['st_date_time']);
		$endDate = new DateTimeField($invitationData['end_date_time']);
		$creatorId = $eventRecordModel->get('createdby');
		$creatorName = getUserFullName($creatorId);

		if ($inviteeType == 'Users') {
			$dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($inviteeId, 'Users');
		}
		else {
			$dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($creatorId, 'Users');
		}

		// Get email template
		$query = "SELECT body FROM vtiger_emailtemplates WHERE templatename = ? AND systemtemplate = 1";
		$templateBody = $adb->getOne($query, ['Event Invitation']);

		// Replace variables
		$body = $templateBody;
		$body = str_replace('$invitee_name$', $inviteeInfo['name'], $body);
		$body = str_replace('$events-date_start$', $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		$body = str_replace('$events-due_date$', $endDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		$body = str_replace('$events-contactid$', $invitationData['contact_name'], $body);
		$body = str_replace('$creator_name$', $creatorName, $body);
		$body = self::addAcceptInvitationLink($body, $eventRecordModel->getId(), $inviteeId, $inviteeType);
		$body = replaceRecordDetailLink($inviteeType, $eventRecordModel->getId(), $body);  // Added by Phuc on 2019.11.28 to add detail link for invitation

		return $body;
	}

	static function addAcceptInvitationLink($emailBody, $eventId, $inviteeId, $inviteeType) {
		if(empty($eventId)) return $emailBody;
		$acceptUrl = self::getAcceptInvitationUrl($eventId, $inviteeId, $inviteeType);

		// Replace variable inside email template
		if (strpos($emailBody, '$AcceptTrackingUrl$')) {
			return str_replace('$AcceptTrackingUrl$', $acceptUrl, $emailBody);
		}
		// Append invitation url in case there is no variable inside the email template
		else {
			$acceptLink = '<div class="invitationresponse"><a href="'. $acceptUrl .'" target="_blank">Accept Invitation</a></div>';
			return substr_replace($emailBody, $acceptLink, strpos($emailBody, '</body>'), 0);
		}
	}

	static function getAcceptInvitationUrl($eventId, $inviteeId, $inviteeType) {
		$options = [
			'handler_path' => 'modules/Events/handlers/TrackAcceptInvitation.php',
			'handler_class' => 'Events_TrackAcceptInvitation_Handler',
			'handler_function' => 'acceptInvitation',
			'handler_data' => [
				'event_id' => $eventId,
				'invitee_id' => $inviteeId,
				'invitee_type' => $inviteeType
			]
		];

		return Vtiger_ShortURL_Helper::generateURL($options);
	}

	static function getInvitedContactsForListView($eventId) {
		$invitedContacts = self::getInviteesForDisplay($eventId, 'Contacts');
		$contactNames = [];

		foreach ($invitedContacts as $contactInfo) {
			$contactNames[] = $contactInfo['name'];
		}

		return join(', ', $contactNames);
	}

	static function getInvitedUsersForListView($eventId) {
		$invitedUsers = self::getInviteesForDisplay($eventId, 'Users');
		$userNames = [];

		foreach ($invitedUsers as $userInfo) {
			$userNames[] = $userInfo['name'];
		}

		return join(', ', $userNames);
	}

	/**
	 * Static method to mark invitee status as `Queued` again
	 * @return void 
	 */
	static function reQueuedInvitation($eventId, $inviteeIds) {
		global $adb;

		// Validate input data
		if (empty($eventId) || empty($inviteeIds) || !is_array($inviteeIds)) return;

		// Reinit db if empty
		if (empty($adb)) $adb = PearDatabase::getInstance();

		$params = [];
		$sql = "UPDATE vtiger_invitees SET status = 'Queued' WHERE activityid = ? AND inviteeid IN (" . generateQuestionMarks($inviteeIds) . ")";

		// Assign event id to params
		$params[] = $eventId;

		// Assign invitee ids to params
		$params = array_merge($params, $inviteeIds);

		return $adb->pquery($sql, $params);
	}
}