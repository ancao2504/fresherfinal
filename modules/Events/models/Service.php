<?php

/*
    Class Events_Service_Model
    Author: Hieu Nguyen
    Date: 2019-04-01
    Purpose: handle sending reminder for upcomming activities before they start
*/

require_once('vtlib/Vtiger/Mailer.php');
require_once('include/utils/NotificationHelper.php');

class Events_Service_Model extends Vtiger_Base_Model {

    static function sendInvitation() {
        global $adb, $current_user;
        $log = LoggerManager::getLogger('PLATFORM'); 
        $log->info('[CRON] Started sendInvitation');

        try {
            // Backup current user before changing
            $currentUser = $current_user;

            // Delete invitation from deleted events
            $sql = 'DELETE FROM vtiger_invitees 
                WHERE activityid NOT IN (SELECT crmid FROM vtiger_crmentity WHERE deleted = 0 AND setype = "Calendar")';
            $adb->pquery($sql, []);

            // Process queue (skip recurrence events)
            $sql = "SELECT activityid, inviteeid, invitee_type FROM vtiger_invitees 
                WHERE is_recurrence = 0 AND status IN ('Queued', 'Failed') AND attempt_count < 5 ORDER BY activityid";
            $result = $adb->pquery($sql, []);

            // Cache data
            $eventId = '';
            $eventRecordModel = null;
            $cacheData = [];
            $icsFile = '';

            while ($row = $adb->fetchByAssoc($result)) {
                if ($row['activityid'] != $eventId) {
                    unlink($icsFile);   // Remove previous cache file
                    $eventId = $row['activityid'];
                    $eventRecordModel = Vtiger_Record_Model::getInstanceById($eventId, 'Events');
                    $cacheData = decodeUTF8($eventRecordModel->getInviteUserMailData());
                    $icsFile = generateIcsAttachment($cacheData, 'invitation'); // [Calendar] Added suffix by Phu Vo on 2019.12.11
                }

                $inviteeId = $row['inviteeid'];
                $inviteeType = $row['invitee_type'];

                if (empty($inviteeId) || empty($inviteeType)) continue;
                $inviteeInfo = Events_Invitation_Helper::getInviteeInfo($inviteeId, $inviteeType);

                // No invitee found, delete this invitation
                if (empty($inviteeInfo)) {
                    $adb->pquery("DELETE FROM vtiger_invitees WHERE activityid = ? AND inviteeid = ?", [$eventId, $inviteeId]);
                    continue;
                }

                // Invitee found but without email
                if (empty($inviteeInfo['email'])) {
                    $sql = "UPDATE vtiger_invitees SET status = 'Failed', failed_reason = 'No email', attempt_count = attempt_count + 1 
                        WHERE activityid = ? AND inviteeid = ?";
                    $adb->pquery($sql, [$eventId, $inviteeId]);
                    continue;
                }

                // Change current user for language translation
                $referenceUserId = ($inviteeType == 'Users') ? $inviteeId : $eventRecordModel->get('createdby');
                $referenceUser = Vtiger_Record_Model::getInstanceById($referenceUserId, 'Users');
                $current_user = $referenceUser->entity;

                // Invitee with email found, send invitation
                $systemEmailInfo = getSystemEmailAddress();
                $invitationBody = Events_Invitation_Helper::getInvitationEmailBody($eventRecordModel, $cacheData, $inviteeInfo);
                $invitationBody = getMergedDescription($invitationBody, $eventId, 'Events');

                $mail = new Vtiger_Mailer();
                $mail->IsHTML(true);
                $mail->ConfigSenderInfo($systemEmailInfo['email'], $systemEmailInfo['name']);
                $mail->Subject = vtranslate('LBL_INVITATION', 'Calendar') .': '. decodeUTF8($cacheData['subject']);
                $mail->Body = $invitationBody;
                $mail->AddAttachment($icsFile, '', 'base64', 'text/calendar');
                $success = $mail->SendTo($inviteeInfo['email'], $inviteeInfo['name'], false, false, true);
                
                // Send success
                if ($success) {
                    // Send invitation notification message if the invitee is an user
                    if ($inviteeType == 'Users') {
                        sendInviteNotification($eventRecordModel, $inviteeId, $inviteeInfo['language'], $inviteeInfo['user_timezone']);
                    }

                    // Mark this invitation as sent
                    $sql = "UPDATE vtiger_invitees SET status = 'Sent', failed_reason = '', attempt_count = attempt_count + 1 
                        WHERE activityid = ? AND inviteeid = ?";
                    $adb->pquery($sql, [$eventId, $inviteeId]);
                }
                // Send error, mark this invitation as failed to retry later
                else {
                    $sql = "UPDATE vtiger_invitees SET status = 'Failed', failed_reason = 'Sent failed', attempt_count = attempt_count + 1 
                        WHERE activityid = ? AND inviteeid = ?";
                    $adb->pquery($sql, [$eventId, $inviteeId]);
                }
            }

            // Restore current user for other cron task
            $current_user = $currentUser;

            $log->info('[CRON] Finished sendInvitation');
        }
        catch (Exception $ex) {
            $log->info('[CRON] sendInvitation error: '. $ex->getMessage());
        }
    }

