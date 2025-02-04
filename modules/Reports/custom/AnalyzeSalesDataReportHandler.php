<?php

/*
    AnalyzeSalesDataReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.19
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class AnalyzeSalesDataReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/AnalyzeSalesDataReport/AnalyzeSalesDataReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/AnalyzeSalesDataReport/AnalyzeSalesDataReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/AnalyzeSalesDataReportWidgetFilter.tpl';
    protected $reportBy = 'SOURCE';

    // Override this function by Hieu Nguyen on 2020-09-07 to set default value
    function getFilterParams() {
        $params = parent::getFilterParams();

        if (empty($params['displayed_by'])) {
            $params['displayed_by'] = $this->reportBy;
        }

        return $params;
    }

    // Override this function by Hieu Nguyen on 2020-09-07 to set report filter meta
    function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'displayed_by_options' => Reports_CustomReport_Helper::getDisplayedByForAnalyzeReport(),
            'input_validators' => [
                'from_date' => [
                    'mandatory' => false,
                    'type' => 'date',
                    'name' => 'from_date',
                    'label' => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                'to_date' => [
                    'mandatory' => false,
                    'type' => 'date',
                    'name' => 'to_date',
                    'label' => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ]
        ];

        return parent::renderReportFilter($params);
    }

    // Modified by Hieu Nguyen on 2020-09-07 to get report header according to selected displayed_by value
    public function getReportHeaders() {
        $reportBy = !empty($_REQUEST['displayed_by']) ? $_REQUEST['displayed_by'] : $this->reportBy;

        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_' . $reportBy, 'Reports') => '47%',
            vtranslate('LBL_REPORT_LEAD', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_POTENTIAL', 'Reports') => '10%',
            vtranslate('LBL_REPORT_QUOTE', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_SALES_ORDER', 'Reports') =>  '10%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '10%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [];

        $data['lead_number'] = [['Element', vtranslate('LBL_REPORT_LEAD', 'Reports')]];
        $data['potential_number'] = [['Element', vtranslate('LBL_REPORT_POTENTIAL', 'Reports')]];
        $data['quote_number'] = [['Element', vtranslate('LBL_REPORT_QUOTE', 'Reports')]];
        $data['sales_order_number'] = [['Element', vtranslate('LBL_REPORT_SALES_ORDER', 'Reports')]];
        $data['sales'] = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports')]];$links = [];

        foreach ($reportData as $row) {
            if ($row['lead_number'] > 0) $data['lead_number'][] = [$row['label'], (int)$row['lead_number']];
            if ($row['potential_number'] > 0) $data['potential_number'][] = [$row['label'], (int)$row['potential_number']];
            if ($row['quote_number'] > 0) $data['quote_number'][] = [$row['label'], (int)$row['quote_number']];
            if ($row['sales_order_number'] > 0) $data['sales_order_number'][] = [$row['label'], (int)$row['sales_order_number']];
            if ($row['sales'] > 0) $data['sales'][] = [$row['label'], (float)$row['sales']];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $displayedBy = [];
        $groupBy = [];
        $no = 0;
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();

        // Get report by
        switch ($params['displayed_by']) {
            case 'SOURCE':
                $groupBy = 'source';
                $displayedBy = Reports_CustomReport_Helper::getSourceValues(false, false, false);
                $displayedBy['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
                break;

            case 'PROVINCE':
                $groupBy = 'city';
                $displayedBy = Reports_CustomReport_Helper::getProvinceValues(false, false, false);
                $displayedBy1 = Reports_CustomReport_Helper::getProvinceValues(false, false, false, 'Contacts');
                $displayedBy2 = Reports_CustomReport_Helper::getProvinceValues(false, false, false, 'Leads');
                $displayedBy = array_merge($displayedBy, $displayedBy1, $displayedBy2);
                $displayedBy = array_unique($displayedBy);
                $displayedBy['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
                break;

            case 'AGE':
                $groupBy = 'age';
                $displayedBy = [
                    '0_20' => '<20 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                    '21_30' => '21-30 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                    '31_40' => '31-40 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                    '41_50' => '41-50 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                    '51_60' => '50-60 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                    '61_0' => '>60 ' . strtolower(vtranslate('LBL_REPORT_AGE', 'Reports')),
                ];
                break;

            case 'GENDER':
                $groupBy = 'salutation';
                $displayedBy = [
                    'Mr.' => vtranslate('LBL_MALE'),
                    'Ms.' => vtranslate('Female'),
                    'Undefined' =>  vtranslate('LBL_REPORT_UNDEFINED', 'Reports')
                ];
                break;
        }

        foreach ($displayedBy as $type => $label) {
            $data[$type] = [
                'type' => ($forExport ? ++$no : $type) ,
                'label' => $label,
                'lead_number' => 0,
                'potential_number' => 0,
                'quote_number' => 0,
                'sales_order_number' => 0,
                'sales' => 0,
            ];
        }

        // Get leads
        $sql = "SELECT *, COUNT(leadid) AS lead_number
            FROM (
                SELECT IF(leadsource IS NULL OR leadsource = '' , '1', leadsource) AS source, IF(salutation != 'Mr.' AND salutation != 'Ms.' OR salutation IS NULL, 'Undefined', salutation) AS salutation, leadid,
                    CASE
                        WHEN 0 < 21 THEN '0_20'
                        WHEN 0 BETWEEN 21 AND 30 THEN '21_30'
                        WHEN 0 BETWEEN 31 AND 40 THEN '31_40'
                        WHEN 0 BETWEEN 41 AND 50 THEN '41_50'
                        WHEN 0 BETWEEN 51 AND 60 THEN '51_60'
                        WHEN 0 > 60 THEN '61_0'
                    END AS age,
                    BINARY IF(city IS NULL OR city = '', '1', city) AS city
                FROM vtiger_leaddetails
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND leadid = crmid)
                INNER JOIN vtiger_leadaddress ON (leadaddressid = leadid)
                WHERE createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ) as temp
            GROUP BY {$groupBy}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row[$groupBy]]['lead_number'] = (int)$row['lead_number'];
        }

        // Get potential
        $sql = "SELECT *, COUNT(potentialid) AS potential_number
            FROM (
                SELECT source, salutation, potentialid,
                    CASE
                        WHEN temp_age < 21 THEN '0_20'
                        WHEN temp_age BETWEEN 21 AND 30 THEN '21_30'
                        WHEN temp_age BETWEEN 31 AND 40 THEN '31_40'
                        WHEN temp_age BETWEEN 41 AND 50 THEN '41_50'
                        WHEN temp_age BETWEEN 51 AND 60 THEN '51_60'
                        WHEN temp_age > 60 THEN '61_0'
                    END AS age,
                    BINARY city AS city
                FROM (
                    SELECT IF(vtiger_potential.leadsource IS NULL OR vtiger_potential.leadsource = '' , '1', vtiger_potential.leadsource) AS source,
                        IF(salutation != 'Mr.' AND salutation != 'Ms.' OR salutation IS NULL, 'Undefined', salutation) AS salutation, vtiger_potential.potentialid,
                        IF(vtiger_contactsubdetails.birthday IS NOT NULL AND vtiger_contactsubdetails.birthday != '0000-00-00', YEAR(NOW()) - YEAR(vtiger_contactsubdetails.birthday), '') AS temp_age,
                        IF(vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS city
                    FROM vtiger_potential
                    INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                    LEFT JOIN (
                        vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                        INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                        INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    ) ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id AND (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = ''))
                    LEFT JOIN (
                        vtiger_account
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                        INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    ) ON (vtiger_potential.related_to = vtiger_account.accountid)
                    WHERE potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                ) AS temp_1
            ) AS temp_2
            GROUP BY {$groupBy}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row[$groupBy]]['potential_number'] = (int)$row['potential_number'];
        }

        // Get quote
        $sql = "SELECT *, COUNT(quoteid) AS quote_number
            FROM (
                SELECT source, salutation, quoteid,
                    CASE
                        WHEN temp_age < 21 THEN '0_20'
                        WHEN temp_age BETWEEN 21 AND 30 THEN '21_30'
                        WHEN temp_age BETWEEN 31 AND 40 THEN '31_40'
                        WHEN temp_age BETWEEN 41 AND 50 THEN '41_50'
                        WHEN temp_age BETWEEN 51 AND 60 THEN '51_60'
                        WHEN temp_age > 60 THEN '61_0'
                    END AS age,
                    BINARY city AS city
                FROM (
                    SELECT IF(vtiger_quotes.leadsource IS NULL OR vtiger_quotes.leadsource = '' , '1', vtiger_quotes.leadsource) AS source,
                        IF(salutation != 'Mr.' AND salutation != 'Ms.' OR salutation IS NULL, 'Undefined', salutation) AS salutation, vtiger_quotes.quoteid,
                        IF(vtiger_contactsubdetails.birthday IS NOT NULL AND vtiger_contactsubdetails.birthday != '0000-00-00', YEAR(NOW()) - YEAR(vtiger_contactsubdetails.birthday), '') AS temp_age,
                        IF (vtiger_quotes.accountid  = '{$personalAccountId}', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS city
                    FROM vtiger_quotes
                    INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
                    LEFT JOIN (
                        vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                        INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                        INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    ) ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid AND vtiger_quotes.accountid = '{$personalAccountId}')
                    LEFT JOIN (
                        vtiger_account
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                        INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    ) ON (vtiger_quotes.accountid = vtiger_account.accountid)
                    WHERE quote_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                ) AS temp_1
            ) AS temp_2
            GROUP BY {$groupBy}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row[$groupBy]]['quote_number'] = (int)$row['quote_number'];
        }

        // Get sales order
        $sql = "SELECT *, COUNT(salesorderid) AS sales_order_number, SUM(total) AS sales
            FROM (
                SELECT source, salutation, salesorderid, total,
                    CASE
                        WHEN temp_age < 21 THEN '0_20'
                        WHEN temp_age BETWEEN 21 AND 30 THEN '21_30'
                        WHEN temp_age BETWEEN 31 AND 40 THEN '31_40'
                        WHEN temp_age BETWEEN 41 AND 50 THEN '41_50'
                        WHEN temp_age BETWEEN 51 AND 60 THEN '51_60'
                        WHEN temp_age > 60 THEN '61_0'
                    END AS age,
                    BINARY city AS city
                FROM (
                    SELECT IF(vtiger_salesorder.leadsource IS NULL OR vtiger_salesorder.leadsource = '' , '1', vtiger_salesorder.leadsource) AS source,
                        IF(salutation != 'Mr.' AND salutation != 'Ms.' OR salutation IS NULL, 'Undefined', salutation) AS salutation, vtiger_salesorder.salesorderid, vtiger_salesorder.total,
                        IF(vtiger_contactsubdetails.birthday IS NOT NULL AND vtiger_contactsubdetails.birthday != '0000-00-00', YEAR(NOW()) - YEAR(vtiger_contactsubdetails.birthday), '') AS temp_age,
                        IF(vtiger_salesorder.accountid  = '{$personalAccountId}', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS city
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                    LEFT JOIN (
                        vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                        INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                        INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
                    LEFT JOIN (
                        vtiger_account
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                        INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    ) ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                ) AS temp_1
            ) AS temp_2
            GROUP BY {$groupBy}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row[$groupBy]]['sales_order_number'] = (int)$row['sales_order_number'];
            $data[$row[$groupBy]]['sales'] = (float)$row['sales'];

            if ($forExport) {
                $data[$row[$groupBy]]['sales'] = [
                    'value' => (float)$row['sales'],
                    'type' => 'currency'
                ];
            }
        }

        $data = array_values($data);

        return $data;
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
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/AnalyzeSalesDataReport/AnalyzeSalesDataReport.tpl');
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