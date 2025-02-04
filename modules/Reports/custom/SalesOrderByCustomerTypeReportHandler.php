<?php

/*
    SalesOrderByCustomerTypeReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.14
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesOrderByCustomerTypeReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesOrderByCustomerTypeReport/SalesOrderByCustomerTypeReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesOrderByCustomerTypeReport/SalesOrderByCustomerTypeReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesOrderByCustomerTypeReportWidgetFilter.tpl';

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

    public function getReportHeaders() {
        return false;
    }

    public function getHeaderFromData($reportData) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        if (!isset($params['displayed_by']) || $params['displayed_by'] == 'month') {
            $groupLabel = vtranslate('LBL_REPORT_MONTH', 'Reports');
        }
        else {
            $groupLabel = vtranslate('LBL_REPORT_QUARTER', 'Reports');
        }

        $headerRows = [
            0 => [
                0 => [
                    'label' => vtranslate('LBL_REPORT_SOURCE', 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ]
            ],
            1 => [
                0 => [
                    'label' => ''
                ],
            ]
        ];

        $numberOfGroup = (count(current($reportData)) - 4) / 3;

        for ($i = 1; $i <= $numberOfGroup; $i++) {
            $headerRows[0][] = [
                'label' => "{$groupLabel} {$i}",
                'merge' => [
                    'row'=> 1,
                    'column' => 3
                ]
            ];

            $headerRows[1][] = ['label' => vtranslate('LBL_REPORT_NUMBER', 'Reports')];
            $headerRows[1][] = ['label' => vtranslate('LBL_REPORT_SALES', 'Reports')];
            $headerRows[1][] = ['label' => vtranslate('LBL_REPORT_REVENUE', 'Reports')];
        }

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports'),
            'merge' => [
                'row'=> 2,
                'column' => 1
            ]
        ];

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports'),
            'merge' => [
                'row'=> 2,
                'column' => 1
            ]
        ];

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL_REVENUE', 'Reports'),
            'merge' => [
                'row'=> 2,
                'column' => 1
            ]
        ];

        return $headerRows;
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);
        $data['saleorder_number'] = [['Element', vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports')]];
        $data['sales'] = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports')]];
        $data['revenue'] = [['Element', vtranslate('LBL_REPORT_REVENUE', 'Reports')]];

        foreach ($reportData['ext_data']['total_row'] as $key => $row) {
            $label = vtranslate($key, 'SalesOrder');
            $data['saleorder_number'][] = [$label, (float)$row['saleorder_number']];
            $data['sales'][] = [$label, (float)$row['sales']];
            $data['revenue'][] = [$label, (float)$row['revenue']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        $displayedBy = $params['displayed_by'];
        $interval = ($displayedBy == 'month' ? 1 : 3);
        $data = [];
        $params['period'] = 'year';
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], $interval);
        $displayedBySelect = ($displayedBy == 'month' ? 'MONTH(vtiger_crmentity.createdtime)' : 'QUARTER(vtiger_crmentity.createdtime)');

        // Get all customer type from sales order
        $sql = "SELECT salesorder_customer_type FROM vtiger_salesorder_customer_type ORDER by sortorderid";
        $customerType = [];
        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $customerType[] = $row['salesorder_customer_type'];
        }

        $data['ext_data']['customer_type']= $customerType;

        // Generate the first values
        foreach($ranges as $timeIndex => $range) {
            foreach ($customerType as $type) {
                $fromDateForFilter = $range['from'];
                $toDateForFilter = $range['to'];

                $fromDateForFilter = new DateTimeField($fromDateForFilter);
                $fromDateForFilter = $fromDateForFilter->getDisplayDate();
                $toDateForFilter = new DateTimeField($toDateForFilter);
                $toDateForFilter = $toDateForFilter->getDisplayDate();

                $conditions = [[
                    ['salesorder_customer_type', 'c', $type],
                    ['sostatus', 'n', 'Cancelled,Created'],
                    ['createdtime', 'bw', "{$fromDateForFilter},{$toDateForFilter}"]
                ]];

                $data[$timeIndex + 1][$type] = [
                    'saleorder_number' => 0,
                    'saleorder_link' => getListViewLinkWithSearchParams('SalesOrder', $conditions),
                    'sales' => 0,
                    'revenue' => 0,
                ];

                $data['ext_data']['total_column'][$timeIndex + 1] = [
                    'saleorder_number' => 0,
                    'saleorder_link' => '',
                    'sales' => 0,
                    'revenue' => 0,
                ];
            }
        }

        foreach ($customerType as $type) {
            $data['ext_data']['total_row'][$type] = [
                'saleorder_number' => 0,
                'saleorder_link' => '',
                'sales' => 0,
                'revenue' => 0,
            ];
        }

        $data['ext_data']['total'] = [
            'saleorder_number' => 0,
            'saleorder_link' => '',
            'sales' => 0,
            'revenue' => 0,
        ];

        // Get data
        $sql = "SELECT vtiger_salesorder.salesorder_customer_type, COUNT(salesorderid) AS saleorder_number, SUM(total) AS sales, {$displayedBySelect} AS group_time
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_salesorder.salesorder_customer_type IS NOT NULL AND vtiger_salesorder.salesorder_customer_type != ''";

        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere} GROUP BY group_time, vtiger_salesorder.salesorder_customer_type
            ORDER BY group_time";
        $result = $adb->pquery($sql, $sqlParams);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $tempType = $row['salesorder_customer_type'];
            $groupTime = $row['group_time'];
            $sales = (float)$row['sales'];
            $saleOrderNumber = (int)$row['saleorder_number'];

            // Generate link for report for sales order
            $data[$row['group_time']][$tempType]['sales'] = $sales;
            $data[$row['group_time']][$tempType]['saleorder_number'] = $saleOrderNumber;

            $data['ext_data']['total_row'][$tempType]['sales'] += $sales;
            $data['ext_data']['total_row'][$tempType]['saleorder_number'] += $saleOrderNumber;

            $data['ext_data']['total_column'][$groupTime]['sales'] += $sales;
            $data['ext_data']['total_column'][$groupTime]['saleorder_number'] += $saleOrderNumber;

            $data['ext_data']['total']['sales'] += $sales;
            $data['ext_data']['total']['saleorder_number'] += $saleOrderNumber;
        }

        $extWhere = str_replace('vtiger_crmentity.createdtime', 'vtiger_cpreceipt.paid_date', $extWhere);
        $displayedBySelect = str_replace('vtiger_crmentity.createdtime', 'paid_date', $displayedBySelect);

        // Get revenue data
        $sql = "SELECT salesorderid, cpreceiptid, SUM(amount_vnd) AS revenue, salesorder_customer_type, {$displayedBySelect} AS group_time
            FROM (
                SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, paid_date, salesorder_customer_type
                FROM (
                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date, vtiger_salesorder.salesorder_customer_type
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND cpreceipt_category = 'sales' AND vtiger_salesorder.salesorder_customer_type IS NOT NULL AND vtiger_salesorder.salesorder_customer_type != '' AND vtiger_cpreceipt.cpreceipt_status = 'completed' {$extWhere}

                    UNION ALL

                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date, vtiger_salesorder.salesorder_customer_type
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND cpreceipt_category = 'sales' AND vtiger_salesorder.salesorder_customer_type IS NOT NULL AND vtiger_salesorder.salesorder_customer_type != '' AND vtiger_cpreceipt.cpreceipt_status = 'completed'  {$extWhere}
                ) AS temp1
            ) AS temp2
            GROUP BY salesorder_customer_type
            ORDER BY group_time";

        $result = $adb->pquery($sql, array_merge($sqlParams, $sqlParams));

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $tempType = $row['salesorder_customer_type'];
            $groupTime = $row['group_time'];
            $revenue = (float)$row['revenue'];

            $data[$row['group_time']][$tempType]['revenue'] = $revenue;

            $data['ext_data']['total_row'][$tempType]['revenue'] += $revenue;
            $data['ext_data']['total_column'][$groupTime]['revenue'] += $revenue;
            $data['ext_data']['total']['revenue'] += $revenue;
        }

        return $data;
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
        $viewer->assign('DISPLAYED_BY_LABEL', 'LBL_REPORT_' . strtoupper($params['displayed_by']));
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->display('modules/Reports/tpls/SalesOrderByCustomerTypeReport/SalesOrderByCustomerTypeReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, false, true);

        $dataForExport = [];

        foreach ($reportData['ext_data']['customer_type'] as $type) {
            $dataForExport[$type] = [
                vtranslate($type, 'SalesOrder'),
            ];
        }

        $dataForExport['total'] = [
            vtranslate('LBL_REPORT_TOTAL', 'Reports'),
        ];

        foreach ($reportData as $key => $groupData) {
            if ($key != 'ext_data') {
                foreach ($groupData as $type => $data) {
                    $dataForExport[$type][] = [
                        'value' => $data['saleorder_number'],
                        'type' => 'integer'
                    ];
                    $dataForExport[$type][] = [
                        'value' => $data['sales'],
                        'type' => 'currency'
                    ];
                    $dataForExport[$type][] = [
                        'value' => $data['revenue'],
                        'type' => 'currency'
                    ];
                }
            }
        }

        foreach ($reportData['ext_data']['total_row'] as $type => $data) {
            $dataForExport[$type][] = [
                'value' => $data['saleorder_number'],
                'type' => 'integer'
            ];
            $dataForExport[$type][] = [
                'value' => $data['sales'],
                'type' => 'currency'
            ];
            $dataForExport[$type][] = [
                'value' => $data['revenue'],
                'type' => 'currency'
            ];
        }

        foreach ($reportData['ext_data']['total_column'] as $data) {
            $dataForExport['total'][] = [
                'value' => $data['saleorder_number'],
                'type' => 'integer'
            ];
            $dataForExport['total'][] = [
                'value' => $data['sales'],
                'type' => 'currency'
            ];
            $dataForExport['total'][] = [
                'value' => $data['revenue'],
                'type' => 'currency'
            ];
        }

        $dataForExport['total'][] = [
            'value' => $reportData['ext_data']['total']['saleorder_number'],
            'type' => 'integer'
        ];
        $dataForExport['total'][] = [
            'value' => $reportData['ext_data']['total']['sales'],
            'type' => 'currency'
        ];
        $dataForExport['total'][] =[
            'value' => $reportData['ext_data']['total']['revenue'],
            'type' => 'currency'
        ];

        CustomReportUtils::writeReportToExcelFile($this, $dataForExport, $tempFileName, $advanceFilterSql);
    }
}