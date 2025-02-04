<?php

/**
 * Author: Phu Vo
 * Date: 2019.04.03
 * Purpose: Event handler for module Calendar
 */

require_once 'include/utils/NotificationHelper.php';

class ActivityHandler extends VTEventHandler {

    function handleEvent($eventName, $entityData) {
        // Event handle for Task only
        if ($entityData->getModuleName() == 'Calendar' || ($entityData->getModuleName() == 'Activity' && $entityData->get('activitytype') == 'Task')) {
            if ($eventName === 'vtiger.entity.aftersave') {
                $this->sendNotificationsForTask($entityData);
            }
        }

        // Event handle for Event only (Call, Task, Meeting)
        if ($entityData->getModuleName() == 'Events' || ($entityData->getModuleName() == 'Activity' && $entityData->get('activitytype') != 'Task')) {
            if ($eventName === 'vtiger.entity.aftersave') {
                $this->sendNotificationsForEvent($entityData);
            }
        }
    }
    
    private function sendNotificationsForTask($entityData) {
        // Assign task send notification goes here
        $recordId = $entityData->getId();

        // return if recordId empty
        if (empty($recordId)) return;
        
        $moduleName = $entityData->focus->moduleName;
        $vtEntityDelta = new VTEntityDelta();
        $delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

        // return if assigned_user_id not change
        if (empty($delta['assigned_user_id'])) return;

        // get assigned user preference
        $userId = $entityData->get('assigned_user_id');
        $userNotificationConfig = Users_References_Model::loadPreferences($userId, 'notification_config');

        if ($userNotificationConfig != null && $userNotificationConfig->receive_assignment_notifications == 1) {
            // Get assigned user language
            $userLanguage = getUserData('language', $userId);
            if (empty($userLanguage)) $userLanguage = vglobal('default_language');
            
            $extraData = [
                'action' => 'assign',
                'assinger' => getUserFullName($entityData->get('modifiedby')),
            ];

            $data = [
                'receiver_id' => $userId,
                'type' => 'notification',
                'related_record_id' => $entityData->getId(),
                'related_record_name' => $entityData->get('label'),
                'related_module_name' => $moduleName,
                'extra_data' => $extraData,
            ];

            $data['message'] = translateNotificationMessage($data, $userLanguage);

            NotificationHelper::sendNotification($data);
        }
    }

    private function sendNotificationsForEvent($entityData) {
        global $adb;
        if (empty($adb)) $adb = PearDatabase::getInstance();

        // Invite send notification goes here
        $id = $entityData->getId();
        $moduleName = $entityData->get('record_module');
        $status = $entityData->get('eventstatus');
        $startDb = $entityData->get('date_start') . ' ' . $entityData->get('time_start');
        $now = new DateTimeField();
        $nowDb = $now->getDBInsertDateTimeValue();

        if($status === 'Planned' && (strtotime($startDb) < strtotime($nowDb))) {
            $invitees = $entityData->get('selectedusers');

            $sql = "SELECT i.inviteeid AS id, u.language FROM vtiger_invitees AS i
                INNER JOIN vtiger_users u ON i.inviteeid = u.id AND u.deleted = 0 AND u.status = 'Active'
                WHERE status <> 'sent' AND activityid = ?
            ";
            $result = $adb->pquery($sql, [$id]);

            if($result) {
                while($row = $adb->fetchByAssoc($result)) {
                    $userId = $row['id'];

                    // Check user reference to send notification
                    $userNotificationConfig = Users_References_Model::loadPreferences($userId, 'notification_config');

                    // [TODO] Make sure if we have to check receive_assignment_notifications or not
                    if($userNotificationConfig != null && $userNotificationConfig->receive_assignment_notifications == 1) {                        
                        $extraData = [
                            'action' => 'invite',
                            'activity_type' => $entityData->get('activitytype'),
                            'inviter' => getUserFullName($entityData->get('modifiedby')),
                        ];

                        $data = [
                            'receiver_id' => $userId,
                            'type' => 'notification',
                            'related_record_id' => $entityData->get('id'),
                            'related_record_name' => $entityData->get('label'),
                            'related_module_name' => $moduleName,
                            'extra_data' => $extraData,
                        ];

                        $data['message'] = translateNotificationMessage($data, $row['language']);

                        NotificationHelper::sendNotification($data);
                    }
                }
            }
        }
    }
}