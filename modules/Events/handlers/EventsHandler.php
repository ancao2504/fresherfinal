<?php

/*
*	EventsHandler.php
*	Author: Phuc Lu
*	Maintainer: Phu Vo
*	Date: 2019.11.26
*   Purpose: provide handler function for module Events
*/

class EventsHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'Events') return;
		
		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
			$this->updateDurationValue($entityData); // Added by Phu Vo on 2020.07.11
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			// Add handler functions here
			$this->sendNotificationForAssignedUsers($entityData);
			$this->sendUpdateNotification($entityData);
			$this->updateMissedCallInfo($entityData);
			$this->forwardEventToCallCenterBridge($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}

	function sendNotificationForAssignedUsers($entityData) {
		require_once('vtlib/Vtiger/Mailer.php');
		$recordId = $entityData->getId();
		$moduleName = $entityData->getModuleName();

		// Get old data
		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId);

		// Send for assign user when event is created or event is updated from no sendnotification to sendnotification
		if (($entityData->isNew() || isset($delta['sendnotification'])) && $entityData->get('sendnotification')) {
			// Get assigned users
			$assignedUserIds = [];
			$assignedUserId = $entityData->get('assigned_user_id');

			if (vtws_getOwnerType($assignedUserId) == 'Users') {
				$assignedUserIds[] = $assignedUserId;
			}
			else {
				$assignedUserIds = getGroupMemberIds($assignedUserId);
				$assignedUserIds = array_unique($assignedUserIds);
			}

			if (count($assignedUserIds)) {
				// Get company name and system email
				$systemEmailInfo = getSystemEmailAddress();

				// Because some common information only usable with Event Record Model, and it extends from
				// Calendar model so we will use Events record model for now
				// [TODO] Refactor and replace with specific module record model
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Events');

				$cacheData = decodeUTF8($recordModel->getInviteUserMailData());
				$icsFile = generateIcsAttachment($cacheData, $moduleName . $recordId . '-assignment'); // [Calendar] Added suffix by Phu Vo on 2019.12.11

				foreach ($assignedUserIds as $assignedUserId) {
					// Modified by Phu Vo on 2021.07.12 to ignore if assigned user is also creator
					$creatorId = $entityData->get('createdby');
					
					if ($creatorId == $assignedUserId) {
						continue;
					}
					// End Phu Vo

					$assignUserInfo = Events_Invitation_Helper::getInviteeInfo($assignedUserId, 'Users');;

					// No receiver found or do not have email, skip this
					if (empty($assignUserInfo) || empty($assignUserInfo['email'])) {
						continue;
					}

					$body = $this->getAssignedNotifyEmailBody($recordModel, $cacheData, $assignUserInfo, $moduleName);
					$body = getMergedDescription($body, $recordId, $moduleName);

					$mail = new Vtiger_Mailer();
					$mail->IsHTML(true);
					$mail->_serverConfigured = true;
					$mail->ConfigSenderInfo($systemEmailInfo['email'], $systemEmailInfo['name']);
					$mail->Subject = vtranslate('LBL_EMAIL_ASSIGNED_EVENT', $moduleName, decodeUTF8($cacheData['subject']));
					$mail->Body = $body;
					$mail->AddAttachment($icsFile, '', 'base64', 'text/calendar');
					$mail->SendTo($assignUserInfo['email'], $assignUserInfo['name']);
				}
			}

		}
	}

	function sendUpdateNotification($entityData) {
		// Added by Phu Vo We will ignore every update action when event/task is completed
		if ($entityData->get('eventstatus') === 'Held') return;
		if ($entityData->get('taskstatus') === 'Completed') return;
		// End Phu Vo

		require_once('vtlib/Vtiger/Mailer.php');

		$recordId = $entityData->getId();
		$moduleName = $entityData->getModuleName();

		// Check if send notification is on and if the event is over or not
		$timeStart = $entityData->get('date_start') . ' ' . $entityData->get('time_start');
		$currentTime = Date('Y-m-d h:m:s');

		// Get old data
		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId);
		$changedFields = array_keys($delta);

		// If status do not change from no sendnotification to sendnotification and is not new and sendnotification is on and time is not over
		if (!isset($delta['sendnotification']) && !$entityData->isNew() && $entityData->get('sendnotification') && strtotime($timeStart) > strtotime($currentTime)) {
			$isChanged = false;

			// Get fields to check
			$checkedFields = [
				'date_start',
				'due_date',
				'time_start',
				'time_end',
				'location',
				'eventstatus',
				'description',
				'taskstatus',
			];

			// If changes any values in checked fields
			foreach ($checkedFields as $fieldName) {
				if (in_array($fieldName, $changedFields)) {
					$isChanged = true;
					break;
				}
			}

			if ($isChanged) {
				// Get all invitees with and accepted
				$invitees = Events_Data_Model::getInvitees($recordId, ['Accepted']);

				// Get assigned users
				$assignedUserIds = [];
				$assignedUserId = $entityData->get('assigned_user_id');

				if (vtws_getOwnerType($assignedUserId) == 'Users') {
					$assignedUserIds[] = [
						'inviteeid' => $assignedUserId,
						'invitee_type' => 'Users',
						'status' => 'Accepted'
					];
				}
				else {
					$userGroupIds = getGroupMemberIds($assignedUserId);
					$userGroupIds = array_unique($userGroupIds);

					foreach ($userGroupIds as $userId) {
						$assignedUserIds[] = [
							'inviteeid' => $userId,
							'invitee_type' => 'Users',
							'status' => 'Accepted'
						];
					}
				}

				$receiverIds = array_merge($invitees, $assignedUserIds);

				if (count($receiverIds)) {
					// Get company name and system email
					$systemEmailInfo = getSystemEmailAddress();

					// Because some common information only usable with Event Record Model, and it extends from
					// Calendar model so we will use Events record model for now
					// [TODO] Refactor and replace with specific module record model
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Events');

					$cacheData = decodeUTF8($recordModel->getInviteUserMailData());
					$icsFile = generateIcsAttachment($cacheData, $moduleName . $recordId . '-update'); // [Calendar] Added suffix by Phu Vo on 2019.12.11

					foreach ($receiverIds as $receiver) {
						// Added by Phu Vo on 2020.02.27 Ignore if receiver is modifier
						if ($receiver['inviteeid'] === $entityData->get('modifiedby')) continue;
						// End Phu Vo

						$receiverInfo = Events_Invitation_Helper::getInviteeInfo($receiver['inviteeid'], $receiver['invitee_type']);;

						// No receiver found or do not have email, skip this
						if (empty($receiverInfo) || empty($receiverInfo['email'])) {
							continue;
						}

						$invitationBody = $this->getUpdateNotifyEmailBody($recordModel, $cacheData, $receiverInfo, $moduleName);
						$invitationBody = getMergedDescription($invitationBody, $recordId, $moduleName);

						$mail = new Vtiger_Mailer();
						$mail->IsHTML(true);
						$mail->_serverConfigured = true;
						$mail->ConfigSenderInfo($systemEmailInfo['email'], $systemEmailInfo['name']);
						$mail->Subject = vtranslate('LBL_INVITATION_UPDATED_SUBJECT', $moduleName, decodeUTF8($cacheData['subject']));
						$mail->Body = $invitationBody;
						$mail->AddAttachment($icsFile, '', 'base64', 'text/calendar');
						$mail->SendTo($receiverInfo['email'], $receiverInfo['name']);
					}
				}

				// Added by Phu Vo on 2020.02.27 to resend invitation to invitees
				// Have not accept yet when these update occurs
				// We will resent invite email to invitees who has not accept event (Queued and sent)
				$sentInvitees = Events_Data_Model::getInvitees($recordId, ['Sent']);

				// Invitation email to send by system using a Queued table and trigger with cron task
				// Next cycle will send invitees with status 'Queued' with newest data
				// So we will just update invitees with status 'Sent' to status 'Queued' to send invitation again
				$sentInviteeIds = [];
				foreach ($sentInvitees as $sentInvitee) {
					$sentInviteeIds[] = $sentInvitee['inviteeid'];
				}

				// Call to static method that update invitation status to Queued
				Events_Invitation_Helper::reQueuedInvitation($recordId, $sentInviteeIds);
				// End Phu Vo
			}
		}
	}

    // Added by Phuc on 2019.11.27 to get body for event updated invitation
    function getUpdateNotifyEmailBody($recordModel, array $invitationData, array $receiverInfo, $moduleName) {
        require_once('include/utils/utils.php');
        global $adb;

        $receiverId = $receiverInfo['id'];
        $startDate = new DateTimeField($invitationData['st_date_time']);
        $endDate = new DateTimeField($invitationData['end_date_time']);
        $creatorId = $recordModel->get('createdby');
        $creatorName = getUserFullName($creatorId);

        if ($receiverInfo['type'] == 'Users') {
            $dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($receiverId, 'Users');
        }
        else {
            $dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($creatorId, 'Users');
        }

        // Get email template
        $query = "SELECT body FROM vtiger_emailtemplates WHERE templatename = ? AND systemtemplate = 1";
        $templateBody = $adb->getOne($query, [$this->getUpdateEmailTemplateName($moduleName)]);

        // Replace variables
        $body = $templateBody;
        $body = str_replace('$invitee_name$', $receiverInfo['name'], $body);
        $body = str_replace('$contactid$', $invitationData['contact_name'], $body);
		$body = str_replace('$creator_name$', $creatorName, $body);

		// Process date time
		$body = str_replace('$date_start$', $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		if ($recordModel->get('activitytype') == 'Task') {
			$body = str_replace('$due_date$', $endDate->getDisplayDate($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		}
		else {
			$body = str_replace('$due_date$', $endDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		}

		$body = replaceRecordDetailLink($receiverInfo['type'], $recordModel->getId(), $body);

        return $body;
    }
	// Ended by Phuc

	// Added by Phuc on 2019.11.28 to get body for assign notification
	function getAssignedNotifyEmailBody($recordModel, array $recordData, array $receiverInfo, $moduleName)  {
		require_once('include/utils/utils.php');
        global $adb;

        $receiverId = $receiverInfo['id'];
        $startDate = new DateTimeField($recordData['st_date_time']);
		$endDate = new DateTimeField($recordData['end_date_time']);
		$creatorId = $recordModel->get('createdby');
        $creatorName = getUserFullName($creatorId);
        $dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($receiverId, 'Users');

        // Get email template
        $query = "SELECT body FROM vtiger_emailtemplates WHERE templatename = ? AND systemtemplate = 1";
        $templateBody = $adb->getOne($query, [$this->getAssignedEmailTemplateName($moduleName)]);

        // Replace variables
        $body = $templateBody;
        $body = str_replace('$user_name$', $receiverInfo['name'], $body);
        $body = str_replace('$contactid$', $recordData['contact_name'], $body);
		$body = str_replace('$creator_name$', $creatorName, $body);

		// Process date time
        $body = str_replace('$date_start$', $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
        if ($recordModel->get('activitytype') == 'Task') {
			$body = str_replace('$due_date$', $endDate->getDisplayDate($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		}
		else {
			$body = str_replace('$due_date$', $endDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
		}

		$body = replaceRecordDetailLink($receiverInfo['type'], $recordModel->getId(), $body);

        return $body;
	}

	// Added by Phu Vo on 2020.01.21
	function updateMissedCallInfo($entityData) {
		global $adb, $current_user;

		// Prevent mass action
		if ($entityData->focus->isBulkSaveMode()) return;

		$eventId = $entityData->getId();
		$activityType = $entityData->get('activitytype');

		if ($activityType === 'Call' || $activityType === 'Mobile Call') {
			if ($entityData->get('events_call_direction') !== 'Outbound') return;
			if (empty($entityData->get('parent_id'))) return;

			// Logic start from here
			// Get old data
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta('Events', $eventId);

			if (isset($delta['eventstatus']) && $delta['eventstatus']['currentValue'] == 'Held') {
				$sql = "UPDATE vtiger_activity AS a
					INNER JOIN vtiger_seactivityrel AS ar ON (a.activityid = ar.activityid)
					SET a.events_call_result = 'call_result_called_back'
					WHERE a.missed_call = 1 AND ar.crmid = ?";

				$adb->pquery($sql, [$entityData->get('parent_id')]);
			}
		}
	}

	// Added by Phu Vo on 2020.01.21
	function forwardEventToCallCenterBridge($entityData) {
		require_once('modules/PBXManager/BaseConnector.php');
		global $current_user, $callCenterConfig;

		// Prevent mass action
		if ($entityData->focus->isBulkSaveMode()) return;
		if ($callCenterConfig['enable'] == false) return;

		if ($entityData->get('activitytype') === 'Call' || $entityData->get('activitytype') === 'Mobile Call') {
			// Send saved log event to client dashboard
			$msg = array(
				'state' => 'CALL_LOG_SAVED',
				'call_id' => $entityData->get('pbx_call_id'),
				'receiver_id' => $current_user->id,
				'customer_id' => $entityData->get('parent_id'),
				'direction' => $entityData->get('events_call_direction'),
			);

			PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
		}
	}

	/** Implemented by Phu Vo on 2020.07.11 */
	function updateDurationValue($entityData) {
		if (empty($entityData->get('date_start')) || empty($entityData->get('time_start'))) return;
		if (empty($entityData->get('due_date')) || empty($entityData->get('time_end'))) return;

		$startTime = strtotime($entityData->get('date_start') . ' ' . $entityData->get('time_start'));
		$endTime = strtotime($entityData->get('due_date') . ' ' . $entityData->get('time_end'));
		$duration = $endTime - $startTime;
		$entityData->set('duration', $duration);
	}

	protected function getAssignedEmailTemplateName($moduleName) {
		$mapping = [
			'Calendar' => 'Task Assigned Notification',
			'Events' => 'Event Assigned Notification',
		];

		return $mapping[$moduleName];
	}

	protected function getUpdateEmailTemplateName($moduleName) {
		$mapping = [
			'Calendar' => 'Task Updated Notification',
			'Events' => 'Event Updated Notification',
		];

		return $mapping[$moduleName];
	}
}