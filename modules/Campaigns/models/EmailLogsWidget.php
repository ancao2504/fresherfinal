<?php

/**
 * Name: EmailLogsWidget.php
 * Author: Phu Vo
 * Date: 2020.11.17
 */

class Campaigns_EmailLogsWidget_Model {

    public function getWidgetDataTableHeaders() {
        $widgetHeaders = [
            [
                'name' => 'send_date',
                'label' => vtranslate('LBL_EMAIL_LOGS_WIDGET_SEND_DATE', 'Campaigns'),
                'width' => '150px',
            ],
            [
                'name' => 'sender_email_address',
                'label' => vtranslate('LBL_EMAIL_LOGS_WIDGET_SENDER_EMAIL_ADDRESS', 'Campaigns'),
                'width' => '150px',
            ],
            [
                'name' => 'recepient_email_address',
                'label' => vtranslate('LBL_EMAIL_LOGS_WIDGET_RECEIPIENT_EMAIL_ADDRESS', 'Campaigns'),
                'width' => '150px',
            ],
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_EMAIL_LOGS_WIDGET_SUBJECT', 'Campaigns'),
                'width' => '260px',
            ],
            [
                'name' => 'status',
                'label' => vtranslate('LBL_EMAIL_LOGS_WIDGET_STATUS', 'Campaigns'),
            ],
        ];

        return $widgetHeaders;
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
            $extraWhere .= " AND vtiger_activity.subject LIKE ?";
            $extraQueryParams[] = '%' . $params['search']['value'] . '%';
        }

        // Paging
        if (!empty($params['length'])) {
            $pagingSql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $pagingSql .= " OFFSET {$params['start']}";
        }

        $sql = "SELECT
                vtiger_crmentity.createdtime AS send_date,
                vtiger_emaildetails.from_email AS sender_email_address,
                vtiger_emaildetails.to_email AS recepient_email_address,
                vtiger_activity.subject AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
	            vtiger_emaildetails.email_flag AS status
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_emaildetails ON (vtiger_emaildetails.emailid = vtiger_activity.activityid)
            INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_activity.activityid AND vtiger_crmentityrel.relmodule = 'Emails' AND vtiger_crmentityrel.module = 'Campaigns')
            WHERE vtiger_crmentityrel.crmid = ? $extraWhere ORDER BY vtiger_crmentity.createdtime DESC $pagingSql";

        $totalSql = "SELECT DISTINCT COUNT(vtiger_activity.activityid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_emaildetails ON (vtiger_emaildetails.emailid = vtiger_activity.activityid)
            INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_activity.activityid AND vtiger_crmentityrel.relmodule = 'Emails' AND vtiger_crmentityrel.module = 'Campaigns')
            WHERE vtiger_crmentityrel.crmid = ? $extraWhere";

        // Process params and query
        $queryParams = [$recordId];
        $queryParams = array_merge($queryParams, $extraQueryParams);

        $result = $adb->pquery($sql, $queryParams);
        $total = $adb->getOne($totalSql, $queryParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['recepient_email_address'] = json_decode($row['recepient_email_address'], true);
            $row['recepient_email_address'] = is_array($row['recepient_email_address']) ? join(', ', $row['recepient_email_address']) : $row['recepient_email_address'];

            // Process format data
            $dateUiType = new Vtiger_Datetime_UIType();
            $row['send_date'] = $dateUiType->getDisplayValue($row['send_date']);
            $row['sender_email_address'] = $this->resolveDataTitle($row['sender_email_address']);
            $row['recepient_email_address'] = $this->resolveDataTitle($row['recepient_email_address']);
            $row['record_name'] = $this->resolveDataTitle($row['record_name']);
            $row['status'] = $this->getEmailFlagDisplayValue($row['status']);
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

    public function resolveDataTitle($recordName) {
        return "<span title=\"{$recordName}\">{$recordName}</span>";
    }

    public function getEmailFlagDisplayValue($emailFlag) {
        $labelClass = 'label-warning';
        if ($emailFlag == 'SAVED') $labelClass = 'label-info';
        if ($emailFlag == 'SENT') $labelClass = 'label-success';
        $status = vtranslate($emailFlag);
        $htmlString = "<span class=\"label {$labelClass}\">{$status}</span>";

        return $htmlString;
    }
}