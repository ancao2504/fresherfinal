<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/com_vtiger_workflow/VTEventHandler.inc';
require_once 'modules/Emails/mail.php';
require_once 'modules/HelpDesk/HelpDesk.php';
require_once('modules/PBXManager/BaseConnector.php');	// [TelesalesCampain] Added by Vu Mai 2022-10-18

class ModCommentsHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {
		global $current_user, $callCenterConfig; // [TelesalesCampain] Added by Vu Mai 2022-10-18

		if($eventName == 'vtiger.entity.beforesave') {
			// Entity is about to be saved, take required action
		}

		if($eventName == 'vtiger.entity.aftersave') {
			$db = PearDatabase::getInstance();

			$relatedToId = $data->get('related_to');
            $relatedInfo = array();
            $relatedInfo['module'] = $data->focus->moduleName;
            $relatedInfo['id'] = $data->focus->id;
			if ($relatedToId && $data->getModuleName() == 'ModComments') {
				$moduleName = getSalesEntityType($relatedToId);
				$focus = CRMEntity::getInstance($moduleName);
				$focus->retrieve_entity_info($relatedToId, $moduleName);
				$focus->id = $relatedToId;
				$fromPortal = $data->get('from_portal');
				if ($fromPortal) {
					$focus->column_fields['from_portal'] = $fromPortal;
				}
				if($data->isNew()) {
					// we need to update related to modified and last modified by, whenever a comment is added
					$focus->trackLinkedInfo($moduleName, $relatedToId, $data->getModuleName(), $data->getId());
				}

				//if its Internal comment, workflow should not trigger
				$isPrivateComment = $data->get('is_private');
				if(!$isPrivateComment) {
					$entityData = VTEntityData::fromCRMEntity($focus);

					$wfs = new VTWorkflowManager($db);
					$relatedToEventHandler = new VTWorkflowEventHandler();
					$relatedToEventHandler->workflows = $wfs->getWorkflowsForModuleSupportingComments($entityData->getModuleName());

					$wsId = vtws_getWebserviceEntityId($entityData->getModuleName(), $entityData->getId());
					$fromPortal = $entityData->get('from_portal');

					$util = new VTWorkflowUtils();
					$entityCache = new VTEntityCache($util->adminUser());

					$entityCacheData = $entityCache->forId($wsId);
					$entityCacheData->set('from_portal', $fromPortal);
					$entityCache->cache[$wsId] = $entityCacheData;
					$relatedToEventHandler->handleEvent($eventName, $entityData, $entityCache,$relatedInfo);
					$util->revertUser();
				}
			}

            // Added by Phu vo on 2019.04.03 => send notification
            $this->sendNotifications($data);
            // End Phu Vo

			// Added by Vu Mai on 2022-10-18 to notify state data changed with type is related_comments to client
			if ($callCenterConfig['enable'] == false) return;

			// Send saved log event to client dashboard
			$msg = array(
				'state' => 'DATA_CHANGED',
				'receiver_id' => $current_user->id,
				'data_type' => 'RELATED_COMMENTS',
				'customer_id' => $relatedToId,
			);

			PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
			// End Vu Mai
		}
	}
    
    /**
     * Sent notification users tracking
     */
    static $notifiedUsers = [];

    // Implement by Phu Vo on 2019.04.03 => Send notification to followers
    private function sendNotifications($entityData) {
		require_once('include/utils/MentionUtils.php');
        require_once('include/utils/NotificationHelper.php');
        require_once('modules/Users/models/Preferences.php');
        global $adb;

        // Don't send notification when import
        if ($entityData->focus->isBulkSaveMode()) return; // Modified by Phu Vo on 2019.09.22 use entity isBulkSaveMode instead

        // Don't send notification when mass update
        if (strtoupper($_REQUEST['action']) === 'MASSSAVE') return;

        // Assign new notified list before perform send notify action
        self::$notifiedUsers = [];

        if (empty($adb)) $adb = PearDatabase::getInstance();
		$moduleName = $entityData->focus->moduleName;
		
		if ($moduleName != 'ModComments') return;

		$parentCommentId = $entityData->get('parent_comments');
		$commentContent = $entityData->get('commentcontent');
		$mentionedUsers = MentionUtils::getMentionedUsers($commentContent);
		$modifiedUserId = $entityData->get('modifiedby');
		$relatedToId = $entityData->get('related_to');
		$relatedModuleName = getSalesEntityType($relatedToId);
		$relatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedToId, $relatedModuleName);
		$assignedUserId = $relatedRecordModel->get('assigned_user_id');

		// Notify replied comment first
		if (!empty($parentCommentId) && $parentCommentId != 'undefined') {
			$parentComment = Vtiger_Record_Model::getInstanceById($parentCommentId, 'ModComments');
			$userId = $parentComment->get('createdby');

			$sql = "SELECT DISTINCT id, language, time_zone FROM vtiger_users WHERE id = ?";
			$result = $adb->pquery($sql, [$userId]);

			while ($row = $adb->fetchByAssoc($result)) {
                $userNotificationConfig = Users_Preferences_Model::loadPreferences($row['id'], 'notification_config');
				
				if (in_array($row['id'], self::$notifiedUsers)) continue;
				if ($userNotificationConfig == null && $userNotificationConfig->receive_record_update_notifications != 1) continue;
				
				self::$notifiedUsers[] = $row['id'];
				
				$extraData = [
					'action' => 'reply_comment',
					'commenter' => $entityData->get('userid'),
				];

				if($relatedModuleName == 'Calendar') $extraData['activity_type'] = $relatedRecordModel->get('activitytype');

				$data = [
					'receiver_id' => $row['id'],
					'type' => 'notification',
					'related_record_id' => $relatedRecordModel->getId(),
					'related_record_name' => $relatedRecordModel->get('label'),
					'related_module_name' => $relatedRecordModel->getModuleName(),
					'extra_data' => $extraData,
				];

				$data['message'] = translateNotificationMessage($data, $row['language'], $row['time_zone']);

				NotificationHelper::sendNotification($data);
			}
		}

		// Notify mentioned user
		$mentionedUsers = join(',', array_keys($mentionedUsers));
		$mentionedUserIds = Vtiger_CustomOwnerField_Helper::getOwnerIdsFromRequest($mentionedUsers);
		if (!empty($mentionedUserIds)) {
			$userIdsString = "('" . join("', '", $mentionedUserIds) . "')";

			$sql = "SELECT DISTINCT id, language, time_zone FROM vtiger_users WHERE id IN {$userIdsString}";
			$result = $adb->pquery($sql);

			while ($row = $adb->fetchByAssoc($result)) {
                $userNotificationConfig = Users_Preferences_Model::loadPreferences($row['id'], 'notification_config');
				
				if (in_array($row['id'], self::$notifiedUsers)) continue;
				if ($userNotificationConfig == null && $userNotificationConfig->receive_record_update_notifications != 1) continue;
				
				self::$notifiedUsers[] = $row['id'];
				
				$extraData = [
					'action' => 'mention_comment',
					'commenter' => $entityData->get('userid'),
				];

				if($relatedModuleName == 'Calendar') $extraData['activity_type'] = $relatedRecordModel->get('activitytype');

				$data = [
					'receiver_id' => $row['id'],
					'type' => 'notification',
					'related_record_id' => $relatedRecordModel->getId(),
					'related_record_name' => $relatedRecordModel->get('label'),
					'related_module_name' => $relatedRecordModel->getModuleName(),
					'extra_data' => $extraData,
				];

				$data['message'] = translateNotificationMessage($data, $row['language'], $row['time_zone']);

				NotificationHelper::sendNotification($data);
			}
		}
        
        // Comment notification goes here        
        if ($entityData->isNew()) {			
			// Notify will be sent to all directly assigned User (Sand alone user or member of custom group)
			$ownerIds = [];
			$ownerType = vtws_getOwnerType($assignedUserId);

			if ($ownerType === 'Users') {
				$ownerIds[] = $assignedUserId;
			}
			elseif ($ownerType === 'Groups' && Vtiger_CustomOwnerField_Helper::isCustomGroup($assignedUserId)) {
				$owners = Vtiger_Owner_UIType::getCurrentOwners($assignedUserId, false);

				foreach ($owners as $owner) {
					$ownerInfo = explode(':', $owner['id']);
					$ownerType = $ownerInfo[0];
					$ownerId = $ownerInfo[1];

					if ($ownerType === 'Users') $ownerIds[] = $ownerId;
				}
			}

			// Process ownerids in to queryable string
			$ownerIdsString = "('" . implode("','", $ownerIds) . "')";

            // Send notification to all follower and assigned user
            $sql = "SELECT DISTINCT id, language, time_zone FROM vtiger_users
                WHERE id IN (
                    SELECT userid FROM vtiger_crmentity_user_field WHERE recordid = ? AND starred = 1
                )
			";
			
			if (sizeof($ownerIds) > 0) $sql .= " OR id IN $ownerIdsString";

			$result = $adb->pquery($sql, [$relatedToId]);
			// End process with Custom Owner Field

            while ($row = $adb->fetchByAssoc($result)) {
                // Don't send notify if assigned user is user commented
                if($row['id'] == $modifiedUserId) continue;

				// Don't notification again
				if (in_array($row['id'], self::$notifiedUsers)) continue;

                $userNotificationConfig = Users_Preferences_Model::loadPreferences($row['id'], 'notification_config');

                if ($userNotificationConfig != null && $userNotificationConfig->receive_record_update_notifications == 1) {
					self::$notifiedUsers[] = $row['id'];

                    $extraData = [
                        'action' => 'comment',
                        'commenter' => $entityData->get('userid'),
                    ];

                    if($relatedModuleName == 'Calendar') $extraData['activity_type'] = $relatedRecordModel->get('activitytype');

                    $data = [
                        'receiver_id' => $row['id'],
                        'type' => 'notification',
                        'related_record_id' => $relatedRecordModel->getId(),
                        'related_record_name' => $relatedRecordModel->get('label'),
                        'related_module_name' => $relatedRecordModel->get('record_module'),
                        'extra_data' => $extraData,
                    ];

                    $data['message'] = translateNotificationMessage($data, $row['language'], $row['time_zone']);

                    NotificationHelper::sendNotification($data);
                }
            }
        }
    }
}


