<?php

/*
    NearlyReachNewLevelCustomerReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.25
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class NearlyReachNewLevelCustomerReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/NearlyReachNewLevelCustomerReport/NearlyReachNewLevelCustomerReportFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (empty($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'customer_groups' => Reports_CustomReport_Helper::getCustomerGroups(),
        ];

        return parent::renderReportFilter($params);
    }

    function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_CUSTOMER', 'Reports') =>  '47%',
            vtranslate('LBL_REPORT_EMAIL', 'Reports') =>  '20%',
            vtranslate('LBL_REPORT_PHONE_NUMBER', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_CURRENT_SALES', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_MISSING_MONEY', 'Reports') =>  '10%',
        ];
    }

    function getReportData($params, $forExport = false) {
        global $adb;
        $customerGroups = Reports_CustomReport_Helper::getCustomerGroups(true, true);

        if ($customerGroups == false || !count($customerGroups) || empty($params['customer_group']))
            return [];

        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);
        $customerGroup = $customerGroups[$params['customer_group']];
        $toValue = CurrencyField::convertToDBFormat($customerGroup['from_value']) - 1;
        $fromValue = $toValue + 1 - CurrencyField::convertToDBFormat($customerGroup['alert_value']);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $customerType = $params['target'];
        $getType = '';
        $data = [];
        $no = 0;

        if (isset($currentConfig['customer_groups']) && isset($currentConfig['customer_groups']['customer_group_calculate_by'])) {
            $getType = $currentConfig['customer_groups']['customer_group_calculate_by'];
        }
        else {
            $getType = 'cummulation';
        }

        $extSql = '';

        if ($getType == 'year') {
            $fromDate = Date('Y-01-01 00:00:00');
            $toDate = Date('Y-12-31 23:59:59');
            $extSql = "AND salesorder_crmentity.createdtime BETWEEN '{$fromDate}' AND '{$toDate}'";
        }

        if ($customerType == 'Account') {
            $sql = "SELECT accountid AS record_id,accountname AS record_name, email, phone, sales, ({$toValue} - sales + 1) AS missing_value
                FROM (
                    SELECT vtiger_account.accountid, vtiger_account.accountname, IFNULL(vtiger_account.email1, vtiger_account.email2) AS email, vtiger_account.phone, SUM(vtiger_salesorder.total) AS sales
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid != '{$personalAccountId}' {$extSql}
                    GROUP BY vtiger_account.accountid
                ) as temp
                WHERE sales BETWEEN ? AND ?
                ORDER BY sales DESC";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');

            $sql = "SELECT contactid AS record_id, record_name, email, phone, sales, ({$toValue} - sales + 1) AS missing_value
                FROM (
                    SELECT vtiger_contactdetails.contactid, {$contactFullNameField} AS record_name, IFNULL(vtiger_account.email1, vtiger_account.email2) AS email, vtiger_account.phone, SUM(vtiger_salesorder.total) AS sales
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactdetails ON (vtiger_salesorder.contactid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid = '{$personalAccountId}' {$extSql}
                    GROUP BY vtiger_contactdetails.contactid
                ) as temp
                WHERE sales BETWEEN ? AND ?
                ORDER BY sales DESC";
        }

        $result = $adb->pquery($sql, [$fromValue, $toValue]);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['record_id']] = $row;

            if ($forExport) {
                $data[$row['record_id']]['sales'] = [
                    'value' => $data[$row['record_id']]['sales'] ,
                    'type' => 'currency'
                ];

                $data[$row['record_id']]['missing_value'] = [
                    'value' => $data[$row['record_id']]['missing_value'] ,
                    'type' => 'currency'
                ];

                $data[$row['record_id']]['record_id'] = ++$no;
            }
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);
        $reportHeader = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeader);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/NearlyReachNewLevelCustomerReport/NearlyReachNewLevelCustomerReport.tpl');
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