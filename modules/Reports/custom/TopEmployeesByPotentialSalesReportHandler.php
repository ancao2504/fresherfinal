<?php

/*
    TopEmployeesByPotentialSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class TopEmployeesByPotentialSalesReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/TopEmployeesByPotentialSalesReport/TopEmployeesByPotentialSalesReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopEmployeesByPotentialSalesReport/TopEmployeesByPotentialSalesReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopEmployeesByPotentialSalesReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopEmployeesByPotentialSalesReportDetail.js';
    protected $targetModule = 'POTENTIAL';

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
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '30%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '19%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports'), vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['user_full_name']), (float)$row['potential_sales'], (float)$row['potential_number']];
            $links[] = '';
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for sale order
        $sql = "SELECT 0 as no, id, {$fullNameField} AS user_full_name, SUM(amount) AS potential_sales, COUNT(potentialid) AS potential_number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = potentialid)
            INNER JOIN vtiger_users ON (main_owner_id = id)
            WHERE potentialresult = 'Closed Won'";

        $sqlParams = [];

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Update params for where
        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere}
        GROUP BY id
        ORDER BY potential_sales DESC
        LIMIT 10";

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['potential_sales'] = (float)$row['potential_sales'];
            $row['potential_number'] = (int)$row['potential_number'];

            if ($forExport) {
                $row['potential_number'] = [
                    'value' => $row['potential_number'],
                    'type' => 'currencry'
                ];
                unset($row['id']);
            }

            $data[] = $row;
        }

        $data = array_values($data);

        return $data;
    }

    function getSummaryData($reportData){
        global $current_user;

        $summary = [
            'number' => 0,
            'amount' => 0
        ];

        foreach ($reportData as $row) {
            $summary['number'] += $row['potential_number'];
            $summary['amount'] += $row['potential_sales'];
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
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('SUMMARY_DATA', $summaryData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('TARGET_MODULE', $this->targetModule);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/TopEmployeesByPotentialSalesReport/TopEmployeesByPotentialSalesReport.tpl');
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