<?php

/**
 * CancelledCampaignWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_CancelledCampaignWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('Campaign Name', 'Campaigns'),
            ],
            [
                'name' => 'closingdate',
                'label' => vtranslate('Expected Close Date', 'Campaigns'),
            ],
            [
                'name' => 'actualcost',
                'label' => vtranslate('Actual Cost', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'actual_revenue',
                'label' => vtranslate('LBL_ACTUAL_REVENUE', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'actualroi',
                'label' => vtranslate('Actual ROI', 'Campaigns'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];
        $aclQuery = CRMEntity::getListViewSecurityParameter('Campaigns');

        $sql = "SELECT
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_campaign.closingdate,
                vtiger_campaign.actualcost,
                vtiger_campaign.actual_revenue
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_campaign.campaignstatus = 'Cancelled' {$aclQuery}
            ORDER BY vtiger_crmentity.modifiedtime DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_campaign.campaignstatus = 'Cancelled' {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $startDateTimeField = new DateTimeField($row['closingdate']);
            $row['actualroi'] = $this->calcRoi($row['actualcost'], $row['actual_revenue']);

            // Format data
            $row['closingdate'] = $startDateTimeField->getDisplayDate($current_user);
            $row['actualcost'] = $this->formatNumberToUser($row['actualcost']);
            $row['actual_revenue'] = $this->formatNumberToUser($row['actual_revenue']);

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
}