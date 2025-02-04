<?php

/*
    SummarySalesByMarketReportHander.php
    Author: Phuc Lu
    Date: 2020.06.03
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SummarySalesByMarketReportHander extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SummarySalesByMarketReport/SummarySalesByMarketReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SummarySalesByMarketReport/SummarySalesByMarketReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SummarySalesByMarketReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/SummarySalesByMarketReportDetail.js';
    protected $targetReport = 'SUMMARY_SALES_BY_MARKET';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'month';
        }

        if (!isset($params['year'])) {
            $params['year'] = Date('Y');
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
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

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '32%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '32%',
            vtranslate('LBL_REPORT_RATIO_BY_MARKET', 'Reports') => '32%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports')]];

        foreach ($reportData as $key => $row) {
            $data[] = [$row['bill_city'], (float)$row['sales']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $data = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $no = 0;
        $totalSales = 0;
        $limitDisplayed = 0;
        $otherTotal = 0;

         // Get data
        $sql = "SELECT SUM(vtiger_salesorder.total)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_account.accountid = vtiger_salesorder.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_account.accountid = accountaddressid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'";

        $totalSales = (float)$adb->getOne($sql);
        $limitDisplayed = $totalSales * 3 / 100;

        // Get data
        $sql = "SELECT 0 AS no, IF(vtiger_salesorder.accountid = '{$personalAccountId}', mailingcity, bill_city) AS bill_city, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_account.accountid = vtiger_salesorder.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_account.accountid = accountaddressid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
            ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY bill_city
            ORDER BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $sales = (float)$row['sales'];

            if ($limitDisplayed > $sales || empty($row['bill_city'])) {
                $otherTotal += $sales;
            }
            else {
                $row['no'] = ++$no;
                $row['ratio'] = $sales / $totalSales * 100;

                if ($forExport) {
                    $row['sales'] = [
                        'value' => $row['sales'],
                        'type' => 'currency'
                    ];

                    $row['ratio'] = CurrencyField::convertToUserFormat($row['ratio']) . '%';
                }

                $data[] = $row;
            }
        }

        if ($otherTotal > 0) {
            $data[] = [
                'no' => ++$no,
                'bill_city' => vtranslate('LBL_REPORT_OTHER', 'Reports'),
                'sales' => $otherTotal,
                'ratio' =>  ($forExport ? CurrencyField::convertToUserFormat($otherTotal / $totalSales * 100) . '%' : ($otherTotal / $totalSales * 100))
            ];
        }

        return array_values($data);
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
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('TARGET_REPORT', $this->targetReport);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SummarySalesByMarketReport/SummarySalesByMarketReport.tpl');
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