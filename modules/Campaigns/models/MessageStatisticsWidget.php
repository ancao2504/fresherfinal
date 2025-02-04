<?php

/**
 * Name: MessageStatisticsWidget.php
 * Author: Phu Vo
 * Date: 2020.11.17
 */

class Campaigns_MessageStatisticsWidget_Model {

    public function getWidgetStatisticHeaders() {
        $widgetHeaders = [
            [
                'name' => 'total',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_TOTAL', 'Campaigns'),
            ],
            [
                'name' => 'queued',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_QUEUED', 'Campaigns'),
            ],
            [
                'name' => 'success',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_SUCCESS', 'Campaigns'),
            ],
            [
                'name' => 'failed',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_FAILED', 'Campaigns'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetDataTableHeaders() {
        $widgetHeaders = [
            [
                'name' => 'send_date',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_SEND_DATE', 'Campaigns'),
                'width' => '150px',
            ],
            [
                'name' => 'message',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_SEND_MESSAGE', 'Campaigns'),
                'width' => '300px',
            ],
            [
                'name' => 'total',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_TOTAL', 'Campaigns'),
            ],
            [
                'name' => 'queued',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_QUEUED', 'Campaigns'),
            ],
            [
                'name' => 'success',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_SUCCESS', 'Campaigns'),
            ],
            [
                'name' => 'failed',
                'label' => vtranslate('LBL_MESSAGE_STATISTICS_WIDGET_FAILED', 'Campaigns'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetStatisticData($params = []) {
        $recordId = $params['record'];

        // Total SMS
        $total = self::getSMSOTTMessageLogCount($recordId);

        // Queued
        $queued = self::getSMSOTTMessageLogCount($recordId, 'queued');

        // Success
        $success = self::getSMSOTTMessageLogCount($recordId, 'success');

        // Failed
        $failed = self::getSMSOTTMessageLogCount($recordId, 'failed');

        $result = [
            'total' => self::generateExportLink($total, ['campaign_id' => $recordId]),
            'queued' => self::generateExportLink($queued, ['campaign_id' => $recordId, 'status' => 'queued']),
            'success' => self::generateExportLink($success, ['campaign_id' => $recordId, 'status' => 'success']),
            'failed' => self::generateExportLink($failed, ['campaign_id' => $recordId, 'status' => 'failed']),
        ];

        return $result;
    }

    public function getWidgetDataTableData($params = []) {
        global $adb;

        $recordId = $params['record'];
        $data = [];
        $total = 0;
        $extraWhere = '';
        $extraQueryParams = [];
        $pagingSql = '';

        // Filter search query
        if (!empty($params['search']) && !empty($params['search']['value'])) {
            $extraWhere .= " AND vtiger_smsnotifier.message LIKE ?";
            $extraQueryParams[] = '%' . $params['search']['value'] . '%';
        }

        // Paging
        if (!empty($params['length'])) {
            $pagingSql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $pagingSql .= " OFFSET {$params['start']}";
        }

        $sql = "SELECT
                vtiger_crmentity.crmid,
                vtiger_crmentity.createdtime AS send_date,
                vtiger_smsnotifier.message,
                IFNULL(total.count, 0) AS total,
                IFNULL(queued.count, 0) AS queued,
                IFNULL(success.count, 0) AS success,
                IFNULL(failed.count, 0) AS failed
            FROM vtiger_smsnotifier
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_smsnotifier.smsnotifierid AND vtiger_crmentity.deleted = 0)
            LEFT JOIN (
                SELECT vtiger_cpsmsottmessagelog.related_sms_ott_notifier, COUNT(DISTINCT vtiger_crmentity.crmid) AS count
                FROM vtiger_cpsmsottmessagelog
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
                GROUP BY vtiger_cpsmsottmessagelog.related_sms_ott_notifier
            ) AS total ON (vtiger_smsnotifier.smsnotifierid = total.related_sms_ott_notifier)
            LEFT JOIN (
                SELECT vtiger_cpsmsottmessagelog.related_sms_ott_notifier, COUNT(DISTINCT vtiger_crmentity.crmid) AS count
                FROM vtiger_cpsmsottmessagelog
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_cpsmsottmessagelog.queue_status = 'queued'
                GROUP BY vtiger_cpsmsottmessagelog.related_sms_ott_notifier
            ) AS queued ON (vtiger_smsnotifier.smsnotifierid = queued.related_sms_ott_notifier)
            LEFT JOIN (
                SELECT vtiger_cpsmsottmessagelog.related_sms_ott_notifier, COUNT(DISTINCT vtiger_crmentity.crmid) AS count
                FROM vtiger_cpsmsottmessagelog
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_cpsmsottmessagelog.queue_status = 'success'
                GROUP BY vtiger_cpsmsottmessagelog.related_sms_ott_notifier
            ) AS success ON (vtiger_smsnotifier.smsnotifierid = success.related_sms_ott_notifier)
            LEFT JOIN (
                SELECT vtiger_cpsmsottmessagelog.related_sms_ott_notifier, COUNT(DISTINCT vtiger_crmentity.crmid) AS count
                FROM vtiger_cpsmsottmessagelog
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_cpsmsottmessagelog.queue_status = 'failed'
                GROUP BY vtiger_cpsmsottmessagelog.related_sms_ott_notifier
            ) AS failed ON (vtiger_smsnotifier.smsnotifierid = failed.related_sms_ott_notifier)
            WHERE vtiger_smsnotifier.related_campaign = ? $extraWhere
            GROUP BY vtiger_smsnotifier.smsnotifierid ORDER BY vtiger_crmentity.createdtime DESC $pagingSql";

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_smsnotifier
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_smsnotifier.smsnotifierid AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_smsnotifier.related_campaign = ? $extraWhere";

        // Process params and query
        $queryParams = [$recordId];
        $queryParams = array_merge($queryParams, $extraQueryParams);

        $result = $adb->pquery($sql, $queryParams);
        $total = $adb->getOne($totalSql, $queryParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            // Process format data
            $dateUiType = new Vtiger_Datetime_UIType();
            $row['send_date'] = $dateUiType->getDisplayValue($row['send_date']);
            $row['message'] = self::getMessageLink($row['crmid'], $row['message']);
            $row['total'] = self::generateExportLink($row['total'], ['campaign_id' => $recordId, 'smsnotifier_id' => $row['crmid']]);
            $row['queued'] = self::generateExportLink($row['queued'], ['campaign_id' => $recordId, 'smsnotifier_id' => $row['crmid'], 'status' => 'queued']);
            $row['success'] = self::generateExportLink($row['success'], ['campaign_id' => $recordId, 'smsnotifier_id' => $row['crmid'], 'status' => 'success']);
            $row['failed'] = self::generateExportLink($row['failed'], ['campaign_id' => $recordId, 'smsnotifier_id' => $row['crmid'], 'status' => 'failed']);

            $data[] = $row;
        }

        $result = [
            'draw' => intval($params['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => array_values($data),
            'offset' => $params['start'],
            'length' => $params['length'],
        ];

        return $result;
    }

    public static function getCustomerList($params = []) {
        global $adb;

        $data = [];
        $extraWhere = '';
        $queryParams = [];

        if (!empty($params['campaign_id'])) {
            $extraWhere .= " AND vtiger_cpsmsottmessagelog.related_campaign = ?";
            $queryParams[] = $params['campaign_id'];
        }

        if (!empty($params['smsnotifier_id'])) {
            $extraWhere .= " AND vtiger_cpsmsottmessagelog.related_sms_ott_notifier = ?";
            $queryParams[] = $params['smsnotifier_id'];
        }

        if (!empty($params['status'])) {
            $extraWhere .= " AND vtiger_cpsmsottmessagelog.queue_status = ?";
            $queryParams[] = $params['status'];
        }

        $sql = "SELECT DISTINCT
                vtiger_cpsmsottmessagelog.related_customer,
                vtiger_cpsmsottmessagelog.phone_number
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
            WHERE 1 = 1 {$extraWhere}
            GROUP BY vtiger_cpsmsottmessagelog.related_customer, vtiger_cpsmsottmessagelog.phone_number";

        $result = $adb->pquery($sql, $queryParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $customerName = Vtiger_Functions::getCRMRecordLabel($row['related_customer']);
            $customerType = Vtiger_Functions::getCRMRecordType($row['related_customer']);

            $row['customer_name'] = $customerName;
            $row['customer_type'] = vtranslate($customerType, $customerType);

            $row = decodeUTF8($row);
            $data[] = $row;
        }

        return $data;
    }

    public static function getSMSOTTMessageLogCount($campaignId, $status = '') {
        global $adb;

        $sql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpsmsottmessagelog
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpsmsottmessagelog.cpsmsottmessagelogid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_smsnotifier ON (vtiger_smsnotifier.smsnotifierid = vtiger_cpsmsottmessagelog.related_sms_ott_notifier)
            WHERE vtiger_smsnotifier.related_campaign = ?";

        $queryParams = [$campaignId];

        if (!empty($status)) {
            $sql .= " AND vtiger_cpsmsottmessagelog.queue_status = ?";
            $queryParams[] = $status;
        }

        return $adb->getOne($sql, $queryParams);
    }

    public static function getMessageLink($messageId, $messageLabel) {
        $href = "index.php?module=SMSNotifier&view=Detail&record={$messageId}&mode=showDetailViewByMode&requestMode=full";
        $link = "<a title=\"{$messageLabel}\" href=\"$href\" target=\"_BLANK\">{$messageLabel}</a>";

        return $link;
    }

    public static function generateExportLink($count, $params = []) {
        if ((int) $count == 0) return $count;

        $query = http_build_query($params);
        $url = "index.php?module=Campaigns&action=DetailAjax&mode=exportMessageStatisticExcel";
        if (!empty($query)) $url .= '&' . $query;
        $link = "<a href=\"{$url}\">$count</a>";

        return $link;
    }
    

    public static function getWidgetStatisticForListView($params) {
        $widgetModel = new self();

        $htmlString = '';
        $headers = $widgetModel->getWidgetStatisticHeaders($params);
        $headersNo = count($headers);
        $data = $widgetModel->getWidgetStatisticData($params);

        $htmlString .= '<ul>';

        foreach ($headers as $index => $header) {
            $value = $data[$header['name']];
            $htmlString .= '<li style="white-space: nowrap">';
            $htmlString .= $header['label'] . ': ' . $value;
            $htmlString .= '</li>';
        }
        
        $htmlString .= '</ul>';

        return $htmlString;
    }
}