    static function sendReminderMsg() {
        global $adb;
        $log = LoggerManager::getLogger('PLATFORM'); 
        $log->info('[CRON] Started sendReminderMsg');
        $jobName = 'EventsReminder';

        try {
            $config = Settings_Vtiger_Config_Model::loadConfig('notification_config');
            $minutesBefore = intval($config->minutes_to_notify_activitites_before_start_time);

            // Get activities that were marked status = Planned and sendnotification = 1 to send notification
            // Modified sql by Phu Vo on 2019.04.10 select user time_zone, start time
            // Modified by Phu Vo on 2019.07.09 to process with Custom Owner Field
            $sql = "SELECT a.activityid, a.subject, a.activitytype, a.location, ae.description, CONCAT(a.date_start, ' ', a.time_start) AS time_start,
                    TIMESTAMPDIFF(MINUTE, NOW(), CONCAT(a.date_start, ' ', a.time_start)) AS start_in_mins, 
                    cl.execute_time, ae.smownerid
                FROM vtiger_activity AS a
                INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = activityid AND ae.setype = 'Calendar' AND ae.deleted = 0)
                LEFT JOIN vtiger_cron_task_log AS cl ON (cl.user_id = ae.smownerid AND cl.record_id = a.activityid AND job_name = 'EventsReminder')
                WHERE CONCAT(COALESCE(a.status, ''), COALESCE(a.eventstatus, '')) = 'Planned' AND sendnotification = 1
                AND NOW() BETWEEN DATE_SUB(CONCAT(a.date_start, ' ', a.time_start), INTERVAL {$minutesBefore} MINUTE) AND CONCAT(a.date_start, ' ', a.time_start)";
            // End Phu Vo
            $params = [];
            $result = $adb->pquery($sql, $params);

            while ($row = $adb->fetchByAssoc($result)) {
                // Skip this message if it was already sent one within the time range
                if (!empty($row['execute_time']) && time() - strtotime($row['execute_time']) <= $minutesBefore * 60) continue;

                // Process to send reminder to all user (Sand alone user or group member)
                $ownerId = $row['smownerid'];
                $ownerType = vtws_getOwnerType($ownerId);

                if ($ownerType === 'Users') {
                    // Stand alone User
                    self::sendReminderToUser($ownerId, $row, $jobName);
                }
                elseif ($ownerType === 'Groups') {
                    // Doesn't care it is normal or custom group, just send notify to all group member
                    $ownerIds = getGroupMemberIds($ownerId);

                    foreach($ownerIds as $memberId) {
                        self::sendReminderToUser($memberId, $row, $jobName);
                    }
                }

                // Write log
                $sqlInsertLog = "INSERT INTO vtiger_cron_task_log(user_id, record_id, job_name) VALUES({$ownerId}, {$row['activityid']}, '{$jobName}')";
                
                if (!$adb->pquery($sqlInsertLog, [])) {
                    $sqlUpdateLog = "UPDATE vtiger_cron_task_log SET execute_time = NOW() WHERE user_id = {$ownerId} AND record_id = {$row['activityid']} AND job_name = '{$jobName}'";
                    $adb->pquery($sqlUpdateLog, []);
                }
            }

            $log->info('[CRON] Finished sendReminderMsg');
        }
        catch (Exception $ex) {
            $log->info('[CRON] sendReminderMsg error: '. $ex->getMessage());
        }
    }

    /**
     * Static method send reminder to single user
     * @param Number $ownerId Receive user id
     * @param Array $data
     * @author Phu Vo (2019.07.09)
     */
    private static function sendReminderToUser($ownerId, $data) {
        // Prepare message
        $userLanguage = getUserData('language', $ownerId) ?? 'en_us';
        $hourFormat = getUserData('hour_format', $ownerId) == '12' ? 'h:i A' : 'H:i';
        $moduleStrings = Vtiger_Language_Handler::getModuleStringsFromFile($userLanguage, 'Events');
        $languageStrings = $moduleStrings['languageStrings'];

        $translatedActivityType = getTranslatedString($data['activitytype'], 'Events', $userLanguage);
        $reminderMsg = $languageStrings['LBL_REALTIME_REMINDER_MSG'];
        $reminderLocation = $languageStrings['LBL_REALTIME_REMINDER_LOCATION'];
        $reminderDescription = $languageStrings['LBL_REALTIME_REMINDER_DESCRIPTION'];

        if($data['start_in_mins'] > 60) {
            $hours = floor($data['start_in_mins'] / 60);
            $mins = $data['start_in_mins'] % 60;
            $startInTime = replaceKeys($languageStrings['LBL_REALTIME_REMINDER_HOURS_AND_MINS'], ['%hours' => $hours, '%mins' => $mins]);
        }
        else {
            $mins = $data['start_in_mins'];
            $startInTime = replaceKeys($languageStrings['LBL_REALTIME_REMINDER_MINS'], ['%mins' => $mins]);
        }

        $data = decodeUTF8($data);    // Convert encoded strings into readable strings

        // Generate message
        $message = [
            'receiver_id' => $ownerId,
            'message' => replaceKeys($reminderMsg, ['%activity_type' => $translatedActivityType, '%activity_name' => $data['subject'], '%time' => $startInTime]),
            'type' => 'popup',
            'related_record_id' => $data['activityid'], 
            'related_record_name' => $data['subject'],
            'related_module_name' => 'Calendar',
            'extra_data' => []
        ];

        if($data['activitytype'] == 'Meeting') {
            $message['extra_data']['location'] = replaceKeys($reminderLocation, ['%location' => $data['location']]);
        }

        // add start time (added by Phu Vo on 2019.04.10)
        $dbTimeZone = new DateTimeZone(DateTimeField::getDBTimeZone());
        $timezoneName = getUserData('time_zone', $ownerId) ?? vglobal('default_timezone');
        $startTimeObj = new DateTime($data['time_start'], $dbTimeZone);
        $startTimeObj->setTimezone(new DateTimeZone($timezoneName));

        $message['message'] .= " <i>({$startTimeObj->format($hourFormat)})</i>";
        // end add start time to message

        $message['extra_data']['description'] = replaceKeys($reminderDescription, ['%description' => $data['description']]);

        // Send message
        NotificationHelper::sendNotification($message, false);
    }

    // Added by Phuc on 2019.11.27 to send email reminder
    function sendCalendarReminder() {
        require_once('vtlib/Vtiger/Mailer.php');
        global $adb;
        $log = LoggerManager::getLogger('PLATFORM'); 
        $log->info('[CRON] Started sendCalendarReminder');

        try {
            // Query for all events/tasks. Refactor by Phu Vo on 2020.03.20
                $sql = "SELECT vtiger_activity.*
                FROM vtiger_activity
                INNER JOIN vtiger_crmentity ON (vtiger_activity.activityid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
                INNER JOIN vtiger_activity_reminder ON (vtiger_activity.activityid = vtiger_activity_reminder.activity_id)
                LEFT OUTER JOIN vtiger_seactivityrel ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid)
                WHERE vtiger_activity_reminder.reminder_sent = 0
                    AND vtiger_activity_reminder.reminder_time > 0
                    AND (NOW() BETWEEN DATE_ADD(CONCAT(date_start, ' ', time_start), INTERVAL - vtiger_activity_reminder.reminder_time MINUTE) AND CONCAT(date_start, ' ', time_start))";
            // End Phu Vo

            $result = $adb->pquery($sql, []);
            
            // Get company name and system email
            $systemEmailInfo = getSystemEmailAddress();			
            
            while ($activity = $adb->fetchByAssoc($result)) {
                $eventId = $activity['activityid'];
                $eventRecordModel = Vtiger_Record_Model::getInstanceById($eventId, 'Events');
                $cacheData = decodeUTF8($eventRecordModel->getInviteUserMailData());

                // Get all invitees
                $invitees = Events_Data_Model::getInvitees($eventId);

                // Get assigned users
                $assignedUserIds = [];
                $assignedUserId = $eventRecordModel->get('assigned_user_id');
                if (vtws_getOwnerType($assignedUserId) == 'Users') {
                    $assignedUserIds[] = [
                        'inviteeid' => $assignedUserId,
                        'invitee_type' => 'Users'
                    ];
                }
                else {
                    $userGroupIds = getGroupMemberIds($assignedUserId);
                    $userGroupIds = array_unique($userGroupIds);

                    foreach ($userGroupIds as $userId) {
                        $assignedUserIds[] = [
                            'inviteeid' => $userId,
                            'invitee_type' => 'Users'
                        ];
                    }
                }

                $receiverIds = array_merge($invitees, $assignedUserIds);

                // Modified by Phu Vo on 2019.12.11 to update activity reminder status base on mailer result
                $successCount = 0;

                if (count($receiverIds)) {
                    foreach ($receiverIds as $receiver) {
                        $receiverInfo = Events_Invitation_Helper::getInviteeInfo($receiver['inviteeid'], $receiver['invitee_type']);;

                        // No receiver found or do not have email, skip this
                        if (empty($receiverInfo) || empty($receiverInfo['email'])) {
                            continue;
                        }
                        
                        $invitationSubjectAndBody = self::getReminderEmailSubjectAndBody($eventRecordModel, $cacheData, $receiverInfo);

                        $mail = new Vtiger_Mailer();
                        $mail->IsHTML(true);
                        $mail->_serverConfigured = true;
                        $mail->ConfigSenderInfo($systemEmailInfo['email'], $systemEmailInfo['name']);
                        $mail->Subject = $invitationSubjectAndBody['subject'];
                        $mail->Body = $invitationSubjectAndBody['body'];
                        $emailResult = $mail->SendTo($receiverInfo['email'], $receiverInfo['name'], false, false, true);

                        // Success Count use to update activity remider status
                        if ($emailResult == true) $successCount++; 
                    }
                }

                if (count($receiverIds) == 0 || $successCount > 0) {
                    $sql = "UPDATE vtiger_activity_reminder SET reminder_sent = 1 WHERE activity_id = ?";
                    $adb->pquery($sql, [$eventId]);
                }
                // End Phu Vo
            }   
            
            $log->info('[CRON] Finished sendCalendarReminder');
        }
        catch (Exception $ex) {
            $log->info('[CRON] sendCalendarReminder error: '. $ex->getMessage());
        }
    }

    // Added by Phuc on 2019.11.28
    private static function getReminderEmailSubjectAndBody($eventRecordModel, array $invitationData, array $inviteeInfo) {
        require_once('include/utils/utils.php');
        global $adb;

        $inviteeId = $inviteeInfo['id'];
        $eventId = $eventRecordModel->getId();
        $startDate = new DateTimeField($invitationData['st_date_time']);
        $endDate = new DateTimeField($invitationData['end_date_time']);

        if ($inviteeInfo['type'] == 'Users') {
            $dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($inviteeId, 'Users');
        }
        else {
            $dateTimeFormatReferenceUser = Vtiger_Record_Model::getInstanceById($creatorId, 'Users');
        }

        // Get email template and replace variables
        if ($eventRecordModel->get('activitytype') == 'Task') {
            $query = "SELECT body FROM vtiger_emailtemplates WHERE templatename = ? AND systemtemplate = 1";
            $body = $adb->getOne($query, ['ToDo Reminder']);

            $body = str_replace('$calendar-subject$', decode_html($eventRecordModel->get('subject')), $body);
            $body = str_replace('$calendar-description$', nl2br(decode_html($eventRecordModel->get('description'))), $body);    // Modified by Hieu Nguyen on 2021-08-10 to display line break in email
            $body = str_replace('$calendar-date_start$', $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
            $body = str_replace('$calendar-due_date$', $endDate->getDisplayDate($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body); // [Calendar] Modified by Phu Vo on 2020.03.20

        }
        else {
            $query = "SELECT body FROM vtiger_emailtemplates WHERE templatename = ? AND systemtemplate = 1";
            $body = $adb->getOne($query, ['Activity Reminder']);

            $body = str_replace('$events-subject$', decode_html($eventRecordModel->get('subject')), $body);
            $body = str_replace('$events-description$', nl2br(decode_html($eventRecordModel->get('description'))), $body);  // Modified by Hieu Nguyen on 2021-08-10 to display line break in email
            $body = str_replace('$events-date_start$', $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
            $body = str_replace('$events-due_date$', $endDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser) .' '. vtranslate($dateTimeFormatReferenceUser->time_zone, 'Users'), $body);
            $body = str_replace('$events-contactid$', $invitationData['contact_name'], $body);
        }

        $body = replaceRecordDetailLink($inviteeInfo['type'], $eventId, $body);

        // [Calendar] Modified by Phu Vo on 2020.03.20
        if ($eventRecordModel->get('activitytype') == 'Task') {
            $body = getMergedDescription($body, $eventId, 'Calendar');
            $subject = vtranslate('Activity Reminder', 'Calendar') . ': ' . decode_html($eventRecordModel->get('subject')) . " @ ". $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser);
        }
        else {
            $body = getMergedDescription($body, $eventId, 'Events');            
            $subject = vtranslate('Reminder', 'Calendar') . ': ' . decode_html($eventRecordModel->get('subject')) . " @ " . $startDate->getDisplayDateTimeValue($dateTimeFormatReferenceUser);
        }
        // End Phu Vo
            
        return ['subject' => $subject, 'body' => $body];
    }
    // Ended by Phuc
}