<?php

/*
    SalesAndRevenueByProductReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.27
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesAndRevenueByProductReportHandler extends CustomReportHandler {
    protected $reportObject = 'PRODUCT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_' . $this->reportObject, 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_SALES_ORDER_SALES', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '15%',
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        if (!isset($params['products']) || empty($params['products'])) {
            return [];
        }

        // Get employees
        $products = $params['products'];
        $allProducts = Reports_CustomReport_Helper::getProducts();

        if (in_array('0', $products)) {
            $products = array_keys($allProducts);
        }
        
        $productIds = implode("','", $products);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;
        
        // Generate first data
        foreach ($products as $product) {
            $data[$product] = [
                'id' => ($forExport ? ++$no : $product),
                'name' => $allProducts[$product],
                'potential_sales' => 0,
                'sales_order_sales' => 0,
                'revenue' => 0,
            ];
        }

        $data['all'] = current($data);
        $data['all']['id'] = ($forExport ? '' : 'all');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Data for sale order
        $sql = "SELECT vtiger_products.productid, SUM(vtiger_inventoryproductrel.margin + vtiger_inventoryproductrel.purchase_cost) AS sales_order_sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_products ON (vtiger_inventoryproductrel.productid = vtiger_products.productid)
            INNER JOIN vtiger_crmentity AS product_crmentity ON (product_crmentity.crmid = vtiger_products.productid AND product_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_products.productid IN ('{$productIds}')
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_products.productid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['productid']]['sales_order_sales'] = (int)$row['sales_order_sales'];            
            $data['all']['sales_order_sales'] += (int)$row['sales_order_sales'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['potential_sales'] = [
                    'value' => $value['potential_sales'],
                    'type' => 'currency'
                ];
                
                $data[$key]['sales_order_sales'] = [
                    'value' => $value['sales_order_sales'],
                    'type' => 'currency'
                ];
                
                $data[$key]['revenue'] = [
                    'value' => $value['revenue'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }
    
    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $_REQUEST;
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();
        $products = Reports_CustomReport_Helper::getProducts(false, true);
        $services = Reports_CustomReport_Helper::getServices(false, true);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('PRODUCTS', $products);
        $viewer->assign('SERVICES', $services);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);        
        $viewer->assign('PARAMS', $params);     
        $viewer->assign('REPORT_ID', $this->reportid);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);

        // Define field for validation
        $fields = [
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
        ];

        $viewer->assign('FIELD_VALIDATORS', $fields);
        $viewer->display('modules/Reports/tpls/SalesAndRevenueByProductReport.tpl');
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
    