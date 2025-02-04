<?php

/*
    TopEmployeesBySalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.20
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class TopEmployeesBySalesReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/TopEmployeesBySalesReport/TopEmployeesBySalesReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopEmployeesBySalesReport/TopEmployeesBySalesReportFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopEmployeesBySalesReportDetail.js';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopEmployeesBySalesReportWidgetFilter.tpl';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
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
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), ['role' => "style"]]];
        $links = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['user_full_name']), (float)$row['db_amount'], "#7cb5ec"];

            $conditions = [[
                ['main_owner_id', 'c', $row['id']],
                ['sostatus', 'n', 'Cancelled,Created'],
                ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
            ]];
            $links[] = base64_encode(getListViewLinkWithSearchParams('SalesOrder', $conditions));
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb, $fullNameConfig;

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for sale order
        $sql = "SELECT 0 AS no, vtiger_users.id, {$fullNameField} AS user_full_name,
                COUNT(vtiger_salesorder.salesorderid) AS number, 0 AS amount, SUM(vtiger_salesorder.total) AS db_amount
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_users ON (vtiger_crmentity.main_owner_id = vtiger_users.id)
            WHERE sostatus NOT IN ('Created', 'Cancelled')";

        // Update params for where
        $extWhere = '';
        $sqlParams = [];

        // Get user from department
        if (!empty($params['department'] && $params['department'] != '0')) {
            $employees = Reports_CustomReport_Helper::getUsersByDepartment($params['department']);
            $employees = array_keys($employees);

            if (!count($employees)) return [];

            $employees = implode("', '", $employees);
            $extWhere = " AND vtiger_crmentity.main_owner_id IN ('{$employees}')";
        }

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        if (!empty($period['from_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND vtiger_crmentity.createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere} GROUP BY vtiger_users.id
            ORDER BY db_amount DESC, number DESC";

        if (isset($params['top']) && !empty($params['top'])) {
            $sql .= ' LIMIT ' . $params['top'];
        }
        else {
            $sql .= ' LIMIT 10';
        }

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['amount'] = CurrencyField::convertToUserFormat($row['db_amount'], null, true);
            $row['number'] = (int)$row['number'];
            $row['user_full_name'] = trim($row['user_full_name']);

            if ($forExport) {
                unset($row['db_amount']);
                unset($row['id']);
            }
            else {
                // Generate link for report for sales order
                $conditions = [[
                    ['main_owner_id', 'c', $row['id']],
                    ['sostatus', 'n', 'Cancelled,Created'],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $row['saleorder_link'] = getListViewLinkWithSearchParams('SalesOrder', $conditions);
            }

            $data[] = $row;
        }

        return $data;
    }

    function getSummaryData($reportData){
        global $current_user;

        $summary = [
            'number' => 0,
            'amount' => 0
        ];

        foreach ($reportData as $row) {
            $summary['number'] += $row['number'];
            $summary['amount'] += $row['db_amount'];
        }

        $summary['number'] = formatNumberToUser($summary['number']);
        $summary['amount'] = CurrencyField::convertToUserFormat($summary['amount']);

        return $summary;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $summaryData = $this->getSummaryData($reportData);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('SUMMARY_DATA', $summaryData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/TopEmployeesBySalesReport/TopEmployeesBySalesReport.tpl');
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