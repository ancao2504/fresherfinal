<?php

/*
    CampaignEffectReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class CampaignEffectReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/CampaignEffectReport/CampaignEffectReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        // Define field for validation
        $this->reportFilterMeta = [
            'all_campaigns' => Campaigns_Data_Model::getAllCampaigns(true, $period['from_date'], $period['to_date']),
            'input_validators' => [
                'from_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'from_date',
                    'label' => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                'to_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'to_date',
                    'label' => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') => '30%',
            vtranslate('LBL_REPORT_EXCEPTED_SALES_ORDER', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_ACTUAL_SALES_ORDER', 'Reports') => '8%',
            vtranslate('LBL_REPORT_EXPECTED_RESPONSE', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_ACTUAL_RESPONSE', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_BUDGET', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_ACTUAL_COST', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_EXPECTED_REVENUE', 'Reports') =>  '8%',
            vtranslate('LBL_REPORT_ACTUAL_REVENUE', 'Reports') =>  '8%',
        ];
    }

    function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['campaigns'])) {
            return [];
        }

        $campaigns = $params['campaigns'];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $allCampaigns = Campaigns_Data_Model::getAllCampaigns(false, $period['from_date'], $period['to_date']);
        $data = [];
        $no = 0;

        // If can not find any campaign
        if (!count($allCampaigns)) {
            return [];
        }

        // Get campaign data
        if ($campaigns == '0' || in_array('0', $campaigns)) {
            $campaigns = array_keys($allCampaigns);
        }

        $campaignIds = implode("','", $campaigns);
        $sql = "SELECT * FROM vtiger_campaign WHERE campaignid IN ('$campaignIds')";
        $result = $adb->pquery($sql, []);

        // Set for all data
        $allData = [
            'campaign_id' => (!$forExport ? 'all' : ''),
            'campaign_name' =>  vtranslate('LBL_REPORT_TOTAL', 'Reports'),
            'expected_sales_order' => 0,
            'actual_sales_order' => 0,
            'expected_response' => 0,
            'actual_response' => 0,
            'budget' => 0,
            'cost' => 0,
            'expected_revenue' => 0,
            'actual_revenue' => 0,
        ];

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']] = [
                'campaign_id' =>  (!$forExport ? $row['campaignid'] : ++$no),
                'campaign_name' => $row['campaignname'],
                'expected_sales_order' => $row['expectedsalescount'],
                'actual_sales_order' => $row['actualsalescount'],
                'expected_response' => $row['expectedresponsecount'],
                'actual_response' => $row['actualresponsecount'],
                'budget' => $row['budgetcost'],
                'cost' => $row['actualcost'],
                'expected_revenue' => $row['expectedrevenue'],
                'actual_revenue' => 0,
            ];

            $allData['expected_sales_order'] += (int)$row['expectedsalescount'];
            $allData['actual_sales_order'] += (int)$row['actualsalescount'];
            $allData['expected_response'] += (int)$row['expectedresponsecount'];
            $allData['actual_response'] += (int)$row['actualresponsecount'];
            $allData['budget'] += (int)$row['budgetcost'];
            $allData['cost'] += (int)$row['actualcost'];
            $allData['expected_revenue'] += (int)$row['expectedrevenue'];
        }

        $data['all'] = $allData;

        // Get revenue
        $sql = "SELECT campaignid, SUM(amount_vnd) AS revenue
        FROM (
            SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, campaignid
            FROM (
                SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_salesorder.related_campaign AS campaignid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.related_campaign IN ('$campaignIds')

                UNION ALL

                SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_salesorder.related_campaign AS campaignid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.related_campaign IN ('$campaignIds')
            ) AS temp1
        ) AS temp2
        GROUP BY campaignid";
        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']]['actual_revenue'] = $row['revenue'];
            $data['all']['actual_revenue'] += $row['revenue'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['budget'] = [
                    'value' => $data[$key]['budget'],
                    'type' => 'currency'
                ];

                $data[$key]['cost'] = [
                    'value' => $data[$key]['cost'],
                    'type' => 'currency'
                ];

                $data[$key]['expected_revenue'] = [
                    'value' => $data[$key]['expected_revenue'],
                    'type' => 'currency'
                ];

                $data[$key]['actual_revenue'] = [
                    'value' => $data[$key]['actual_revenue'],
                    'type' => 'currency'
                ];
            }
        }

        $data = array_values($data);

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportHeaders = $this->getReportHeaders();
        $reportData = $this->getReportData($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/CampaignEffectReport/CampaignEffectReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}