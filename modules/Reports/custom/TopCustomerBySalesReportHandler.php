<?php

/*
    TopCustomerBySalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.14
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class TopCustomerBySalesReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/TopCustomerBySalesReport/TopCustomerBySalesReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopCustomerBySalesReport/TopCustomerBySalesReportFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopCustomerBySalesReportDetail.js';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopCustomerBySalesReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (empty($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        // Define field for validation
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
            vtranslate('LBL_REPORT_CUSTOMER', 'Reports') => '50%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_QUOTE_NUMBER', 'Reports') => '',
            vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), ['role' => "style"]]];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['record_name']), (float)$row['amount'], "#7cb5ec"];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        // Get customer type
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $personalAccount = Accounts_Data_Helper::getPersonalAccount();
        $customerType = $params['target'];
        $extSelect = 'vtiger_account.accountname AS record_name, vtiger_account.accountid AS record_id';
        $extJoin = '';
        $extWhere = " AND vtiger_account.accountid != '{$personalAccountId}'";
        $extLimit = '';
        $data = [];
        $no = 1;

        if (isset($params['top']) && !empty($params['top'])) {
            $extLimit .= ' LIMIT ' . $params['top'];
        }
        else {
            $extLimit .= ' LIMIT 10';
        }

        if ($customerType == 'Contact') {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');
            $extSelect = "{$contactFullNameField} AS record_name, vtiger_contactdetails.contactid AS record_id";
            $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
            $extWhere = " AND vtiger_account.accountid = '{$personalAccountId}'";
        }

        // Data for sale order
        $sql = "SELECT '0' AS no, {$extSelect}, 0 AS potential_number, 0 AS quote_number,
                COUNT(salesorderid) AS saleorder_number, SUM(total) AS amount
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
            {$extJoin}
            WHERE sostatus NOT IN ('Created', 'Cancelled') {$extWhere} AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY record_id
            ORDER BY amount DESC
            {$extLimit}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['amount'] = (float)$row['amount'];

            // Generate link for report for sales order
            if (!$forExport) {
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['account_id', 'c', $row['record_name']],
                        ['sostatus', 'n', 'Cancelled,Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['account_id', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['sostatus', 'n', 'Cancelled,Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['saleorder_link'] = getListViewLinkWithSearchParams('SalesOrder', $conditions);

                // Generate link for report for potential
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['related_to', 'c', $row['record_name']],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['related_to', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['potential_link'] =  getListViewLinkWithSearchParams('Potentials', $conditions);

                // Generate link for report for quote
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['account_id', 'c', $row['record_name']],
                        ['quotestage', 'n', 'Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['account_id', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['quotestage', 'n', 'Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['quote_link'] =  getListViewLinkWithSearchParams('Quotes', $conditions);
            }

            $data[$row['record_id']] = $row;
        }

        if ($forChart) {
            return array_values($data);
        }

        if (count($data)) {
            $customerIds = array_keys($data);
            $customerIds = implode("','", $customerIds);
            $extField = 'vtiger_potential.related_to';
            $extWhere = '';

            if ($customerType == 'Contact') {
                $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
                $extWhere = " AND (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '')";
                $extField = 'vtiger_potential.contact_id';
            }

            // Count potential
            $sql = "SELECT {$extField} AS record_id, COUNT(potentialid) AS potential_number
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potentialid = potential_crmentity.crmid AND potential_crmentity.deleted = 0)
                {$extJoin}
                WHERE {$extField} IN ('{$customerIds}') {$extWhere} AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY record_id";

            $result = $adb->pquery($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['record_id']]['potential_number'] = $row['potential_number'];
            }

            $extField = 'vtiger_quotes.accountid';
            $extWhere = '';

            if ($customerType == 'Contact') {
                $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
                $extWhere = " AND (vtiger_quotes.accountid = '{$personalAccountId}' OR vtiger_quotes.accountid IS NULL OR vtiger_quotes.accountid = '')";
                $extField = 'vtiger_quotes.contactid';
            }

            // Count quote
            $sql = "SELECT {$extField} AS record_id, COUNT(quoteid) AS quote_number
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (vtiger_quotes.quoteid = quote_crmentity.crmid AND quote_crmentity.deleted = 0)
                {$extJoin}
                WHERE vtiger_quotes.quotestage != 'Created' AND {$extField} IN ('{$customerIds}') {$extWhere} AND quote_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY record_id";

            $result = $adb->pquery($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['record_id']]['quote_number'] = $row['quote_number'];
            }
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                unset($data[$key]['record_id']);
                $data[$key]['amount'] = [
                    'value' => $value['amount'],
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
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/TopCustomerBySalesReport/TopCustomerBySalesReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, false, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}