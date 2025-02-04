<?php

/**
 * Name: CompareCustomerConversionRateByCampaignReportHandler.php
 * Author: Phu Vo
 * Date: 2020.10.12
 */

require_once('modules/Reports/custom/CustomerConversionRateByEmployeeReportHandler.php');

class CompareCustomerConversionRateByCampaignReportHandler extends CustomerConversionRateByEmployeeReportHandler {
    
    protected $chartTemplate = 'modules/Reports/tpls/CompareCustomerConversionRateByCampaignReport/CompareCustomerConversionRateByCampaignReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopEmployeesByPotentialSalesReport/TopEmployeesByPotentialSalesReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopEmployeesByPotentialSalesReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopEmployeesByPotentialSalesReportDetail.js';
    protected $reportObject = 'COMPARE_BY_CAMPAIGN';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('Chiến dịch', 'Reports') => '30%',
            vtranslate('Tỷ lệ Cơ hội / Đầu mối', 'Reports') =>  '7%',
            vtranslate('Tỷ lệ Thành công / Cơ hội', 'Reports') =>  '7%',
        ];
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);   

        $viewer->display('modules/Reports/tpls/CompareCustomerConversionRateByCampaignReport/CompareCustomerConversionRateByCampaignReport.tpl');
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('Tỷ lệ Cơ hội / Đầu mối', 'Reports'), vtranslate('Tỷ lệ Thành công / Cơ hội', 'Reports')]];
        $links = [];
        $data = [];
        $data['cols'] = [
            ['label' => 'Element', 'type' => 'string'],
            ['label' => vtranslate('Tỷ lệ Cơ hội / Đầu mối', 'Reports'), 'type' => 'number'],
            ['label' => vtranslate('Tỷ lệ Thành công / Cơ hội', 'Reports'), 'type' => 'number'],
        ];


        $data['rows'] = [];
        foreach ($reportData as $row) {
            if ($row['id'] == 'all') {
                break;
            }

            $data['rows'][] = [
                'c' => [
                    ['v' => html_entity_decode($row['campaignname'])],
                    ['v' => (int)(-$row['potential_lead_ratio']), 'f' => (int)($row['potential_lead_ratio'])],
                    ['v' => (int)($row['won_potential_ratio'])],
                ]
            ];

            $links[] = '';
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $campaigns = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for campaigns
        $sql = "SELECT campaignid, campaignname
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (crmid = campaignid AND deleted = 0)
            WHERE createdtime BETWEEN  '{$period['from_date']}' AND '{$period['to_date']}'";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['all_potentials'] = 0;
            $row['won_potentials'] = 0;
            $row['all_leads'] = 0;
            $row['potential_lead_ratio'] = 0;
            $row['won_potential_ratio'] = 0;
            $campaigns[$row['campaignid']] = $row;
        }

        $campaignIds = join("', '", array_keys($campaigns));

        // Data for all potentials
        $sql = "SELECT campaignid, COUNT(potentialid) AS number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE isconvertedfromlead = 1 AND campaignid IN ('{$campaignIds}')
            GROUP BY campaignid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaigns[$row['campaignid']]['all_potentials'] = $row['number'];
        }

        // Data for won potentials
        $sql = "SELECT campaignid, COUNT(potentialid) AS number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE isconvertedfromlead = 1 AND sales_stage = 'Closed Won' AND campaignid IN ('{$campaignIds}')
            GROUP BY campaignid";

        $result = $adb->pquery($sql);
            
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaigns[$row['campaignid']]['won_potentials'] = $row['number'];
            $campaigns[$row['campaignid']]['won_potential_ratio'] = round(($row['number'] / $campaigns[$row['campaignid']]['all_potentials']) * 100);
        }

        // Data for all leads
        $sql = "SELECT related_campaign AS campaignid, COUNT(leadid) AS number
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE related_campaign IN ('{$campaignIds}')
            GROUP BY related_campaign";
    
        $result = $adb->pquery($sql);
    
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaigns[$row['campaignid']]['all_leads'] = $row['number'];
            $campaigns[$row['campaignid']]['potential_lead_ratio'] = round(($campaigns[$row['campaignid']]['all_potentials'] / $row['number']) * 100);
        }

        return array_values($campaigns);
    }
}