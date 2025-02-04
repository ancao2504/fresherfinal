<?php

/**
 * ActiveCampaignsWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_ActiveCampaignsWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('Campaigns', 'Campaigns'),
            ],
            [
                'name' => 'closingdate',
                'label' => vtranslate('Expected Close Date', 'Campaigns'),
            ],
            [
                'name' => 'targetaudience',
                'label' => vtranslate('Target Audience', 'Campaigns'),
            ],
            [
                'name' => 'lead_count',
                'label' => vtranslate('LBL_DASHBOARD_GENERATED_LEADS_COUNT'),
                'type' => 'number',
            ],
            [
                'name' => 'potential_count',
                'label' => vtranslate('LBL_DASHBOARD_CREATED_POTENTIALS_COUNT'),
                'type' => 'number',
            ],
            [
                'name' => 'budgetcost',
                'label' => vtranslate('Budget Cost', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'actualcost',
                'label' => vtranslate('Actual Cost', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'expectedrevenue',
                'label' => vtranslate('Expected Revenue', 'Campaigns'),
                'type' => 'number',
            ],
            [
                'name' => 'expectedroi',
                'label' => vtranslate('Expected ROI', 'Campaigns'),
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
                vtiger_campaign.targetaudience,
                COUNT(vtiger_leaddetails.leadid) AS lead_count,
                COUNT(vtiger_potential.potentialid) AS potential_count,
                vtiger_campaign.budgetcost,
                vtiger_campaign.actualcost,
                vtiger_campaign.expectedrevenue,
                vtiger_campaign.actual_revenue,
                MAX(IFNULL(modtracker.changedon, vtiger_crmentity.createdtime)) AS changeon
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            LEFT JOIN (
                vtiger_leaddetails
                INNER JOIN vtiger_crmentity AS lead_entity ON (lead_entity.crmid = vtiger_leaddetails.leadid AND lead_entity.setype = 'Leads' AND lead_entity.deleted = 0)
            ) ON (vtiger_leaddetails.related_campaign = vtiger_crmentity.crmid)
            LEFT JOIN (
                vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_entity ON (potential_entity.crmid = vtiger_potential.potentialid AND potential_entity.setype = 'Potentials' AND potential_entity.deleted = 0)
            ) ON (vtiger_potential.campaignid = vtiger_crmentity.crmid)
            LEFT JOIN (
                SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
                WHERE vtiger_modtracker_detail.fieldname = 'campaignstatus'
                ORDER BY vtiger_modtracker_basic.changedon DESC
            ) AS modtracker ON (modtracker.crmid = vtiger_crmentity.crmid)
            WHERE vtiger_campaign.campaignstatus = 'Active' {$aclQuery}
            GROUP BY vtiger_crmentity.crmid
            ORDER BY changeon DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_campaign.campaignstatus = 'Active' {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $closingDateTimeField = new DateTimeField($row['closingdate']);

            // Calc roi
            $row['expectedroi'] = $this->calcRoi($row['budgetcost'], $row['expectedrevenue']);
            $row['actualroi'] = $this->calcRoi($row['actualcost'], $row['actual_revenue']);

            // Format data
            $row['closingdate'] = $closingDateTimeField->getDisplayDate($current_user);
            $row['budgetcost'] = $this->formatNumberToUser($row['budgetcost']);
            $row['actualcost'] = $this->formatNumberToUser($row['actualcost']);
            $row['expectedrevenue'] = $this->formatNumberToUser($row['expectedrevenue']);
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
