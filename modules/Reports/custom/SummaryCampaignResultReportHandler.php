<?php

/*
    SummaryCampaignResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.28
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SummaryCampaignResultReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/SummaryCampaignResultReport/SummaryCampaignResultReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'all_campaigns' => Campaigns_Data_Model::getAllCampaigns(),
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
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') => '30%',
            vtranslate('LBL_REPORT_LEAD', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_POTENTIAL', 'Reports') => '7%',
            vtranslate('LBL_REPORT_QUOTE', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_SALES_ORDER', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '',
            vtranslate('LBL_REPORT_CAMPAIGN_COST', 'Reports') =>  '',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') . '/' . vtranslate('LBL_REPORT_COST', 'Reports') =>  '',
        ];
    }

    function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['campaigns'])) {
            return [];
        }

        $campaigns = $params['campaigns'];
        $campaignIds = implode("', '", $campaigns);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];

        // Get campaign data
        $sql = "SELECT campaignid, campaignname, actualcost FROM vtiger_campaign WHERE campaignid IN ('$campaignIds')";
        $result = $adb->pquery($sql, []);

        $totalCost = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $commonConditions = [[
                ['related_campaign', 'e', $row['campaignname']],
                ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']],
            ]];

            $potentialConditions = [[
                ['campaignid', 'e', $row['campaignname']],
                ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
            ]];

            $quoteConditions = [[
                ['related_campaign', 'e', $row['campaignname']],
                ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']],
                ['quotestage', 'n', 'Created'],
            ]];

            $data[$row['campaignid']] = [
                'campaign_id' => $row['campaignid'],
                'campaign_name' => $row['campaignname'],
                'lead_number' => 0,
                'lead_link' => getListViewLinkWithSearchParams('Leads', $commonConditions),
                'potential_number' => 0,
                'potential_link' => getListViewLinkWithSearchParams('Potentials', $potentialConditions),
                'quote_number' => 0,
                'quote_link' => getListViewLinkWithSearchParams('Quotes', $quoteConditions),
                'salesorder_number' => 0,
                'salesorder_link' => getListViewLinkWithSearchParams('SalesOrder', $commonConditions),
                'sales' => 0,
                'revenue' => 0,
                'cost' => $row['actualcost'],
                'revenue_per_cost' => 0
            ];

            $totalCost += $row['actualcost'];
        }
        
        $data['total'] = [
            'campaign_id' => '',
            'campaign_name' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
            'lead_number' => 0,
            'lead_link' => '',
            'potential_number' => 0,
            'potential_link' => '',
            'quote_number' => 0,
            'quote_link' => '',
            'salesorder_number' => 0,
            'salesorder_link' => '',
            'sales' => 0,
            'revenue' => 0,
            'cost' => $totalCost,
            'revenue_per_cost' => 0
        ];

        // Get potentials, quotes, sales orders, sales
		$sql = "SELECT vtiger_leaddetails.related_campaign AS campaignid, 'lead_number' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, 0 AS amount
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_leaddetails.related_campaign IN ('$campaignIds') AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_leaddetails.related_campaign

            UNION ALL

            SELECT vtiger_potential.campaignid, 'potential_number' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, 0 AS amount
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_potential.campaignid IN ('$campaignIds') AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_potential.campaignid

            UNION ALL

            SELECT vtiger_quotes.related_campaign AS campaignid, 'quote_number' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, 0 AS amount
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_quotes.quoteid AND vtiger_crmentity.deleted = 0)
            WHERE vtiger_quotes.related_campaign IN ('$campaignIds') AND vtiger_quotes.quotestage NOT IN ('Created') AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_quotes.related_campaign

            UNION ALL

            SELECT vtiger_salesorder.related_campaign AS campaignid, 'salesorder_number' AS data_type, COUNT(DISTINCT vtiger_crmentity.crmid) AS records_count, SUM(IF(sostatus NOT IN ('Created', 'Cancelled'), vtiger_salesorder.total, 0)) AS amount
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.deleted = 0)
            WHERE sostatus != 'Created' AND vtiger_salesorder.related_campaign IN ('$campaignIds') AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_salesorder.related_campaign";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']][$row['data_type']] = (int)$row['records_count'];
            $data['total'][$row['data_type']] += (int)$row['records_count'];

            if ($row['data_type'] == 'salesorder_number') {
                $data[$row['campaignid']]['sales'] = $row['amount'];
                $data['total']['sales'] += $row['amount'];
            }
        }

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
                    AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'

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
                    AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ) AS temp1
        ) AS temp2
        GROUP BY campaignid";
        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']]['revenue'] = $row['revenue'];
            $data['total']['revenue'] += $row['revenue'];
        }

        $data = array_values($data);

        if ($forExport) {
            for ($i = 0; $i < count($data); $i++) {
                unset($data[$i]['lead_link']);
                unset($data[$i]['potential_link']);
                unset($data[$i]['quote_link']);
                unset($data[$i]['salesorder_link']);

                $data[$i]['campaign_id'] = $i + 1;
                $data[$i]['revenue_per_cost'] = round($data[$i]['revenue'] / $data[$i]['cost'], 2);
                $data[$i]['sales'] = [
                    'value' => $data[$i]['sales'],
                    'type' => 'currency'
                ];

                $data[$i]['revenue'] = [
                    'value' => $data[$i]['revenue'],
                    'type' => 'currency'
                ];

                $data[$i]['cost'] = [
                    'value' => $data[$i]['cost'],
                    'type' => 'currency'
                ];
            }
        }

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

        $viewer->display('modules/Reports/tpls/SummaryCampaignResultReport/SummaryCampaignResultReport.tpl');
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