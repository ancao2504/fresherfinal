<?php

/*
    SalesByProductGroupReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.25
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesByProductGroupReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesByProductGroupReport/SalesByProductGroupReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesByProductGroupReport/SalesByProductGroupReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesByProductGroupReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/SalesByProductGroupReportDetail.js';
    protected $reportObject = 'PRODUCT';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'input_validators' => [
                "from_date" => [
                    "mandatory" => false,
                    "presence" => true,
                    "quickcreate" => false,
                    "masseditable" => false,
                    "defaultvalue" => false,
                    "type" => "date",
                    "name" => "from_date",
                    "label" => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                "to_date" => [
                    "mandatory" => false,
                    "presence" => true,
                    "quickcreate" => false,
                    "masseditable" => false,
                    "defaultvalue" => false,
                    "type" => "date",
                    "name" => "to_date",
                    "label" => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_' . $this->reportObject . '_CATEGORY', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_SALES_NUMBER', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_SALES_ORDER_SALES', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_QUOTE_SALES', 'Reports') =>  '15%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data[] = ['Element', vtranslate('LBL_REPORT_SALES_ORDER_SALES', 'Reports'), vtranslate('LBL_REPORT_QUOTE_SALES', 'Reports'), vtranslate('LBL_REPORT_SALES_NUMBER', 'Reports')];

        foreach ($reportData as $key => $column) {
            if ($key == count($reportData) - 1) break;

            $data[] = [$column['name'], (float)$column['sales'], (float)$column['quote_sales'], (int)$column['sales_number']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        $allProductGroups = Vtiger_Util_Helper::getPickListValues('productcategory');
        $allProductGroups['undefined'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        $productWithGroups = Reports_CustomReport_Helper::getGroupsOfProducts();
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;

        // Generate first data
        foreach ($allProductGroups as $groupId => $groupLabel) {
            $data[$groupLabel] = [
                'id' => ($forExport ? ++$no : $groupId),
                'name' => vtranslate($groupLabel, 'Products'),
                'sales_number' => 0,
                'sales' => 0,
                'quote_sales' => 0,
            ];
        }

        $data['all'] = current($data);
        $data['all']['id'] = ($forExport ? '' : 'all');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Data for sale order
        $sql = "SELECT vtiger_products.productid, SUM(vtiger_inventoryproductrel.quantity) AS sales_number, 
                SUM(
                    CASE
                        WHEN vtiger_inventoryproductrel.discount_amount > 0 THEN
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity - vtiger_inventoryproductrel.discount_amount
                        WHEN vtiger_inventoryproductrel.discount_percent > 0 THEN
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity - vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.discount_percent/100
                        ELSE
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity
                    END
                ) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_products ON (vtiger_inventoryproductrel.productid = vtiger_products.productid)
            INNER JOIN vtiger_crmentity AS product_crmentity ON (product_crmentity.crmid = vtiger_products.productid AND product_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_products.productid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$productWithGroups[$row['productid']]]['sales_number'] += (int)$row['sales_number'];
            $data['all']['sales_number'] += (int)$row['sales_number'];

            $data[$productWithGroups[$row['productid']]]['sales'] += (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Data for quotes
        $sql = "SELECT vtiger_products.productid,
                SUM(
                    CASE
                        WHEN vtiger_inventoryproductrel.discount_amount > 0 THEN
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity - vtiger_inventoryproductrel.discount_amount
                        WHEN vtiger_inventoryproductrel.discount_percent > 0 THEN
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity - vtiger_inventoryproductrel.listprice*vtiger_inventoryproductrel.discount_percent/100
                        ELSE
                            vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity
                    END
                ) AS quote_sales
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity AS quote_crmentity ON (quoteid = quote_crmentity.crmid AND quote_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_quotes.quoteid)
            INNER JOIN vtiger_products ON (vtiger_inventoryproductrel.productid = vtiger_products.productid)
            INNER JOIN vtiger_crmentity AS product_crmentity ON (product_crmentity.crmid = vtiger_products.productid AND product_crmentity.deleted = 0)
            WHERE quotestage NOT IN ('Created') AND quote_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_products.productid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$productWithGroups[$row['productid']]]['quote_sales'] += (float)$row['quote_sales'];
            $data['all']['quote_sales'] += (float)$row['quote_sales'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];

                $data[$key]['quote_sales'] = [
                    'value' => $value['quote_sales'],
                    'type' => 'currency'
                ];
            }
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
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('CHART', $chart);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SalesByProductGroupReport/SalesByProductGroupReport.tpl');
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