function CustomerCommentFromPortal($entityData) {
	$adb = PearDatabase::getInstance();

	$data = $entityData->getData();
	$customerWSId = $data['customer'];

	$relatedToWSId = $data['related_to'];
	$relatedToId = explode('x', $relatedToWSId);
	$moduleName = getSalesEntityType($relatedToId[1]);

	if($moduleName == 'HelpDesk' && !empty($customerWSId)) {
		$ownerIdInfo = getRecordOwnerId($relatedToId[1]);
		if(!empty($ownerIdInfo['Users'])) {
			$ownerId = $ownerIdInfo['Users'];
			$ownerName = getOwnerName($ownerId);
			$toEmail = getUserEmailId('id',$ownerId);
		}
		if(!empty($ownerIdInfo['Groups'])) {
			$ownerId = $ownerIdInfo['Groups'];
			$groupInfo = getGroupName($ownerId);
			$ownerName = $groupInfo[0];
			$toEmail = implode(',', getDefaultAssigneeEmailIds($ownerId));
		}
		$subject = getTranslatedString('LBL_RESPONDTO_TICKETID', $moduleName)."##". $relatedToId[1]."## ". getTranslatedString('LBL_CUSTOMER_PORTAL', $moduleName);
		$contents = getTranslatedString('Dear', $moduleName)." ".$ownerName.","."<br><br>"
					.getTranslatedString('LBL_CUSTOMER_COMMENTS', $moduleName)."<br><br>
					<b>".$data['commentcontent']."</b><br><br>"
					.getTranslatedString('LBL_RESPOND', $moduleName)."<br><br>"
					.getTranslatedString('LBL_REGARDS', $moduleName)."<br>"
					.getTranslatedString('LBL_SUPPORT_ADMIN', $moduleName);

		$customerId = explode('x', $customerWSId);

		$result = $adb->pquery("SELECT email FROM vtiger_contactdetails WHERE contactid=?", array($customerId[0]));
		$fromEmail = $adb->query_result($result,0,'email');

		send_mail('HelpDesk', $toEmail,'', $fromEmail, $subject, $contents);
	}
}

