<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once dirname(__FILE__) .'/ModTracker.php';
require_once 'data/VTEntityDelta.php';
require_once 'modules/Users/models/Preferences.php';

class ModTrackerHandler extends VTEventHandler {

	function handleEvent($eventName, $data) {

		global $adb, $current_user;
		$moduleName = $data->getModuleName();
		$isTrackingEnabled = ModTracker::isTrackingEnabledForModule($moduleName);
		if(!$isTrackingEnabled) {
			return;
		}
		if($eventName == 'vtiger.entity.aftersave.final') {
			$recordId = $data->getId();
			$columnFields = $data->getData();
			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

			$newerEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
			$newerColumnFields = $newerEntity->getData();

			if(is_array($delta)) {
				$inserted = false;
				foreach($delta as $fieldName => $values) {
					if($fieldName != 'modifiedtime') {
						if(!$inserted) {
							$checkRecordPresentResult = $adb->pquery('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ? AND status = ?', array($recordId, ModTracker::$CREATED));
							if(!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
								$status = ModTracker::$CREATED;
							} else {
								$status = ModTracker::$UPDATED;
							}
							$this->id = $adb->getUniqueId('vtiger_modtracker_basic');
							$changedOn = $newerColumnFields['modifiedtime'];
							if($moduleName == 'Users') {
								$date_var = date("Y-m-d H:i:s");
								$changedOn =  $adb->formatDate($date_var,true);
							}
							$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
										VALUES(?,?,?,?,?,?)', Array($this->id, $recordId, $moduleName,
										$current_user->id, $changedOn, $status));
							$inserted = true;
                        }
                        
                        // [CustomOwnerField] Added by Phu Vo on 2019.07.23 to process assigned user id, user_invitees (for Events)
                        // Because custom group will be deleted after no record use it, we have to save it display value instead of raw id value
                        if (
                            $fieldName === 'assigned_user_id' 
                            || ($fieldName === 'user_invitees' && in_array($moduleName, ['Calendar', 'Events']))
                        ) {
                            $oldDeletedGroup = $GLOBALS['deleted_custom_groups'][$values['oldValue']];
                            $oldValue = $oldDeletedGroup['label'] ? $oldDeletedGroup['label'] : Vtiger_Owner_UIType::getCurrentOwnersForDisplay($values['oldValue'], false);

                            $values['oldValue'] = $oldValue;
                            $values['currentValue'] = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($values['currentValue'], false);
                        }
                        // End process assigned user id

						$adb->pquery('INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue,postvalue) VALUES(?,?,?,?)',
							Array($this->id, $fieldName, $values['oldValue'], $values['currentValue']));
					}
				}
			}

