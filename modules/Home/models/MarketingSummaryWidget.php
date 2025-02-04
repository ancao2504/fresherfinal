<?php

/**
 * Name: MarketingSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_MarketingSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'budgetcost',
                'label' => vtranslate('LBL_DASHBOARD_BUDGET_COST', 'Home'),
            ],
            [
                'name' => 'actualcost',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_COST', 'Home'),
            ],
            [
                'name' => 'expectedrevenue',
                'label' => vtranslate('LBL_DASHBOARD_EXPECTED_REVENUE', 'Home'),
            ],
            [
                'name' => 'actual_revenue',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_REVENUE', 'Home'),
            ],
            [
                'name' => 'expectedroi',
                'label' => vtranslate('LBL_DASHBOARD_EXPECTED_ROI', 'Home'),
            ],
            [
                'name' => 'actualroi',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_ROI', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $data['budgetcost'] = [];
        $data['actualcost'] = [];
        $data['expectedrevenue'] = [];
        $data['actual_revenue'] = [];
        $data['expectedroi'] = [];
        $data['actualroi'] = [];

        $sql = "SELECT
                SUM(vtiger_campaign.budgetcost) AS budgetcost,
                SUM(vtiger_campaign.actualcost) AS actualcost,
                SUM(vtiger_campaign.expectedrevenue) AS expectedrevenue,
                SUM(vtiger_campaign.actual_revenue) AS actual_revenue
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_campaign.campaignid AND vtiger_crmentity.setype = 'Campaigns' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_campaign.campaignstatus <> 'Planning'";
        
        $result = $adb->pquery($sql);
        $result = $adb->fetchByAssoc($result);

        $data['budgetcost']['value'] = $this->formatNumberToUser($result['budgetcost']);
        $data['actualcost']['value'] = $this->formatNumberToUser($result['actualcost']);
        $data['expectedrevenue']['value'] = $this->formatNumberToUser($result['expectedrevenue']);
        $data['actual_revenue']['value'] = $this->formatNumberToUser($result['actual_revenue']);
        $data['expectedroi']['value'] = $this->calcRoi($result['budgetcost'], $result['expectedrevenue']) . '%';
        $data['actualroi']['value'] = $this->calcRoi($result['actualcost'], $result['actual_revenue']) . '%';

        return $data;
    }
}