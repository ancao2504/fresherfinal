<?php

/**
 * CompletedCampaignWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_CompletedCampaignWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

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
                vtiger_campaign.actual_revenue,
                MAX(IFNULL(modtracker.changedon, vtiger_crmentity.createdtime)) AS changeon
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN (
                SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
                WHERE vtiger_modtracker_detail.fieldname = 'campaignstatus'
                ORDER BY vtiger_modtracker_basic.changedon DESC
            ) AS modtracker ON (modtracker.crmid = vtiger_crmentity.crmid)
            WHERE vtiger_campaign.campaignstatus = 'Completed' {$aclQuery}
            GROUP BY vtiger_crmentity.crmid
            ORDER BY changeon DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_campaign.campaignstatus = 'Completed' {$aclQuery}";

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