            // Added by Phu vo on 2019.04.03 => Send notification to assigned user
            if (isMassAction()) {
                $this->queueMassActionNotification($data, $delta);
            }
            else {
                $this->sendNotification($data, $delta);
            }
            // End Phu Vo
		}

		if($eventName == 'vtiger.entity.beforedelete') {
			$recordId = $data->getId();
			$columnFields = $data->getData();
			$id = $adb->getUniqueId('vtiger_modtracker_basic');
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s',time()), ModTracker::$DELETED));
		}

		if($eventName == 'vtiger.entity.afterrestore') {
			$recordId = $data->getId();
			$columnFields = $data->getData();
			$id = $adb->getUniqueId('vtiger_modtracker_basic');
			$adb->pquery('INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)', Array($id, $recordId, $moduleName, $current_user->id, date('Y-m-d H:i:s',time()), ModTracker::$RESTORED));
		}
    }
    
    private function queueMassActionNotification($entityData, $delta) {
        // Process queue notification for assignment
        $assignedUserId = $entityData->get('assigned_user_id');
        $changedFields = [];
        $skipFields = ['modifiedtime', 'modifiedby', 'label'];

        foreach ($delta as $fieldName => $field) {
            if (!in_array($fieldName, $skipFields)) {
                $changedFields[$fieldName] = $field;
            }
        }
        
        $owners = getGroupMemberIds($assignedUserId);

        foreach ($owners as $ownerId) {
            // Send notification to user member
            $this->queueMassActionNotificationForUser($ownerId, $entityData, $changedFields);
        }
    }

    private function queueMassActionNotificationForUser($userId, $entityData, $changedFields) {
        global $notificationConfig;
        
        // Prepair variables use for validate action
        $moduleName = $entityData->focus->moduleName;
        $massNotify = vglobal('mass_notify');
        $modifyUserId = $entityData->get('modifiedby');

        // Process for assign user
        if (
            !empty($changedFields['assigned_user_id'])
            && !in_array($moduleName, $notificationConfig['assign_ignore_modules'])
        ) {
            // Process a mechanism to validate assignment notify
            // Use the variable below as a checkpoint, if we figure out current assigned user is already present in old group
            // Set it to false
            $triggerAssignmentNotify = true;

            // Get old assigned owner
            $oldOwnerId = $changedFields['assigned_user_id']['oldValue'];
            $oldGroupMemberIds = $this->getOldGroupMemberIds($oldOwnerId);

            // Check if current assigned user in that old group and decide to trigger assignment notify
            if (in_array($userId, $oldGroupMemberIds)) $triggerAssignmentNotify = false;
        
            // Process for assignement
            if($triggerAssignmentNotify) {
                if (empty($massNotify['mass_assign'])) {
                    $massNotify['mass_assign'] = [
                        'module_name' => $moduleName,
                        'modify_user_id' => $modifyUserId,
                        'users' => [],
                    ];

                    if($moduleName == 'Calendar') $massNotify['mass_assign']['activity_type'] = $entityData->get('activitytype');
                };

                if (empty($massNotify['mass_assign']['users'][$userId])) $massNotify['mass_assign']['users'][$userId] = 0;
                $massNotify['mass_assign']['users'][$userId]++;
            }
        }
    
        vglobal('mass_notify', $massNotify);
    }

    /**
     * Sent notification users tracking
     */
    static $notifiedUsers = [];

    /**
     * Records old owner ids
     */
    static $oldGroupMemberIds = [];

    // Added by Phu Vo on 2019.04.04 => Send Close deal notifications and Update notification goes here
    private function sendNotification($entityData, $delta) {
        // Modified by Phu Vo on 2019.07.08 to process base on Custom Owner Field logic
        require_once('include/utils/NotificationHelper.php');

        global $adb;

        // Don't send notification when import and mass update
        if (isMassAction()) return;

        $assignedUserId = $entityData->get('assigned_user_id');
        $modifyUserId = $entityData->get('modifiedby');

        $moduleName = $entityData->focus->moduleName;
        $recordId = $entityData->getId();
        $skipFields = ['modifiedtime', 'modifiedby', 'label'];
        $changedFields = [];

        if ($moduleName == 'ModComments') return; // ModComments notification logic will handle at ModCommentHandler

        // Assign new notified list before perform send notify action
        self::$notifiedUsers = [];

        foreach ($delta as $fieldName => $field) {
            if (!in_array($fieldName, $skipFields)) {
                $changedFields[$fieldName] = $field;
            }
        }

        if (sizeof($changedFields) > 0) {
            // Send notification for close opportunity
            if (!empty($changedFields['potentialresult']) && $moduleName === 'Potentials' && $changedFields['potentialresult']['currentValue'] == 'Closed Won') {
                $sql = "SELECT id, language, time_zone FROM vtiger_users WHERE deleted = 0 AND status = 'Active' AND id <> ? AND id = ?";
                $result = $adb->pquery($sql, [$modifyUserId, $assignedUserId]);

                if ($result) {
                    while ($row = $adb->fetchByAssoc($result)) {
                        // Check assigned user notification reference
                        $userNotificationConfig = Users_Preferences_Model::loadPreferences($row['id'], 'notification_config');

                        if ($userNotificationConfig != null && $userNotificationConfig->receive_record_update_notifications == 1) {
                            $extraData = [
                                'action' => 'close_deal',
                                'updater' => $modifyUserId,
                                'deal_result' => $changedFields['potentialresult']['currentValue'],
                            ];

                            if($moduleName == 'Calendar') $extraData['activity_type'] = $entityData->get('activitytype');

                            $data = [
                                'receiver_id' => $row['id'],
                                'type' => 'notification',
                                'related_record_id' => $recordId,
                                'related_record_name' => $entityData->get('label'),
                                'related_module_name' => $moduleName,
                                'extra_data' => $extraData,
                            ];

                            $data['message'] = translateNotificationMessage($data, $row['language'], $row['time_zone']);

                            NotificationHelper::sendNotification($data);
                        }
                    }
                }
            }
            // Process notification for assign and update situation
            else {
                // Process notification for Transfer Main Owner Ship
                $this->sendTransferMainOwnerNotification($assignedUserId, $entityData, $changedFields);

                // Process update // assigned notification start from here
                $ownerType = vtws_getOwnerType($assignedUserId);

                if ($ownerType === 'Groups') {
                    $this->sendUpdateNotificationToGroup($assignedUserId, $entityData, $changedFields);
                }
                else if ($ownerType === 'Users') {
                    $this->sendUpdateNotificationToUser($assignedUserId, $entityData, $changedFields);
                }
            }
        }
    }

    /**
     * Send Transfer Main Owner Notification to old main owner
     * @author Phu Vo (2019.11.15)
     */
    private function sendTransferMainOwnerNotification($assignedUserId, $entityData, $changedFields) {
        // Infomation use to validate input
        $modifyUserId = $entityData->get('modifiedby');
        $oldMainOwnerId = $changedFields['main_owner_id']['oldValue'];
        $oldOwnerNotificationConfig = Users_Preferences_Model::loadPreferences($oldMainOwnerId, 'notification_config');

        // Validate action
        if (in_array($oldMainOwnerId, self::$notifiedUsers)) return; // Don't send to user that already received notification
        if (empty($changedFields['main_owner_id'])) return; // main_owner_id changed
        if (empty($changedFields['main_owner_id']['oldValue'])) return; // there is a old main owner
        if ($changedFields['main_owner_id']['oldValue'] === $modifyUserId) return; // old main owner not the modify user
        if (empty($oldOwnerNotificationConfig)) return; // Not config notification yet
        if ($oldOwnerNotificationConfig->receive_assignment_notifications != 1) return; // Not accept receive assignment notification

        // Flag to prevent other notification
        self::$notifiedUsers[] = $oldMainOwnerId;
        
        // Infomation use to perform sending transfer main owner notification
        $recordId = $entityData->getId();
        $moduleName = $entityData->focus->moduleName;
        $newMainOwnerId = $changedFields['main_owner_id']['currentValue'];
        $oldOwnerLanguage = getUserData('language', $oldMainOwnerId) ?? vglobal('default_language');
        $oldOwnerTimezone = getUserData('time_zone', $oldMainOwnerId) ?? vglobal('default_timezone');

        $extraData = [
            'action' => 'transfer_main_owner',
            'updater' => $modifyUserId,
            'new_main_owner' => $newMainOwnerId,
            'owner_type' => 'Users',
        ];

        // Procces notification message in case record assigned to a group, so there are no main owner
        // We will still send the notification but with another message
        if (empty($newMainOwnerId) || $newMainOwnerId == -1) {
            $extraData['new_main_owner'] = $entityData->get('assigned_user_id');
            $extraData['owner_type'] = 'Groups';
        }

        if($moduleName == 'Calendar') $extraData['activity_type'] = $entityData->get('activitytype');

        $data = [
            'receiver_id' => $oldMainOwnerId,
            'type' => 'notification',
            'related_record_id' => $recordId,
            'related_record_name' => $entityData->get('label'),
            'related_module_name' => $moduleName,
            'extra_data' => $extraData,
        ];

        $data['message'] = translateNotificationMessage($data, $oldOwnerLanguage, $oldOwnerTimezone);

        NotificationHelper::sendNotification($data);
    }

    /**
     * Send notification to a group (normal or custom group)
     * @author Phu Vo (2019.07.09)
     */
    private function sendUpdateNotificationToGroup($groupId, $entityData, $changedFields) {
        $isCustomGroup = Vtiger_CustomOwnerField_Helper::isCustomGroup($groupId);

        if($isCustomGroup) {
            // If it is a custom group, first send to all directly assigned users
            $owners = Vtiger_Owner_UIType::getCurrentOwners($groupId, false);

            foreach ($owners as $owner) {
                $ownerInfo = explode(':', $owner['id']);
                $ownerType = $ownerInfo[0];
                $ownerId = $ownerInfo[1];

                if ($ownerType !== 'Users') continue;

                $this->sendUpdateNotificationToUser($ownerId, $entityData, $changedFields);
            }

            // Then Send to all users in each member group
            foreach ($owners as $owner) {
                $ownerInfo = explode(':', $owner['id']);
                $ownerType = $ownerInfo[0];
                $ownerId = $ownerInfo[1];

                if ($ownerType !== 'Groups') continue;

                $this->sendUpdateNotificationToGroup($ownerId, $entityData, $changedFields);
            }

            // Don't process anymore leave the rest to the recursion
            return;
        }

        // We don't wanna know this group includes how many sub group or roles, just get all user members and send notify
        $owners = getGroupMemberIds($groupId);

        foreach ($owners as $ownerId) {
            // Send notification to user member
            $this->sendUpdateNotificationToUser($ownerId, $entityData, $changedFields, $groupId);
        }
    }

    /**
     * Send notification to a user (stand alone or member of a group)
     * @author Phu Vo (2019.07.09)
     */
    private function sendUpdateNotificationToUser($userId, $entityData, $changedFields, $memberOfGroup = null) {
        global $notificationConfig;

        // Prepair variables use for validate action
        $modifyUserId = $entityData->get('modifiedby');
        $moduleName = $entityData->focus->moduleName;
        $userNotificationConfig = Users_Preferences_Model::loadPreferences($userId, 'notification_config');

        // Working with notified user tracker and validate to decide notify to this id or not
        if (in_array($userId, self::$notifiedUsers)) return;
        if ($modifyUserId == $userId) return; // Don't send notify if user edit they own record
        if (empty($userNotificationConfig)) return; // Don't send notification if user preferences null

        // IMPORTANT: Sending notification start from here, mark that user as notified
        self::$notifiedUsers[] = $userId;

        // Get some information to use later
        $recordId = $entityData->getId();
        $userLanguage = getUserData('language', $userId) ?? vglobal('default_language');
        $timezone = getUserData('time_zone', $userId) ?? vglobal('default_timezone');

        // Process a mechanism to validate assignment notify
        // Use the variable below as a checkpoint, if we figure out current assigned user is already present in old group
        // Set it to false
        $triggerAssignmentNotify = true;

        if (!empty($changedFields['assigned_user_id'])) {
            // Get old assigned owner
            $oldOwnerId = $changedFields['assigned_user_id']['oldValue'];
            $oldGroupMemberIds = $this->getOldGroupMemberIds($oldOwnerId);

            // Check if current assigned user in that old group and decide to trigger assignment notify
            if (in_array($userId, $oldGroupMemberIds) && $this->isDirectOwner($userId, $oldOwnerId)) $triggerAssignmentNotify = false;
        }

        if(
            !empty($changedFields['assigned_user_id']) 
            && !in_array($moduleName, $notificationConfig['assign_ignore_modules'])
            && $triggerAssignmentNotify
        ) {
            // Ignore assignment notification for missed call
            if ($moduleName == 'Calendar' && $entityData->get('activitytype') == 'Call' && $entityData->get('missed_call') == 1) return;

            // Check user receive assignment notification preference
            if ($userNotificationConfig->receive_assignment_notifications == 1) {
                $extraData = [
                    'action' => 'assign',
                    'assigner' => $modifyUserId,
                    'group_owner' => $memberOfGroup,
                ];

                if($moduleName == 'Calendar') $extraData['activity_type'] = $entityData->get('activitytype');

                $data = [
                    'receiver_id' => $userId,
                    'type' => 'notification',
                    'related_record_id' => $entityData->getId(),
                    'related_record_name' => $entityData->get('label'),
                    'related_module_name' => $moduleName,
                    'extra_data' => $extraData,
                ];

                $data['message'] = translateNotificationMessage($data, $userLanguage, $timezone);

                NotificationHelper::sendNotification($data);
            }
        }
        else if (!$entityData->isNew() && $userNotificationConfig->receive_record_update_notifications == 1) {
            // Process first changed field
            $fieldName = array_keys($changedFields)[0];
            $currentValue = $changedFields[$fieldName]['currentValue'];

            $extraData = [
                'action' => 'update',
                'updater' => $entityData->get('modifiedby'),
                'updated_field' => $fieldName,
                'updated_value' => $currentValue,
                'group_owner' => $memberOfGroup,
            ];

            // Translate updated value in custom group case
            if ($fieldName === 'assigned_user_id') {
                $extraData['updated_label'] = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($currentValue, false);
            };

            if($moduleName == 'Calendar') $extraData['activity_type'] = $entityData->get('activitytype');

            $data = [
                'receiver_id' => $userId,
                'type' => 'notification',
                'related_record_id' => $recordId,
                'related_record_name' => $entityData->get('label'),
                'related_module_name' => $moduleName,
                'extra_data' => $extraData,
            ];

            $data['message'] = translateNotificationMessage($data, $userLanguage, $timezone);

            NotificationHelper::sendNotification($data);
        }
    }

    private function getOldGroupMemberIds($oldOwnerId) {
        if (!empty(self::$oldGroupMemberIds[$oldOwnerId])) {
            return self::$oldGroupMemberIds[$oldOwnerId];
        }
        
        $oldOwnerType = vtws_getOwnerType($oldOwnerId);

        // If it is a User, just assign that owner to $oldGroupMemberIds to check it later
        if ($oldOwnerType === 'Users') {
            self::$oldGroupMemberIds[$oldOwnerId] = [$oldOwnerId];
        }
        else if ($oldOwnerType === 'Groups') {
            // If it is a normal group (or a custom group that are not already deleted)
            // Its information is still on the system, so just process it normally
            self::$oldGroupMemberIds[$oldOwnerId] = getGroupMemberIds($oldOwnerId);
        }
        else if ($oldOwnerType == false) {
            // Maybe that is a custom group and it is already deleted
            $deletedGroups = $GLOBALS['deleted_custom_groups'][$oldOwnerId];

            if (!empty($deletedGroups)) {
                // We already cache group member ids before delete that group
                self::$oldGroupMemberIds[$oldOwnerId] = $deletedGroups['member_ids'];
            }
        }

        return self::$oldGroupMemberIds[$oldOwnerId];
    }

    private function isDirectOwner($userId, $groupId) {
        $owners = [];
        $isDeleted = vtws_getOwnerType($groupId) == false;

        if ($isDeleted) {
            $deletedGroups = $GLOBALS['deleted_custom_groups'][$groupId];
            $owners = $deletedGroups['owners'];
        }
        else {
            $owners = Vtiger_Owner_UIType::getCurrentOwners($groupId, false);
        }

        foreach ($owners as $owner) {
            $ownerInfo = explode(':', $owner['id']);
            $ownerType = $ownerInfo[0];
            $ownerId = $ownerInfo[1];

            if ($ownerType ===  'Users' && $userId == $ownerId) return true;
        }

        return false;
    }
}
?>