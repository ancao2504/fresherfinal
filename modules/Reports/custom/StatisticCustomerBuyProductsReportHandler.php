<?php

/*
    StatisticCustomerBuyProductsReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.16
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class StatisticCustomerBuyProductsReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/StatisticCustomerBuyProductsReport/StatisticCustomerBuyProductsReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $products = Reports_CustomReport_Helper::getProducts(false, false);
        $services = Reports_CustomReport_Helper::getServices(false, false);

        if (empty($products)) {
            $products = $services;
        }
        else {
            $products += $services;
        }

        $this->reportFilterMeta = [
            'all_campaigns' => Campaigns_Data_Model::getAllCampaigns(),
            'products' => $products,
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

    public function getReportHeaders($params) {
        if (empty($params)) {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $filters = $request->get('advanced_filter');
            $params = [];

            foreach ($filters as $filter) {
                $params[$filter['name']] = $filter['value'];
            }
        }

        $headers = [];

        if (isset($params['target']) && $params['target'] == 'Contact') {
            $headers = [
                vtranslate('LBL_REPORT_NO', 'Reports') => '20px',
                vtranslate('LBL_REPORT_CUSTOMER', 'Reports') => '28%',
                vtranslate('LBL_REPORT_BOUGHT_SALES_ORDER', 'Reports') => '25%',
                vtranslate('LBL_REPORT_EMAIL', 'Reports') =>  '10%',
                vtranslate('LBL_REPORT_PHONE_NUMBER', 'Reports') => '10%',
                vtranslate('LBL_REPORT_ADDRESS', 'Reports') =>  '20%',
                vtranslate('LBL_REPORT_AGE', 'Reports') =>  '5%',
            ];
        }

        if (!isset($params['target']) || $params['target'] == 'Account') {
            $headers = [
                vtranslate('LBL_REPORT_NO', 'Reports') => '20px',
                vtranslate('Account Name', 'Accounts') => '33%',
                vtranslate('LBL_REPORT_BOUGHT_SALES_ORDER', 'Reports') => '25%',
                vtranslate('LBL_REPORT_ADDRESS', 'Reports') =>  '20%',
                vtranslate('LBL_REPORT_INDUSTRY', 'Reports') => '10%',
                vtranslate('Phone', 'Accounts') =>  '10%',
            ];
        }

        return $headers;
    }

    function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['bought_products'])) {
            return [];
        }

        $boughtProducts = $params['bought_products'];
        $noBoughtProducts = $params['no_bought_products'];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $productIds = implode("','", array_merge($boughtProducts, (empty($noBoughtProducts) ? [] : $noBoughtProducts)));
        $boughtProductIds = implode("','", $boughtProducts);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $no = 0;
        $data = [];

        if (!empty($noBoughtProducts) && !empty(array_intersect($boughtProducts, $noBoughtProducts))) {
            return [];
        }

        // Get all sales order name
        $sql = "SELECT salesorderid, salesorder_no FROM vtiger_salesorder";
        $result = $adb->pquery($sql);
        $allSalesOrders = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $allSalesOrders[$row['salesorderid']] = $row['salesorder_no'];
        }

        // Get customer that buy bought products
        if ($params['target'] == 'Account') {
            $sql = "SELECT 0 as no, vtiger_account.accountid AS record_id, vtiger_account.accountname AS record_name, GROUP_CONCAT(DISTINCT IF(vtiger_inventoryproductrel.productid IN ('{$boughtProductIds}'), vtiger_salesorder.salesorderid , '')) AS salesorders, CONCAT(IFNULL(bill_street, ''), ', ', IFNULL(bill_state, ''), ', ', IFNULL(bill_city, ''), ', ', IFNULL(bill_country, '')) AS address,
                    vtiger_account.industry, vtiger_account.phone, GROUP_CONCAT(DISTINCT vtiger_inventoryproductrel.productid) product_ids
                FROM vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                INNER JOIN vtiger_salesorder ON (vtiger_account.accountid = vtiger_salesorder.accountid)
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                    AND vtiger_inventoryproductrel.productid IN ('{$productIds}') AND vtiger_salesorder.accountid != '{$personalAccountId}'
                GROUP BY record_id
                ORDER BY record_id";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');

            $sql = "SELECT 0 as no,vtiger_contactdetails.contactid AS record_id, {$contactFullNameField} AS record_name, GROUP_CONCAT(DISTINCT IF(vtiger_inventoryproductrel.productid IN ('{$boughtProductIds}'), vtiger_salesorder.salesorderid , '')) AS salesorders, vtiger_contactdetails.email, vtiger_contactdetails.mobile AS phone,
                    CONCAT(IFNULL(mailingstreet, ''), ', ', IFNULL(mailingstate, ''), ', ', IFNULL(mailingcity, ''), ', ', IFNULL(mailingcountry, '')) AS address,
                    YEAR(NOW()) - YEAR(birthday) AS age, GROUP_CONCAT(DISTINCT vtiger_inventoryproductrel.productid) product_ids
                FROM vtiger_contactdetails
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_salesorder ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                    AND vtiger_inventoryproductrel.productid IN ('{$productIds}') AND vtiger_salesorder.accountid = '{$personalAccountId}'
                GROUP BY record_id
                ORDER BY record_id";
        }

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $productIds = explode(",", $row['product_ids']);

            // Check if bought all products in bought products or not
            if (count(array_diff($boughtProducts, $productIds)) > 0) {
                continue;
            }

            // Check if bought all products in no bought products or not
            if (!empty($noBoughtProducts) && count(array_diff($noBoughtProducts, $productIds)) == 0) {
                continue;
            }

            $row['no'] = ++$no;

            // Handle address
            $address = $row['address'];

            while (strpos($address, ', , ') !== false) {
                $address = str_replace(', , ', ', ', $address);
            }

            $row['address'] = trim($address, "\, ");

            // Translate industry
            if (isset($row['industry'])) {
                $row['industry'] = vtranslate($row['industry']);
            }

            // Handle sales order list
            $salesOrders = [];

            foreach(explode(",", $row['salesorders']) as $salesOrderId) {
                $salesOrders[$salesOrderId] = $allSalesOrders[$salesOrderId];
            }

            $row['salesorders'] = ($forExport ? implode(", ", $salesOrders) : $salesOrders);

            unset($row['product_ids']);

            if ($forExport) {
                unset($row['record_id']);
                unset($row['salesorder_ids']);
            }

            $data[] = $row;
        }

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportHeaders = $this->getReportHeaders($params);
        $reportData = $this->getReportData($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/StatisticCustomerBuyProductsReport/StatisticCustomerBuyProductsReport.tpl');
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