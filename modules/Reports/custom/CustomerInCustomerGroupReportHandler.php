<?php

/*
    CustomerInCustomerGroupReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.03
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class CustomerInCustomerGroupReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/CustomerInCustomerGroupReport/CustomerInCustomerGroupReportFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function getReportHeaders() {
        $headers = [
            vtranslate('LBL_REPORT_NO', 'Reports') => '20px',
            vtranslate('LBL_REPORT_CUSTOMER', 'Reports') => '40%',
            vtranslate('LBL_REPORT_EMAIL', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_PHONE_NUMBER', 'Reports') => '10%',
            vtranslate('LBL_REPORT_ADDRESS', 'Reports') =>  '20%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '10%',
        ];

        return $headers;
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;
        $customerGroups = Reports_CustomReport_Helper::getCustomerGroups(false, true);

        if ($customerGroups == false || !count($customerGroups))
            return [];

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $data = [];
        $no = 0;

        if ($params['target'] == 'Account') {
            $sql = "SELECT 0 AS no, vtiger_account.accountid AS record_id, vtiger_account.accountname AS record_name, vtiger_account.email1 AS email, vtiger_account.phone,
                    CONCAT(IFNULL(bill_street, ''), ', ', IFNULL(bill_state, ''), ', ', IFNULL(bill_city, ''), ', ', IFNULL(bill_country, '')) AS address, SUM(vtiger_salesorder.total) AS sales
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                    AND vtiger_account.accountid != '{$personalAccountId}'
                GROUP BY vtiger_account.accountid
                ORDER BY sales";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');

            $sql = "SELECT 0 AS no, vtiger_contactdetails.contactid AS record_id, {$contactFullNameField} AS record_name, vtiger_contactdetails.email, vtiger_contactdetails.mobile AS phone,
                    CONCAT(IFNULL(mailingstreet, ''), ', ', IFNULL(mailingstate, ''), ', ', IFNULL(mailingcity, ''), ', ', IFNULL(mailingcountry, '')) AS address, SUM(vtiger_salesorder.total) AS sales
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_contactdetails ON (vtiger_salesorder.contactid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_contactdetails.contactid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                    AND vtiger_contactdetails.contacts_type = 'Customer' AND vtiger_salesorder.accountid = '{$personalAccountId}'
                GROUP BY vtiger_contactdetails.contactid
                ORDER BY sales";
        }

        $result = $adb->pquery($sql);
        $no = 0;
        $currentGroup = current($customerGroups);
        $toValue = $currentGroup['to_value'];

        $data[] = [
            'group' => $currentGroup['label']
        ];

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['no'] = ++$no;

            // Handle address
            $address = $row['address'];

            while (strpos($address, ', , ') !== false) {
                $address = str_replace(', , ', ', ', $address);
            }

            $row['address'] = trim($address, "\, ");

            // Handle time
            if (!empty($row['latest_date'])) {
                $date = new DateTimeField($row['latest_date']);
                $row['latest_date'] = $date->getDisplayDate();
            }

            // Handle group
            $sales = (float)$row['sales'];

            while ($toValue < $sales && $toValue > 0) {
                $currentGroup = next($customerGroups);
                $toValue = $currentGroup['to_value'];
                $data[] = [
                    'group' => $currentGroup['label']
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                $row['sales'] = [
                    'value' => $row['sales'],
                    'type' => 'currency'
                ];
            }

            $data[] = $row;
        }

        while ($toValue > 0) {
            $currentGroup = next($customerGroups);
            $toValue = $currentGroup['to_value'];
            $data[] = [
                'group' => $currentGroup['label']
            ];
        }

        return array_values($data);
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

        $viewer->display('modules/Reports/tpls/CustomerInCustomerGroupReport/CustomerInCustomerGroupReport.tpl');
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