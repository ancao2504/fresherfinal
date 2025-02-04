<?php

/*
    GeographyReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.30
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class GeographyReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/GeographyReport/GeographyReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/GeographyReport/GeographyReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/GeographyReportWidgetFiler.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (empty($params['target'])) {
            $params['target'] = 'Account';
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_modules' => Reports_CustomReport_Helper::getGeographyReportModules(),
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

    protected function getChartData(array $params) {
        $data = $this->getReportData($params);

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
        ];
    }

    public function getReportHeaders($params = null) {
        if ($params == null) {
            $request = new Vtiger_Request($_REQUEST, $_REQUEST);
            $filters = $request->get('advanced_filter');
            $params = [];

            foreach ($filters as $filter) {
                $params[$filter['name']] = $filter['value'];
            }
        }

        $headers = [];

        switch ($params['report_module']) {
            case 'Accounts':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '14%',
                    vtranslate('Accounts', 'Accounts') => '30%',
                    vtranslate('LBL_REPORT_CURRENT_YEAR_SALES', 'Reports') =>  '14%',
                    vtranslate('LBL_REPORT_PREVIOUS_YEAR_SALES', 'Reports') => '14%',
                    vtranslate('LBL_REPORT_ACCUMULATED_SALES', 'Reports') =>  '14%',
                    vtranslate('LBL_REPORT_LATEST_DATE_OF_SALES_ORDER', 'Reports') =>  '14%',
                ];

                break;

            case 'Meeting':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '20%',
                    vtranslate('Meeting', 'Events') => '25%',
                    vtranslate('Start Date & Time', 'Calendar') =>  '15%',
                    vtranslate('End Date & Time', 'Calendar') =>  '15%',
                    vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '15%',
                ];
                break;

            case 'SalesOrder':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '13%',
                    vtranslate('SalesOrder No', 'SalesOrder') => '22%',
                    vtranslate('Customer No', 'SalesOrder') =>  '13%',
                    vtranslate('LBL_STATUS') => '13%',
                    vtranslate('LBL_GRAND_TOTAL') =>  '13%',
                    vtranslate('Created Time') =>  '13%',
                    vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '13%',
                ];

                break;

            case 'HelpDesk':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '15%',
                    vtranslate('Title', 'HelpDesk') => '40%',
                    vtranslate('LBL_STATUS', 'HelpDesk') =>  '15%',
                    vtranslate('Priority') => '15%',
                    vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '10%',
                ];

                break;

            case 'Potentials':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '10%',
                    vtranslate('Potential No', 'Potentials') => '10%',
                    vtranslate('Potentials', 'Potentials') =>  '30%',
                    vtranslate('Sales Stage', 'Potentials') => '10%',
                    vtranslate('Expected Close Date', 'Potentials') =>  '10%',
                    vtranslate('Related To', 'Potentials') =>  '10%',
                    vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '10%',
                    vtranslate('Forecast', 'Potentials') =>  '10%',
                ];

                break;

            case 'Contacts':
                    $headers = [
                        vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '14%',
                        vtranslate('Name', 'Vtiger') => '30%',
                        vtranslate('Company', 'Contacts') =>  '14%',
                        vtranslate('Mobile', 'Vtiger') => '14%',
                        vtranslate('Email', 'Contacts') =>  '14%',
                        vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '14%',
                    ];

                    break;

            case 'Leads':
                $headers = [
                    vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '14%',
                    vtranslate('LBL_FULL_NAME', 'Leads') => '30%',
                    vtranslate('Lead Status', 'Leads') =>  '14%',
                    vtranslate('Mobile', 'Vtiger') => '14%',
                    vtranslate('Email', 'Leads') =>  '14%',
                    vtranslate('LBL_MAIN_OWNER_ID', 'Vtiger') =>  '14%',
                ];

                break;
        }

        return $headers;
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        $data = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        // Get sales order
        switch ($params['report_module']) {
            case 'Accounts':
                $data = $this->getAccountData($period, $params, $forExport);
                break;

            case 'Meeting':
                $data = $this->getMeetingData($period, $params, $forExport);
                break;

            case 'SalesOrder':
                $data = $this->getSOData($period, $params, $forExport);
                break;

            case 'HelpDesk':
                $data = $this->getTicketData($period, $params, $forExport);
                break;

            case 'Potentials':
                $data = $this->getPotentialData($period, $params, $forExport);
                break;

            case 'Contacts':
                $data = $this->getContactData($period, $params, $forExport);
                break;

            case 'Leads':
                $data = $this->getLeadData($period, $params, $forExport);
                break;

        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);
        $reportHeader = $this->getReportHeaders($params);
        $chart = $this->renderChart($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeader);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/GeographyReport/GeographyReport.tpl');
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

    function getAccountData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Accounts');
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $extSql = '';

        if (isset($params['is_real_customer']) && $params['is_real_customer'] == 1) {
            $extSql = "account_type = 'Customer' AND ";
        }

        $sql = "SELECT GROUP_CONCAT(accountid)
            FROM vtiger_account
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = accountid)
            WHERE {$extSql} accountid != '{$personalAccountId}' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $accountIds = $adb->getOne($sql);

        if (empty($accountIds)) {
            return [];
        }


        $params['year'] = Date('Y');
        $params['period'] = 'year';
        $curPeriod = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $params['year']--;
        $prevPeriod = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        $accountData = [];
        $accountIds = explode(",", $accountIds);
        $accountIdsStr = implode("','", $accountIds);

        // Get sales order
        $sql = "SELECT BINARY vtiger_accountbillads.bill_city AS bill_city, longitude, latitude, vtiger_account.accountid AS record_id, vtiger_account.accountname AS record_name,
                SUM(IF(salesorder_crmentity.createdtime BETWEEN '{$curPeriod ['from_date']}' AND '{$curPeriod['to_date']}', vtiger_salesorder.total, 0)) AS cur_sales,
                SUM(IF(salesorder_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', vtiger_salesorder.total, 0)) AS prev_sales,
                SUM(IF(YEAR(salesorder_crmentity.createdtime) = YEAR(NOW()) AND salesorder_crmentity.createdtime <= NOW(), vtiger_salesorder.total, 0)) AS sales,
                MAX(salesorder_crmentity.createdtime) AS latest_date_of_so
            FROM vtiger_account
            INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            LEFT JOIN vtiger_address_coordinates ON (vtiger_address_coordinates.crm_module = 'Accounts' AND vtiger_address_coordinates.crm_id = vtiger_account.accountid AND vtiger_address_coordinates.address_field = 'bill_street')
            LEFT JOIN (vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
            ) ON (vtiger_salesorder.accountid = vtiger_account.accountid AND sostatus NOT IN ('Created', 'Cancelled'))
            WHERE vtiger_account.accountid IN ('{$accountIdsStr}')
            GROUP BY vtiger_account.accountid
            ORDER BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['sales'] = (float)$row['sales'];
            $row['cur_sales'] = (float)$row['cur_sales'];
            $row['prev_sales'] = (float)$row['prev_sales'];
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            if (!empty($row['latest_date_of_so'])) {
                $date = new DateTimeField($row['latest_date_of_so']);
                $row['latest_date_of_so'] = $date->getDisplayDate();
            }

            if ($row['prev_sales'] == 0 || $row['prev_sales'] == '') {
                $balance = 0;
            }
            else {
                $balance = $row['cur_sales'] - $row['prev_sales'];
            }

            if (!$forExport) {
                $row['data_format'] = [
                    'cur_sales' => CurrencyField::convertToUserFormat( $row['cur_sales']),
                    'prev_sales' => CurrencyField::convertToUserFormat( $row['prev_sales']),
                    'sales' => CurrencyField::convertToUserFormat( $row['sales']),
                    'compare_class' => ($balance > 0 ? 'spn-positive far fa-arrow-up' : ( $balance < 0 ? 'spn-negative far fa-arrow-down' : '')),
                    'compare_percent' => ($balance == 0 ? '-' : CurrencyField::convertToUserFormat($balance / $row['prev_sales'] * 100) . '%'),
                    'cluster_object' => vtranslate('Accounts', 'Accounts')
                ];
            }

            $accountData[] = $row;
        }

        if ($forExport) {
            foreach ($accountData as $key => $value) {
                unset($accountData[$key]['record_id']);
                unset($accountData[$key]['alt_bill_city']);
                unset($accountData[$key]['longitude']);
                unset($accountData[$key]['latitude']);

                $accountData[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];

                $accountData[$key]['cur_sales'] = [
                    'value' => $value['cur_sales'],
                    'type' => 'currency'
                ];

                $accountData[$key]['prev_sales'] = [
                    'value' => $value['prev_sales'],
                    'type' => 'currency'
                ];
            }
        }

        return $accountData;
    }

    function getLeadData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Leads');
        $leadFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_leaddetails.firstname', 'lastname' => 'vtiger_leaddetails.lastname'], 'Leads');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $leadData = [];

        $sql = "SELECT BINARY city AS bill_city, longitude, latitude, vtiger_leaddetails.leadid AS record_id, {$leadFullNameField} AS record_name, leadstatus, email, mobile, main_owner_id, {$userFullNameField} AS assignee
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = leadid)
            INNER JOIN vtiger_leadaddress ON (leadaddressid = leadid)
            LEFT JOIN vtiger_address_coordinates ON (vtiger_address_coordinates.crm_module = 'Leads' AND vtiger_address_coordinates.crm_id = vtiger_leaddetails.leadid AND vtiger_address_coordinates.address_field = 'lane')
            INNER JOIN vtiger_users ON (vtiger_users.id = main_owner_id)
            WHERE createdtime BETWEEN '{$period ['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['leadstatus'] = vtranslate($row['leadstatus'], 'Leads');
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('Leads', 'Leads')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['main_owner_id']);
                unset($row['longitude']);
                unset($row['latitude']);
            }

            $leadData[] = $row;
        }

        return $leadData;
    }

    function getContactData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Contacts');
        $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $leadData = [];

        $sql = "SELECT BINARY mailingcity AS bill_city, longitude, latitude, vtiger_contactdetails.contactid AS record_id, {$contactFullNameField} AS record_name, vtiger_account.accountid, vtiger_account.accountname, email, vtiger_contactdetails.mobile, {$userFullNameField} AS assignee
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = contactid)
            INNER JOIN vtiger_contactaddress ON (contactaddressid = contactid)
            LEFT JOIN vtiger_address_coordinates ON (vtiger_address_coordinates.crm_module = 'Contacts' AND vtiger_address_coordinates.crm_id = vtiger_contactdetails.contactid AND vtiger_address_coordinates.address_field = 'mailingstreet')
            INNER JOIN vtiger_users ON (vtiger_users.id = contact_crmentity.main_owner_id)
            LEFT JOIN (vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            ) ON (vtiger_contactdetails.accountid = vtiger_account.accountid)
            WHERE contact_crmentity.createdtime BETWEEN '{$period ['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('Contacts', 'Contacts')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['accountid']);
                unset($row['longitude']);
                unset($row['latitude']);
            }

            $leadData[] = $row;
        }

        return $leadData;
    }

    function getPotentialData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Contacts');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $potentialData = [];

        $sql = "SELECT IF(vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', BINARY mailingcity, BINARY bill_city) AS bill_city,
                IF(vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', contact_coordinate.longitude, account_coordinate.longitude) AS longitude,
                IF(vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', contact_coordinate.latitude, account_coordinate.latitude) AS latitude,
                potentialid AS record_id, potential_no, potentialname AS record_name, sales_stage, closingdate, vtiger_account.accountid, accountname, {$userFullNameField} AS assignee, forecast_amount
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                LEFT JOIN vtiger_address_coordinates AS contact_coordinate ON (contact_coordinate.crm_module = 'Contacts' AND contact_coordinate.crm_id = vtiger_contactdetails.contactid AND contact_coordinate.address_field = 'mailingstreet')
            ) ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id AND (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = ''))
            LEFT JOIN (
                vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                LEFT JOIN vtiger_address_coordinates AS account_coordinate ON (account_coordinate.crm_module = 'Accounts' AND account_coordinate.crm_id = vtiger_account.accountid AND account_coordinate.address_field = 'bill_street')
            ) ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_users ON (vtiger_users.id = potential_crmentity.main_owner_id)
            WHERE potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['sales_stage'] = vtranslate($row['sales_stage'], 'Potentials');
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            $date = new DateTimeField($row['closingdate']);
            $row['closingdate'] = $date->getDisplayDate();

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('Potentials', 'Potentials')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['accountid']);
                unset($row['longitude']);
                unset($row['latitude']);

                $row['forecast_amount'] = [
                    'value' => $row['forecast_amount'],
                    'type' => 'currency'
                ];
            }

            $potentialData[] = $row;
        }

        return $potentialData;
    }

    function getTicketData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $ticketData = [];

        $sql = "SELECT IF(vtiger_troubletickets.parent_id = '{$personalAccountId}' OR vtiger_troubletickets.parent_id IS NULL OR vtiger_troubletickets.parent_id = '', BINARY mailingcity, BINARY bill_city) AS bill_city,
                IF(vtiger_troubletickets.parent_id = '{$personalAccountId}' OR vtiger_troubletickets.parent_id IS NULL OR vtiger_troubletickets.parent_id = '', contact_coordinate.longitude, account_coordinate.longitude) AS longitude,
                IF(vtiger_troubletickets.parent_id = '{$personalAccountId}' OR vtiger_troubletickets.parent_id IS NULL OR vtiger_troubletickets.parent_id = '', contact_coordinate.latitude, account_coordinate.latitude) AS latitude,
                ticketid AS record_id, vtiger_troubletickets.title AS record_name, vtiger_troubletickets.status, vtiger_troubletickets.priority, {$userFullNameField} AS assignee
            FROM vtiger_troubletickets
            INNER JOIN vtiger_crmentity AS ticket_crmentity ON (ticket_crmentity.deleted = 0 AND ticket_crmentity.crmid = ticketid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                LEFT JOIN vtiger_address_coordinates AS contact_coordinate ON (contact_coordinate.crm_module = 'Contacts' AND contact_coordinate.crm_id = vtiger_contactdetails.contactid AND contact_coordinate.address_field = 'mailingstreet')
            ) ON (vtiger_contactdetails.contactid = vtiger_troubletickets.contact_id AND (vtiger_troubletickets.parent_id = '{$personalAccountId}' OR vtiger_troubletickets.parent_id IS NULL OR vtiger_troubletickets.parent_id = ''))
            LEFT JOIN (
                vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                LEFT JOIN vtiger_address_coordinates AS account_coordinate ON (account_coordinate.crm_module = 'Accounts' AND account_coordinate.crm_id = vtiger_account.accountid AND account_coordinate.address_field = 'bill_street')
            ) ON (vtiger_troubletickets.parent_id = vtiger_account.accountid)
            INNER JOIN vtiger_users ON (vtiger_users.id = ticket_crmentity.main_owner_id)
            WHERE ticket_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['priority'] = vtranslate($row['priority'], 'HelpDesk');
            $row['status'] = vtranslate($row['status'], 'HelpDesk');
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('HelpDesk', 'HelpDesk')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['longitude']);
                unset($row['latitude']);
            }

            $ticketData[] = $row;
        }

        return $ticketData;
    }

    function getSOData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $soData = [];

        $sql = "SELECT IF(vtiger_salesorder.accountid  = '{$personalAccountId}', BINARY mailingcity,  BINARY bill_city) AS bill_city,
                IF(vtiger_salesorder.accountid  = '{$personalAccountId}', contact_coordinate.longitude, account_coordinate.longitude) AS longitude,
                IF(vtiger_salesorder.accountid  = '{$personalAccountId}', contact_coordinate.latitude, account_coordinate.latitude) AS latitude,
                salesorderid AS record_id, salesorder_no AS record_name, vtiger_account.accountid, vtiger_account.accountname, sostatus, total, salesorder_crmentity.createdtime, {$userFullNameField} AS assignee
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                LEFT JOIN vtiger_address_coordinates AS contact_coordinate ON (contact_coordinate.crm_module = 'Contacts' AND contact_coordinate.crm_id = vtiger_contactdetails.contactid AND contact_coordinate.address_field = 'mailingstreet')
            ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
            LEFT JOIN (
                vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                LEFT JOIN vtiger_address_coordinates AS account_coordinate ON (account_coordinate.crm_module = 'Accounts' AND account_coordinate.crm_id = vtiger_account.accountid AND account_coordinate.address_field = 'bill_street')
            ) ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_users ON (vtiger_users.id = salesorder_crmentity.main_owner_id)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['sostatus'] = vtranslate($row['sostatus'], 'SalesOrder');
            $row['alt_bill_city'] = unUnicode($row['bill_city']);

            $date = new DateTimeField($row['createdtime']);
            $row['createdtime'] = $date->getDisplayDate();

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('SalesOrder', 'SalesOrder')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['accountid']);
                unset($row['longitude']);
                unset($row['latitude']);

                $row['total'] = [
                    'value' => $row['total'],
                    'type' => 'currency'
                ];
            }

            $soData[] = $row;
        }

        return $soData;
    }

    function getMeetingData($period, $params, $forExport = false) {
        global $adb;

        $aclQuery = CRMEntity::getListViewSecurityParameter('Calendar');
        $userFullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
        $meetingData = [];

        $sql = "SELECT location AS bill_city, longitude, latitude, activityid AS record_id, subject AS record_name, CONCAT(date_start, ' ', time_start) AS starttime, CONCAT(due_date, ' ', time_end) AS duetime, {$userFullNameField} AS assignee
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (crmid = activityid AND deleted = 0)
            INNER JOIN vtiger_users ON (vtiger_users.id = main_owner_id)
            LEFT JOIN vtiger_address_coordinates ON (crm_module = 'Calendar' AND crm_id = activityid AND address_field = 'location')
            WHERE activitytype = 'Meeting' AND date_start BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$aclQuery}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $date = new DateTimeField($row['starttime']);
            $row['starttime'] = $date->getDisplayDateTimeValue();

            $date = new DateTimeField($row['duetime']);
            $row['duetime'] = $date->getDisplayDateTimeValue();

            if (!$forExport) {
                $row['data_format'] = [
                    'cluster_object' => vtranslate('Meeting', 'Events')
                ];
            }

            if ($forExport) {
                unset($row['record_id']);
                unset($row['longitude']);
                unset($row['latitude']);
            }

            $meetingData[] = $row;
        }

        return $meetingData;
    }
}
