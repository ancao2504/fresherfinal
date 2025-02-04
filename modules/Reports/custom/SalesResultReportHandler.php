<?php

/*
    SalesResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.26
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesResultReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesResultReport/SalesResultReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesResultReport/SalesResultReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesResultReportWidgetFilter.tpl';

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

    function getReportHeaders($params) {
        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'three_latest_years';
        }

        if ($params['displayed_by'] == 'three_latest_years') {
            $reportPeriod = 'YEAR';
        }

        if ($params['displayed_by'] == 'quarter') {
            $reportPeriod = 'QUARTER';
        }

        if ($params['displayed_by'] == 'month') {
            $reportPeriod = 'MONTH';
        }

        return [
            vtranslate('LBL_REPORT_' . $reportPeriod, 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '18%',
            vtranslate('LBL_REPORT_QUOTE_NUMBER', 'Reports') =>  '18%',
            vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports') =>  '18%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '18%',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '18%',
        ];
    }

    protected function getChartData(array $params) {
        $quarter = ['I', 'II', 'III', 'IV'];
        $reportData = $this->getReportData($params);
        $data[] = ['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), vtranslate('LBL_REPORT_REVENUE', 'Reports'), vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports')];

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
            if ($key == count($reportData) || $key == 'all') break;

            if ($params['displayed_by'] == 'quarter') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $quarter[$key - 1];
            }
            else {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $key;
            }

            $data[] = [$label, (float)$column['sales'], (float)$column['revenue'], (int)$column['sales_order_number']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        $quarter = ['I', 'II', 'III', 'IV'];
        $displayedBy = $params['displayed_by'];
        $params['period'] = ($displayedBy != 'three_latest_years' ? 'year' : 'three_latest_years');
        $groupTime = ($displayedBy == 'three_latest_years' ? 'YEAR' : ($displayedBy == 'quarter' ? 'QUARTER' : 'MONTH'));
        $interval = ($displayedBy == 'month' ? 1 : ($displayedBy == 'quarter' ? 3 : 12));
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], $interval);
        $data = [];

        // Generate first data
        foreach($ranges as $timeIndex => $range) {
            if ($interval == 1) {
                $index = $timeIndex + 1;
                $timeIndex = $index;
            }

            if ($interval == 12) {
                $index = Date('Y', strtotime($range['from']));
                $timeIndex = $index;
            }

            if ($interval == 3) {
                $index = $quarter[$timeIndex];
                $timeIndex += 1;
            }

            $data[$timeIndex] = [
                'period' => $index,
                'potential_number' => 0,
                'quote_number' => 0,
                'sales_order_number' => 0,
                'sales' => 0,
                'revenue' => 0
            ];

            if (!$forExport) {
                $commonConditions = [[
                    ['createdtime', 'bw', $range['from'] . ',' . $range['to']]
                ]];

                $data[$timeIndex] = array_merge($data[$timeIndex], [
                    'potential_number_link' => getListViewLinkWithSearchParams('Potentials', $commonConditions),
                    'quote_number_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'n', 'Created']])]),
                    'sales_order_number_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'n', 'Created,Cancelled']])]),
                ]);
            }
        }

        // For all
        $data['all'] = [
            'period' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
            'potential_number' => 0,
            'quote_number' => 0,
            'sales_order_number' => 0,
            'sales' => 0,
            'revenue' => 0
        ];

        // Count potential
        $sql = "SELECT {$groupTime}(vtiger_crmentity.createdtime) AS group_time, COUNT(potentialid) AS potential_number
        FROM vtiger_potential
        INNER JOIN vtiger_crmentity ON (potentialid = crmid AND deleted = 0)
        WHERE vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
        GROUP BY group_time";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['potential_number'] = (int)$row['potential_number'];
            $data['all']['potential_number'] += (int)$row['potential_number'];
        }

        // Count quote
        $sql = "SELECT {$groupTime}(vtiger_crmentity.createdtime) AS group_time, COUNT(quoteid) AS quote_number
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity  ON (quoteid = crmid AND deleted = 0)
            WHERE vtiger_quotes.quotestage != 'Created' AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY group_time";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['quote_number'] = (int)$row['quote_number'];
            $data['all']['quote_number'] += (int)$row['quote_number'];
        }

        // Get sales
        $sql = "SELECT {$groupTime}(vtiger_crmentity.createdtime) AS group_time, COUNT(vtiger_salesorder.salesorderid) AS sales_order_number, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_users ON (vtiger_crmentity.main_owner_id = vtiger_users.id)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY group_time";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['sales_order_number'] = (int)$row['sales_order_number'];
            $data['all']['sales_order_number'] += (int)$row['sales_order_number'];

            $data[$row['group_time']]['sales'] = (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Get revenue
        $sql = "SELECT salesorderid, cpreceiptid, SUM(amount_vnd) AS revenue, {$groupTime}(paid_date) AS group_time
            FROM (
                SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, paid_date
                FROM (
                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid AND vtiger_cpreceipt.cpreceipt_category = 'sales')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed'
                        AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')

                    UNION ALL

                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt' AND vtiger_cpreceipt.cpreceipt_category = 'sales')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed'
                        AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY group_time";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['group_time']]['revenue'] = (float)$row['revenue'];
            $data['all']['revenue'] += (float)$row['revenue'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];

                $data[$key]['revenue'] = [
                    'value' => $value['revenue'],
                    'type' => 'currency'
                ];
            }
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
        $reportData = array_values($reportData);
        $reportHeaders = $this->getReportHeaders($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('DISPLAYED_BY_LABEL', $displayedLabel);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SalesResultReport/SalesResultReport.tpl');
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
