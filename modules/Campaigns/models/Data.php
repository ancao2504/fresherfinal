<?php

/*
*	Data.php
*	Author: Phuc Lu
*	Date: 2019.08.01
*   Purpose: handle record for Campaign
*/

class Campaigns_Data_Model {

    static function getAllCampaigns($addAllOption = false, $from = null, $to = null){
        global $adb;
        $campaigns = [];
        $sqlExt = '1 = 1';

        if ($addAllOption) {
            $campaigns['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        if (!empty($from)) {
            $sqlExt .= " AND createdtime >= '{$from}'";
        }
        
        if (!empty($from)) {
            $sqlExt .= " AND createdtime <= '{$to}'";
        }

        $sql = "SELECT crmid, campaignname FROM vtiger_campaign INNER JOIN vtiger_crmentity ON (crmid = campaignid AND deleted = 0) WHERE {$sqlExt} ORDER BY createdtime DESC";
        $result = $adb->pquery($sql);
    
        while ($row = $adb->fetchByAssoc($result)) {
            $campaigns[$row['crmid']] = trim($row['campaignname']);
        }
        
        return $campaigns;
    }

    // Implemented by Hieu Nguyen on 2020-11-13
    static function getCampaignInfo($campaignId) {
        global $adb;
        $sql = "SELECT c.campaignid AS id, c.campaignname AS name, c.campaigns_purpose AS purpose, c.campaignstatus AS status, c.closingdate AS end_date
            FROM vtiger_campaign AS c
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = c.campaignid AND e.deleted = 0 AND e.setype = 'Campaigns')
            WHERE c.campaignid = ?";
        $result = $adb->pquery($sql, [$campaignId]);
        $campaignInfo = $adb->fetchByAssoc($result);

        return decodeUTF8($campaignInfo);
    }

    // Implemented by Hieu Nguyen on 2020-11-13
    static function getLinkTargetListsWithCustomersCount($campaignId) {
        global $adb;

        $sql = "SELECT t.cptargetlistid AS id, t.name
            FROM vtiger_cptargetlist AS t
            INNER JOIN vtiger_crmentityrel AS r ON (r.relcrmid = t.cptargetlistid AND r.module = 'Campaigns' AND r.crmid = ?)
            INNER JOIN vtiger_crmentity AS te ON (te.crmid = r.relcrmid AND te.deleted = 0 AND te.setype = 'CPTargetList')";
        $result = $adb->pquery($sql, [$campaignId]);
        $targetLists = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row['customers_count'] = self::getCustomersCountFromTargetList($row['id'], true);
            $targetLists[] = decodeUTF8($row);
        }

