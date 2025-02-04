<?php

/*
    CustomerHaveNoSOInPeriodReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.02
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class CustomerHaveNoSOInPeriodReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/CustomerHaveNoSOInPeriodReport/CustomerHaveNoSOInPeriodReportFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['period_days'])) {
            $params['period_days'] = 30;
        }

        if (!isset($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function getReportHeaders($params = null) {
        if (empty($params)) {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $filters = $request->get('advanced_filter');
            $params = [];

            foreach ($filters as $filter) {
                $params[$filter['name']] = $filter['value'];
            }
        }

        $headers = [
            vtranslate('LBL_REPORT_NO', 'Reports') => '20px',
            vtranslate('LBL_REPORT_CUSTOMER', 'Reports') => '20%',
            vtranslate('LBL_REPORT_EMAIL', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_PHONE_NUMBER', 'Reports') => '8%',
            vtranslate('LBL_REPORT_ADDRESS', 'Reports') =>  '22%',
        ];

        if (isset($params['target']) && $params['target'] == 'Contact') {
            $headers[vtranslate('LBL_REPORT_AGE', 'Reports')] = '30px';
        }

        $headers = array_merge($headers, [
            vtranslate('LBL_REPORT_ASSIGNEE', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_LATEST_BOOKED_SALES_ORDER', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_TIME_AGO', 'Reports') =>  '10%',
        ]);

        return $headers;
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $data = [];
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $params['period_days'] = Vtiger_Integer_UIType::convertToDBFormat($params['period_days']);

        if ($params['target'] == 'Account') {
            $sql = "SELECT *
                FROM (
                    SELECT vtiger_account.accountid AS record_id, vtiger_account.accountname AS record_name, vtiger_account.email1 AS email, vtiger_account.phone,
                        bill_street AS address, '' AS age, {$userFullNameField} AS user_full_name, MAX(IF(salesorder_crmentity.createdtime IS NULL, '', salesorder_crmentity.createdtime)) AS latest_date,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, account_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                    INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    LEFT JOIN vtiger_users ON (account_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled'))
                    ) ON (vtiger_account.accountid = vtiger_salesorder.accountid)
                    WHERE vtiger_account.accountid != '{$personalAccountId}'
                    GROUP BY vtiger_account.accountid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');

            $sql = "SELECT *
                FROM (
                    SELECT vtiger_contactdetails.contactid AS record_id, {$contactFullNameField} AS record_name, vtiger_contactdetails.email, vtiger_contactdetails.mobile AS phone,
                        mailingstreet AS address, YEAR(NOW()) - YEAR(birthday) AS age, {$userFullNameField} AS user_full_name,
                        MAX(IF(salesorder_crmentity.createdtime IS NULL, '', salesorder_crmentity.createdtime)) AS latest_date,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, contact_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                    LEFT JOIN vtiger_users ON (contact_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (
                        vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled'))
                    ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
                    WHERE vtiger_contactdetails.contacts_type = 'Customer'
                    GROUP BY vtiger_contactdetails.contactid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";
        }

        $result = $adb->pquery($sql);
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            // Handle time
            if (!empty($row['latest_date'])) {
                $date = new DateTimeField($row['latest_date']);
                $row['latest_date'] = $date->getDisplayDate();
            }

            // Handle time ago
            $row['inactive_days'] = Reports_CustomReport_Helper::formatDayToLongDays($row['inactive_days']);

            if ($forExport) {
                $row['record_id'] = ++$no;

                if (!isset($params['target']) || $params['target'] != 'Contact') {
                    unset($row['age']);
                }
            }

            $data[] = $row;
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

        $viewer->display('modules/Reports/tpls/CustomerHaveNoSOInPeriodReport/CustomerHaveNoSOInPeriodReport.tpl');
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