<?php
/**
 * @author Tin Bui
 * @email tin.bui@onlinecrm.vn
 * @create date 2022.03.16
 * @desc Ticket general utils
 */

class HelpDesk_GeneralUtils_Helper {

    static function getFieldModelFromName($fieldName, $recordModel) {
        $fieldModel = $recordModel->getField($fieldName);
        $fieldModel->set('fieldvalue', $recordModel->get($fieldName));
        return $fieldModel;
    }

    public static function getUpdateStatusRecordStucture($recordModel) {
        $fieldLayout = [
            ['ticketstatus', 'ticketcategories'],
            ['assigned_user_id', 'ticketpriorities'],
            ['related_cpslacategory', 'is_send_survey'],
            ['helpdesk_over_sla_reason', 'over_sla_note'],
        ];

        $surveyStatus = trim($recordModel->get('helpdesk_survey_status'));
        $isOverSLA = $recordModel->get('over_sla') == 'on' || $recordModel->get('over_sla') == 1;
        $structure = [];
        
        foreach ($fieldLayout as $rowKey => $row) {
            
            foreach ($row as $fieldName) {
                // If system sent survey email, not show send survey checkbox
                if ((isForbiddenFeature('CustomerSurveyOnTicket') || !in_array($surveyStatus, ['not_yet_sent_mail', 'customer_reopen_ticket'])) && $fieldName == 'is_send_survey') {
                    continue;
                }

                if (isForbiddenFeature('SLAManagement') && $fieldName == 'related_cpslacategory') {
                    continue;
                }

                if ((isForbiddenFeature('SLAManagement') || !$isOverSLA) && in_array($fieldName, ['helpdesk_over_sla_reason', 'over_sla_note'])) {
                    continue;
                }

                $structure[$rowKey][$fieldName] = self::getFieldModelFromName($fieldName, $recordModel);
            }
        }

        return $structure;
    }

    public static function getTicketStatusHistoryLog($id) {
        global $adb;

        $sql = "SELECT old_status, new_status, timestamp, user_id
                FROM helpdesk_ticket_status_change_log
                WHERE id = ?
                ORDER BY timestamp DESC";
        $result = $adb->pquery($sql, [$id]);
        $statusLogs = [];

        while ($result && $row = $adb->fetchByAssoc($result)) {
            $replaceVariables = [
                'user_name' => getUserFullName($row['user_id'])
            ];

            if (!empty($row['old_status'])) {
                $replaceVariables = array_merge($replaceVariables, [
                    'old_status' => vtranslate($row['old_status'], 'HelpDesk'),
                    'new_status' => vtranslate($row['new_status'], 'HelpDesk')
                ]);
                $message = vtranslate('LBL_UPDATED_STATUS_FROM_TO', 'HelpDesk');
            } 
            else {
                $message = vtranslate('LBL_OPENED_TICKET', 'HelpDesk');
            }

            foreach ($replaceVariables as $variable => $value) {
                $message = str_replace("%{$variable}%", $value, $message);
            }
            
            $statusLog = [
                'user_avt_url' => '',
                'message' => $message,
                'timestamp' => DateTimeField::convertToUserFormat($row['timestamp'])
            ];
            
            $userRecord = Users_Record_Model::getInstanceById($row['user_id'], 'Users');
            $userAvt = $userRecord->getImageDetails();
            
            if (count($userAvt)) {
                $avt = (array) $userAvt[0];
                $statusLog['user_avt_url'] = "{$avt['path']}_{$avt['orgname']}";
            }
            
            $statusLogs[] = $statusLog;
        }

        return $statusLogs;
    }

    public static function displayRatingStars($rating, $status) {
        $smarty = new Vtiger_Viewer();            
        $rating = str_replace('rating_', '', $rating);
        $smarty->assign('CURRENT_STAR', $rating);
        $smarty->assign('TICKET_STATUS', $status);
        
        return $smarty->fetch('modules/HelpDesk/tpls/RatingReadView.tpl');
    }
    