        return $targetLists;
    }

    // Implemented by Hieu Nguyen on 2020-11-13
    static function getCustomersCountFromTargetList($targetListId, $ignoreConvertedRecords = false) {
        global $adb;

        $sql = "SELECT (
                (SELECT COUNT(rel.relcrmid)
                FROM vtiger_crmentityrel AS rel
                INNER JOIN vtiger_contactdetails AS c ON (c.contactid = rel.relcrmid)
                INNER JOIN vtiger_crmentity AS ce ON (ce.crmid = c.contactid AND ce.deleted = 0)
                WHERE rel.module = 'CPTargetList' AND rel.crmid = ? AND rel.relmodule = 'Contacts')
                +
                (SELECT COUNT(rel.relcrmid)
                FROM vtiger_crmentityrel AS rel
                INNER JOIN vtiger_leaddetails AS l ON (l.leadid = rel.relcrmid)
                INNER JOIN vtiger_crmentity AS le ON (le.crmid = l.leadid AND le.deleted = 0)
                WHERE rel.module = 'CPTargetList' AND rel.crmid = ? AND rel.relmodule = 'Leads'
                ". ($ignoreConvertedRecords ? "AND l.converted != 1" : '') .")
                +
                (SELECT COUNT(rel.relcrmid)
                FROM vtiger_crmentityrel AS rel
                INNER JOIN vtiger_cptarget AS t ON (t.cptargetid = rel.relcrmid)
                INNER JOIN vtiger_crmentity AS te ON (te.crmid = t.cptargetid AND te.deleted = 0)
                WHERE rel.module = 'CPTargetList' AND rel.crmid = ? AND rel.relmodule = 'CPTarget'
                ". ($ignoreConvertedRecords ? "AND t.cptarget_status != 'Converted'" : '') .")
            ) AS count";

        $count = $adb->getOne($sql, [$targetListId, $targetListId, $targetListId]);
        return $count;
    }

    // Implemented by Hieu Nguyen on 2020-11-16
    static function getCustomerIdsFromTargetLists(array $targetLists, $ignoreConvertedRecords = false) {
        $customerIds = [];

        foreach ($targetLists as $targetListId) {
            $targetListCustomerIds = self::getCustomerIdsFromTargetList($targetListId, $ignoreConvertedRecords);
            $customerIds = array_merge_recursive($customerIds, $targetListCustomerIds);
        }

        // Make the ids unique
        foreach ($customerIds as $moduleName => $ids) {
            $customerIds[$moduleName] = array_unique($ids, SORT_REGULAR);
        }

        return $customerIds;
    }

    // Implemented by Hieu Nguyen on 2020-11-16
    static function getCustomerIdsFromTargetList($targetListId, $ignoreConvertedRecords = false) {
        $customerIds = [
            'Leads' => CPTargetList_Data_Model::getRelatedIds($targetListId, 'Leads', $ignoreConvertedRecords),
            'Contacts' => CPTargetList_Data_Model::getRelatedIds($targetListId, 'Contacts', $ignoreConvertedRecords),
            'CPTarget' => CPTargetList_Data_Model::getRelatedIds($targetListId, 'CPTarget', $ignoreConvertedRecords)
        ];

        return $customerIds;
    }

    // Implemented by Hieu Nguyen on 2020-11-17
    static function getCustomersWithPhoneNumber(array $targetLists, array $phoneFields, $ignoreConvertedRecords = false) {
        global $adb;
        $customerIdsMap = self::getCustomerIdsFromTargetLists($targetLists, $ignoreConvertedRecords);
        $customerIds = array_merge_recursive($customerIdsMap['Contacts'], $customerIdsMap['Leads'], $customerIdsMap['CPTarget']);
        $customerIdsForQuery = join("', '", $customerIds);

        $sql = "SELECT e.label AS full_name, cd.phone, cd.mobile 
            FROM vtiger_contactdetails AS cd
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = cd.contactid AND e.setype = 'Contacts' AND e.deleted = 0)
            WHERE cd.contactid IN ('{$customerIdsForQuery}')

            UNION ALL

            SELECT e.label AS full_name, la.phone, la.mobile 
            FROM vtiger_leaddetails AS l
            INNER JOIN vtiger_leadaddress AS la ON (la.leadaddressid = l.leadid)
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = la.leadaddressid AND e.setype = 'Leads' AND e.deleted = 0)
            WHERE la.leadaddressid IN ('{$customerIdsForQuery}') ". ($ignoreConvertedRecords ? "AND l.converted != 1" : '') ."

            UNION ALL

            SELECT e.label AS full_name, t.phone, t.mobile 
            FROM vtiger_cptarget AS t
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = t.cptargetid AND e.setype = 'CPTarget' AND e.deleted = 0)
            WHERE t.cptargetid IN ('{$customerIdsForQuery}') ". ($ignoreConvertedRecords ? "AND t.cptarget_status != 'Converted'" : '');
        $result = $adb->pquery($sql, []);
        $customers = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $fullName = trim($row['full_name']);
            if (empty($fullName)) continue;

            foreach ($phoneFields as $phoneField) {
                $phoneNumber = $row[$phoneField];
                if (empty($phoneNumber)) continue;

                $customers[] = [
                    'full_name' => $fullName,
                    'phone_number' => $phoneNumber
                ];
            }
        }

        return $customers;
    }

    // Implemented by Hieu Nguyen on 2020-11-13
    static function getSMSAndOTTMessageTemplates($channel = '') {
        global $adb;
        
        $sql = "SELECT t.cpsmstemplateid AS id, t.name, e.description AS message 
            FROM vtiger_cpsmstemplate AS t
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = t.cpsmstemplateid AND e.setype = 'CPSMSTemplate' AND e.deleted = 0)";
        $params = [];
        
        if (!empty($channel)) {
            $sql .= " WHERE t.sms_ott_message_type = ?";
            $params = [$channel];
        }

        $result = $adb->pquery($sql, $params);
        $templates = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $templates[] = decodeUTF8($row);
        }

        return $templates;
    }

    // Implemented by Hieu Nguyen on 2020-11-16
    static function getEmailTemplates() {
        global $adb;
        
        $sql = "SELECT templateid AS id, templatename AS name, subject, body FROM vtiger_emailtemplates WHERE deleted = 0";
        $params = [];

        $result = $adb->pquery($sql, $params);
        $templates = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $templates[] = $row;
        }

        return $templates;
    }

    // Implemented by Hieu Nguyen on 2020-11-26
    static function getPartnerContacts() {
        global $adb;

        $fullNameConcatSql = getSqlForNameInDisplayFormat(['firstname' => 'c.firstname', 'lastname' => 'c.lastname'], 'Contacts');
        $sql = "SELECT c.contactid AS id, {$fullNameConcatSql} AS name, c.email
            FROM vtiger_contactdetails AS c
            INNER JOIN vtiger_crmentity AS ce ON (ce.crmid = c.contactid AND ce.deleted = 0)
            WHERE c.contacts_type = 'Partner'";
        $result = $adb->pquery($sql, []);
        $contacts = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $contacts[] = $row;
        }

        return $contacts;
    }

    // Implemented by Hieu Nguyen on 2020-11-26
    static function linkEmailLog($campaignId, $emailLogId) {
        global $adb;
        $sql = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule) VALUES (?, 'Campaigns', ?, 'Emails')";
        $adb->pquery($sql, [$campaignId, $emailLogId]);
    }

    // Implemented by Hieu Nguyen on 2020-11-16
    static function addNewSMSOTTMessageNotifier(array $data) {
        global $current_user;
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');

        $smsOTTNotifier = Vtiger_Record_Model::getCleanInstance('SMSNotifier');
        $smsOTTNotifier->set('name', "[{$data['channel']}] {$current_user->user_name} - {$currentDate} {$currentTime}");
        $smsOTTNotifier->set('related_campaign', $data['campaign_id']);
        $smsOTTNotifier->set('sms_ott_message_type', $data['channel']);
        $smsOTTNotifier->set('message', $data['message']);
        $smsOTTNotifier->save();

        return $smsOTTNotifier->getId();
    }

    // Implemented by Hieu Nguyen on 2020-11-16
    static function updateSMSOTTMessageQueueStatus($id, string $status, array $data) {
        $messageQueue = Vtiger_Record_Model::getInstanceById($id, 'CPSMSOTTMessageLog');
        $messageQueue->set('mode', 'edit');
        $messageQueue->set('queue_status', $status);
        $messageQueue->set('attempt_count', $messageQueue->get('attempt_count') + 1);
        $messageQueue->set('last_attempt_time', date('H:i:s'));

        if ($status == 'success') {
            $messageQueue->set('tracking_id', $data['tracking_id']);
            $messageQueue->set('description', '');
        }

        if ($status == 'failed') {
            $messageQueue->set('description', $data['error_message']);
        }

        $messageQueue->save();
    }
}