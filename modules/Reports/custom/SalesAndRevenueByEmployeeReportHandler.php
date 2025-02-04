<?php

/*
    SalesAndRevenueByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.14
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesAndRevenueByEmployeeReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesAndRevenueByEmployeeReport/SalesAndRevenueByEmployeeReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesAndRevenueByEmployeeReport/SalesAndRevenueByEmployeeReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesAndRevenueByEmployeeReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'three_latest_years';
        }

        if (!isset($params['year'])) {
            $params['year'] = Date('Y');
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], true, false),
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return false;
    }

    public function getHeaderFromData($reportData) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $quarter = ['I', 'II', 'III', 'IV'];
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        if ($params['displayed_by']  == 'three_latest_years') {
            $displayedLabel = 'LBL_REPORT_YEAR';
        }
        else {
            $displayedLabel = 'LBL_REPORT_' . strtoupper($params['displayed_by']);
        }

        $headerRows = [
            [
                [
                    'label' => '',
                ]
            ]
        ];

        for ($i = 1; $i < count(current($reportData)) - 1; $i++) {
            if ($params['displayed_by'] == 'quarter') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $quarter[$i - 1];
            }

            if ($params['displayed_by'] == 'month') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $i;
            }

            if ($params['displayed_by'] == 'three_latest_years') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . (Date('Y') - 3 + $i);
            }

            $headerRows[0][] = [
                'label' => $label
            ];
        }

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports')
        ];

        return $headerRows;
    }

    protected function getChartData(array $params) {
        $quarter = ['I', 'II', 'III', 'IV'];
        $reportData = $this->getReportData($params, true);
        $data[] = ['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), vtranslate('LBL_REPORT_REVENUE', 'Reports')];

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'three_latest_years';
        }

        if (!isset($params['year'])) {
            $params['year'] = Date('Y');
        }

        if ($params['displayed_by']  == 'three_latest_years') {
            $displayedLabel = 'LBL_REPORT_YEAR';
        }
        else {
            $displayedLabel = 'LBL_REPORT_' . strtoupper($params['displayed_by']);
        }

        foreach ($reportData as $key => $column) {
            if ($key == 'total') break;

            if ($params['displayed_by'] == 'quarter') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $quarter[$key - 1];
            }
            else {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $key;
            }

            $data[] = [$label, (float)$column['sales'], (float)$column['revenue']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    public function getReportData($params){
        global $adb;

        if (!isset($params['employee']) || empty($params['employee'])) {
            return [];
        }

        $displayedBy = $params['displayed_by'];
        $params['period'] = ($displayedBy != 'three_latest_years' ? 'year' : 'three_latest_years');
        $groupTime = ($displayedBy == 'three_latest_years' ? 'YEAR' : ($displayedBy == 'quarter' ? 'QUARTER' : 'MONTH'));
        $interval = ($displayedBy == 'month' ? 1 : ($displayedBy == 'quarter' ? 3 : 12));
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], $interval);
        $sqlParams = [$params['employee']];
        $extWhere = '';
        $data = [];

        // Generate first data
        foreach($ranges as $timeIndex => $range) {
            $index  =$timeIndex + 1;

            if ($interval == 12) {
                $index = Date('Y', strtotime($range['from']));
            }

            $data[$index] = [
                'sales' => 0,
                'revenue' => 0
            ];
        }

        // For sum
        $data['total'] = [
            'sales' => 0,
            'revenue' => 0
        ];

        if (!empty($period['from_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        // Get sales
        $sql = "SELECT {$groupTime}(vtiger_crmentity.createdtime) AS group_time, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_users ON (vtiger_crmentity.main_owner_id = vtiger_users.id)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_crmentity.main_owner_id = ? {$extWhere}
            GROUP BY group_time
            ORDER BY group_time";

        $result = $adb->pquery($sql, $sqlParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['sales'] = $row['sales'];
            $data['total']['sales'] += $row['sales'];
        }

        $extWhere = str_replace('vtiger_crmentity.createdtime', 'vtiger_cpreceipt.paid_date', $extWhere);

        // Get revenue
        $sql = "SELECT salesorderid, cpreceiptid, SUM(amount_vnd) AS revenue, {$groupTime}(paid_date) AS group_time
            FROM (
                SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, paid_date
                FROM (
                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND salesorder_crmentity.main_owner_id = ? {$extWhere}

                    UNION ALL

                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND salesorder_crmentity.main_owner_id = ? {$extWhere}
                ) AS temp1
            ) AS temp2
            GROUP BY group_time
            ORDER BY group_time";

        $result = $adb->pquery($sql, array_merge($sqlParams, $sqlParams));

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['revenue'] = $row['revenue'];
            $data['total']['revenue'] += $row['revenue'];
        }

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        if ($params['displayed_by']  == 'three_latest_years') {
            $displayedLabel = 'LBL_REPORT_YEAR';
        }
        else {
            $displayedLabel = 'LBL_REPORT_' . strtoupper($params['displayed_by']);
        }

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('DISPLAYED_BY_LABEL', $displayedLabel);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SalesAndRevenueByEmployeeReport/SalesAndRevenueByEmployeeReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, false, true);

        $dataForExport = [
            [vtranslate('LBL_REPORT_SALES', 'Reports')],
            [vtranslate('LBL_REPORT_REVENUE', 'Reports')]
        ];

        foreach ($reportData as $data) {
            $dataForExport[0][] = [
                'value' => $data['sales'],
                'type' => 'currency'
            ];
            $dataForExport[1][] = [
                'value' => $data['revenue'],
                'type' => 'currency'
            ];
        }

        CustomReportUtils::writeReportToExcelFile($this, $dataForExport, $tempFileName, $advanceFilterSql);
    }
}