    public static function sendTicketRepliedNotification($ticketId, $ticketRecord = null) {
        if (empty($ticketRecord)) {
            if (empty($ticketId) || !isRecordExists($ticketId)) return;
            $ticketRecord = HelpDesk_Record_Model::getInstanceById($ticketId);
        }
    
        $mainOwnerUserId = $ticketRecord->get('main_owner_id');

        if (!empty($mainOwnerUserId)) {
            // Check assigned User notification config to decide send notification or not
            $userNotificationConfig = Users_Preferences_Model::loadPreferences($mainOwnerUserId, 'notification_config');
    
            if ($userNotificationConfig != null && $userNotificationConfig->receive_notifications == 1) {
                // Peform send notification action
                $userLanguage = getUserData('language', $mainOwnerUserId);
                $userTimezone = getUserData('time_zone', $mainOwnerUserId);

                $extraData = [
                    'action' => 'customer_reply_ticket_email',
                    'ticket_number' => trim($ticketRecord->get('ticket_no'))
                ];
                $data = [
                    'receiver_id' => $mainOwnerUserId,
                    'type' => 'notification',
                    'related_record_id' => $ticketRecord->getId(),
                    'related_record_name' => $ticketRecord->get('label'),
                    'related_module_name' => $ticketRecord->getModuleName(),
                    'extra_data' => $extraData,
                ];
 
                $data['message'] = translateNotificationMessage($data, $userLanguage, $userTimezone);
    
                NotificationHelper::sendNotification($data);
            }
        }
    }

    public static function getPicklistOptions($fieldName, $module) {
        $pickList = Vtiger_Util_Helper::getPickListValues($fieldName, '');
        $pickListOption = [];
       
        foreach($pickList as $value) {
            $pickListOption[$value] = vtranslate($value, $module);
        }

        return $pickListOption;
    }

    public static function validateRecordStatus($newStatus, $recordModel) {
        $result = ['isValid' => true];

		if (!isForbiddenFeature('SLAManagement') && in_array($newStatus, ['Wait Close', 'Closed']) && empty($recordModel->get('related_cpslacategory'))) {
			$result['missingFields'] = ['related_cpslacategory'];
			$result['isValid'] = false;
		}

		return $result;
    }

    public static function autoCloseWaitCloseTicket() {
        global $adb, $ticketConfigs;

        if (empty($ticketConfigs['auto_close_hours'])) return;

        $sql = "SELECT t.ticketid
            FROM vtiger_troubletickets AS t
            INNER JOIN vtiger_crmentity AS te ON te.crmid = t.ticketid AND te.deleted = 0
            INNER JOIN helpdesk_ticket_status_change_log AS l ON l.id = t.ticketid AND l.new_status = 'Wait Close'
            WHERE t.status = 'Wait Close'
            GROUP BY t.ticketid
            HAVING TIMESTAMPDIFF(HOUR, MAX(l.timestamp), NOW()) > ?";

        $result = $adb->pquery($sql, [$ticketConfigs['auto_close_hours']]);

        while ($result && $row = $adb->fetchByAssoc($result)) {
            // Trigger save events by record model
            $ticketRecord = HelpDesk_Record_Model::getInstanceById($row['ticketid']);
            $ticketRecord->set('mode', 'edit');
            $ticketRecord->set('ticketstatus', 'Closed');
            $ticketRecord->save();
        }
    }

    public static function getOverSLARecordStucture($recordModel) {
        $fields = ['helpdesk_over_sla_reason', 'over_sla_note'];
        $structure = [];
        
        foreach ($fields as $fieldName) {
            $structure[$fieldName] = self::getFieldModelFromName($fieldName, $recordModel);
        }

        return $structure;
    }

    public static function getFileUploadValidatorConfigs() {
        global $ticketConfigs, $validationConfig;

		$fileValidatorConfigs = $ticketConfigs['file_upload_validation'];
		$fileValidatorConfigs['allowed_upload_file_exts'] = $validationConfig['allowed_upload_file_exts'];

        return $fileValidatorConfigs;
    }
}