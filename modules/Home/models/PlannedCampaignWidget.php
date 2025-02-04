<?php

/**
 * PlannedCampaignWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_PlannedCampaignWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'name' => 'start_date',
                'label' => vtranslate('LBL_START_DATE', 'Campaigns'),
            ],
            [
                'name' => 'targetaudience',
                'label' => vtranslate('Target Audience', 'Campaigns'),
            ],
            [
                'name' => 'budgetcost',
                'label' => vtranslate('Budget Cost', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'expectedrevenue',
                'label' => vtranslate('Expected Revenue', 'Campaigns'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Campaigns');

        $sql = "SELECT
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_campaign.start_date,
                vtiger_campaign.targetaudience,
                vtiger_campaign.budgetcost,
                vtiger_campaign.expectedrevenue,
                MAX(IFNULL(modtracker.changedon, vtiger_crmentity.createdtime)) AS changeon
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN (
                SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
                WHERE vtiger_modtracker_detail.fieldname = 'campaignstatus'
                ORDER BY vtiger_modtracker_basic.changedon DESC
            ) AS modtracker ON (modtracker.crmid = vtiger_crmentity.crmid)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_campaign.campaignstatus = 'Planning' 
                {$aclQuery}
            GROUP BY vtiger_crmentity.crmid
            ORDER BY changeon DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_campaign.campaignstatus = 'Planning' 
                {$aclQuery}
            ORDER BY vtiger_crmentity.modifiedtime DESC";
                
        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $startDateTimeField = new DateTimeField($row['start_date']);
            $row['start_date'] = $startDateTimeField->getDisplayDate($current_user);
            $row['budgetcost'] = $this->formatNumberToUser($row['budgetcost']);
            $row['expectedrevenue'] = $this->formatNumberToUser($row['expectedrevenue']);
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