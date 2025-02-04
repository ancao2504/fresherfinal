<?php

/*
    TopProductsBySalesNumberReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.14
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class TopProductsBySalesNumberReportHandler extends CustomReportHandler {


    protected $chartTemplate = 'modules/Reports/tpls/TopProductsBySalesNumberReport/TopProductsBySalesNumberReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopProductsBySalesNumberReport/TopProductsBySalesNumberReportFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopProductsBySalesNumberReportDetail.js';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopProductsBySalesNumberReportWidgetFilter.tpl';
    protected $targetModule = 'Products';

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
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_PRODUCT_CODE', 'Reports') =>  '',
            vtranslate('LBL_REPORT_PRODUCT', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), vtranslate('LBL_REPORT_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['productname']), (float)$row['db_amount'], (float)$row['number']];
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

        // Data for sale order
        $sql = "SELECT 0 AS no, vtiger_products.productid, vtiger_products.product_no, vtiger_products.productname,
                SUM(vtiger_inventoryproductrel.quantity) AS number, 0 AS amount, SUM(vtiger_inventoryproductrel.margin + vtiger_inventoryproductrel.purchase_cost) AS db_amount
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS first_crmentity ON (salesorderid = first_crmentity.crmid AND first_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_products ON (vtiger_inventoryproductrel.productid = vtiger_products.productid)
            INNER JOIN vtiger_crmentity AS product_crmentity ON (product_crmentity.crmid = vtiger_products.productid AND product_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled')";

        $sqlParams = [];

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Update params for where
        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND first_crmentity.createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND first_crmentity.createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere} GROUP BY vtiger_products.productid
            ORDER BY number DESC";

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

            if ($forExport) {
                unset($row['db_amount']);
                unset($row['productid']);
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
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('SUMMARY_DATA', $summaryData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('TARGET_MODULE', $this->targetModule);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/TopProductsBySalesNumberReport/TopProductsBySalesNumberReport.tpl');
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