function TicketOwnerComments($entityData) {
	global $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID;
	$adb = PearDatabase::getInstance();

	//if commented from portal by the customer, then ignore this
	$customer = $entityData->get('customer');
	if(!empty($customer)) return;

	$wsParentId = $entityData->get('related_to');
	$parentIdParts = explode('x', $wsParentId);
	$parentId = $parentIdParts[1];
	$moduleName = getSalesEntityType($parentId);

	$isNew = $entityData->isNew();

	if($moduleName == 'HelpDesk') {
		$ticketFocus = CRMEntity::getInstance($moduleName);
		$ticketFocus->retrieve_entity_info($parentId, $moduleName);
		$ticketFocus->id = $parentId;

		if(!$isNew) {
			$reply = 'Re : ';
		} else {
			$reply = '';
		}

		$subject = $ticketFocus->column_fields['ticket_no'] . ' [ '.getTranslatedString('LBL_TICKET_ID', $moduleName)
							.' : '.$parentId.' ] '.$reply.$ticketFocus->column_fields['ticket_title'];

		$emailOptOut = 0;
		$contactId = $ticketFocus->column_fields['contact_id'];
		$accountId = $ticketFocus->column_fields['parent_id'];
		//To get the emailoptout vtiger_field value and then decide whether send mail about the tickets or not
		if(!empty($contactId)) {
			$result = $adb->pquery('SELECT email, emailoptout FROM vtiger_contactdetails WHERE contactid=?',
										array($contactId));
			$emailOptOut = $adb->query_result($result,0,'emailoptout');
			$parentEmail = $contactMailId = $adb->query_result($result,0,'email');
			$displayValueArray = getEntityName('Contacts', $contactId);
			if (!empty($displayValueArray)) {
				foreach ($displayValueArray as $key => $value) {
					$contactName = $value;
				}
			}
			$parentName = $contactName;

			//Get the status of the vtiger_portal user. if the customer is active then send the vtiger_portal link in the mail
			if($parentEmail != '') {
				$sql = "SELECT * FROM vtiger_portalinfo WHERE user_name=?";
				$isPortalUser = $adb->query_result($adb->pquery($sql, array($parentEmail)),0,'isactive');
			}
		} else if(!empty($accountId)) {
			$result = $adb->pquery("SELECT accountname, emailoptout, email1 FROM vtiger_account WHERE accountid=?",
										array($accountId));
			$emailOptOut = $adb->query_result($result,0,'emailoptout');
			$parentEmail = $adb->query_result($result,0,'email1');
			$parentName = $adb->query_result($result,0,'accountname');

		}
		//added condition to check the emailoptout
		if($emailOptOut == 0) {
			$entityData = VTEntityData::fromCRMEntity($ticketFocus);

			if($isPortalUser == 1){
				$bodysubject = getTranslatedString('Ticket No', $moduleName) .": " . $ticketFocus->column_fields['ticket_no']
					. "<br>" . getTranslatedString('LBL_TICKET_ID', $moduleName).' : '.$parentId.'<br> '
					.getTranslatedString('LBL_SUBJECT', $moduleName).$ticketFocus->column_fields['ticket_title'];

				$emailBody = $bodysubject.'<br><br>'.HelpDesk::getPortalTicketEmailContents($entityData);
			} else {
				$emailBody = HelpDesk::getTicketEmailContents($entityData);
			}

			send_mail('HelpDesk', $parentEmail, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $emailBody);
		}
	}
}