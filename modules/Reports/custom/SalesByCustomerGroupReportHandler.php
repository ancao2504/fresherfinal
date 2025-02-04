<?php

/*
    SalesByCustomerGroupReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.04
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesByCustomerGroupReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesByCustomerGroupReport/SalesByCustomerGroupReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesByCustomerGroupReport/SalesByCustomerGroupReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesByCustomerGroupReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function getReportHeaders() {

        $headers = [
            vtranslate('LBL_REPORT_NO', 'Reports') => '20px',
            vtranslate('LBL_REPORT_CUSTOMER_GROUP', 'Reports') => '38%',
            vtranslate('LBL_REPORT_FROM', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_TO', 'Reports') => '15%',
            vtranslate('LBL_REPORT_CUSTOMER_NUMBER', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '15%',
        ];

        return $headers;
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports'),  vtranslate('LBL_REPORT_CUSTOMER_NUMBER', 'Reports')]];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['group_name']), (float)$row['sales'], (float)$row['customer_number']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;
        $customerGroups = Reports_CustomReport_Helper::getCustomerGroups(false, true);

        if ($customerGroups == false || !count($customerGroups))
            return [];

        $params['period'] = 'year';
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;
        $extQuery = 'CASE ';

        foreach ($customerGroups as $customerGroup) {
            $fromValue = (float)$customerGroup['from_value'];
            $toValue = (float)$customerGroup['to_value'];

            $data[$customerGroup['group_id']] = [
                'no' => ++$no,
                'group_name' => $customerGroup['group_name'],
                'from_value' => (!$forExport ? $fromValue : ['value' => $fromValue, 'type' => 'currency']),
                'to_value' => (!$forExport ? $toValue : ['value' => $toValue, 'type' => 'currency']),
                'customer_number' => 0,
                'sales' => 0
            ];

            if ($toValue == 0) {
                $extQuery .= " ELSE {$customerGroup['group_id']} END AS group_id";
                break;
            }
            else {
                $extQuery .= "WHEN SUM(vtiger_salesorder.total) <= {$toValue} THEN {$customerGroup['group_id']} ";
            }
        }

        $sql = "SELECT group_id, SUM(account_sales) AS sales, COUNT(accountid) AS customer_number
            FROM (
                SELECT vtiger_account.accountid, SUM(vtiger_salesorder.total) AS account_sales, $extQuery
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY vtiger_account.accountid
            ) AS temp
            GROUP BY group_id
            ORDER BY sales";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $sales = (float)$row['sales'];

            $data[$row['group_id']]['customer_number'] = (int)$row['customer_number'];
            $data[$row['group_id']]['sales'] = (!$forExport ? $sales : ['value' => $sales, 'type' => 'currency']);
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportHeaders = $this->getReportHeaders($params);
        $reportData = $this->getReportData($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SalesByCustomerGroupReport/SalesByCustomerGroupReport.tpl');
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