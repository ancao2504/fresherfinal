<?php

/*
	CustomReport.php
	Author: Phuc Lu
	Date: 2020.04.09
*/

class Reports_CustomReport_Helper {

    static function getPeriodFromFilter($params, $maxToIsCurrent = false) {
        $fromDate = '';
        $toDate = '';

        if (!isset($params['period'])) {
            $params['period'] = 'month';
        }

        switch ($params['period']) {
            case 'cumulate':
                $fromDate = '1970-01-01';
                $toDate = '9999-12-31';
                break;
            case 'date': 
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $month = isset($params['month']) ? $params['month'] : Date('m');
                $date = isset($params['date']) ? $params['date'] : Date('d');
                $fromDate = "{$year}-{$month}-{$date}";
                $fromDate = Date('Y-m-d', strtotime($fromDate));
                $toDate = Date('Y-m-d', strtotime($fromDate));
                break;
            case 'week':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $month = isset($params['month']) ? $params['month'] : Date('m');
                $date = Date('d');
                $dayOfWeek = Date('w');
                $fromDate = "{$year}-{$month}-{$date}";
                $fromDate = Date('Y-m-d', strtotime($fromDate. "- {$dayOfWeek} days"));
                $toDate = Date('Y-m-d', strtotime($fromDate. "+ 6 days"));
                break;
            case 'month':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $month = isset($params['month']) ? $params['month'] : Date('m');
                $fromDate = "{$year}-{$month}-01";
                $fromDate = Date('Y-m-d', strtotime($fromDate));
                $toDate = Date('Y-m-t', strtotime($fromDate));
                break;

            case 'quarter':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $quarter = isset($params['quarter']) ? $params['quarter'] : ((int)(Date('m') / 3) + (bool)(Date('m') % 3));
                $month = $quarter * 3 -2;
                $fromDate = "{$year}-{$month}-01";
                $fromDate = Date('Y-m-d', strtotime($fromDate));
                $month += 2;
                $toDate = Date('Y-m-t', strtotime("{$year}-{$month}-01"));
                break;

            case 'year':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $fromDate = "{$year}-01-01";
                $toDate = "{$year}-12-31";
                break;

            case 'custom':
                $fromDate = trim($params['from_date']);
                $toDate = trim($params['to_date']);

                if ($fromDate != '') {
                    $fromDate = DateTimeField::convertToDBFormat($fromDate);
                }

                if ($toDate != '') {
                    $toDate = DateTimeField::convertToDBFormat($toDate);
                }
                break;
                
            case 'three_latest_years':
                $fromDate = Date('Y-01-01', strtotime('-2 year' . Date('Y-01-01')));
                $toDate = Date('Y-12-31');
                break;

            case '3_next_months':
            case '6_next_months':
            case '12_next_months':
                $nextMonths = (int)str_replace('_next_months', '', $params['period']) - 1;
                $fromDate = Date('Y-m-01', strtotime($fromDate . " 1 month"));
                $toDate = Date('Y-m-t', strtotime($fromDate . " +{$nextMonths} month"));
                break;
            
            case 'next_year':
                $fromDate = Date('Y-01-01', strtotime($fromDate . " +1 year"));
                $toDate = Date('Y-12-31', strtotime($fromDate));
                break;

            default:
                if (isset($params['from_date'])) {
                    $fromDate = trim($params['from_date']);
                    $fromDate = DateTimeField::convertToDBFormat($fromDate);
                }
                else {
                    $fromDate = Date('Y-m-d');
                }

                if (isset($params['to_date'])) {
                    $toDate = trim($params['to_date']);
                    $toDate = DateTimeField::convertToDBFormat($toDate);
                }
                else {
                    $toDate = Date('Y-m-t');
                }
        }

        if ($maxToIsCurrent) {
            $toDate = min($toDate, Date('Y-m-d'));
        }

        $fromDate = empty($fromDate) ? '1970-01-01' : $fromDate;
        $toDate = empty($toDate) ? '9999-12-31' : $toDate;
        $fromDateForFilter = new DateTimeField($fromDate);
        $fromDateForFilter = $fromDateForFilter->getDisplayDate();
        $toDateForFilter = new DateTimeField($toDate);
        $toDateForFilter = $toDateForFilter->getDisplayDate();

        return [
            'from_date' => $fromDate . ' 00:00:00',
            'to_date' => $toDate . ' 23:59:39',
            'from_date_for_filter' =>  $fromDateForFilter,
            'to_date_for_filter' => $toDateForFilter,
        ];
    }

    static function getPrevPeriodFromFilter($params, $maxToIsCurrent = false) {
        global $adb;

        $fromDate = '';
        $toDate = '';

        if (!isset($params['period'])) {
            $params['period'] = 'month';
        }

        switch ($params['period']) {
            case 'month':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $month = isset($params['month']) ? $params['month'] : Date('m');
                $month -= 1;

                if ($month == 0) {
                    $month = 12;
                    $year -= 1;
                }

                $fromDate = "{$year}-{$month}-01";
                $fromDate = Date('Y-m-d', strtotime($fromDate));
                $toDate = Date('Y-m-t', strtotime($fromDate));
                break;

            case 'quarter':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $quarter = isset($params['quarter']) ? $params['quarter'] : ((int)(Date('m') / 3) + (bool)(Date('m') % 3));
                $quarter -= 1;

                if ($quarter == 0) {
                    $quarter = 4;
                    $year -= 1;
                }

                $month = $quarter * 3 -2;
                $fromDate = "{$year}-{$month}-01";
                $fromDate = Date('Y-m-d', strtotime($fromDate));
                $month += 2;
                $toDate = Date('Y-m-t', strtotime("{$year}-{$month}-01"));
                break;

            case 'year':
                $year = isset($params['year']) ? $params['year'] : Date('Y');
                $year -= 1;
                $fromDate = "{$year}-01-01";
                $toDate = "{$year}-12-31";
                break;

            case 'custom':
                $fromDate = trim($params['from_date']);
                $toDate = trim($params['to_date']);

                if ($fromDate != '' && $toDate != '') {
                    $fromDate = DateTimeField::convertToDBFormat($fromDate);
                    $toDate = DateTimeField::convertToDBFormat($toDate);

                    $dateDiff = date_diff($toDate, $fromDate);
                    $toDate = Date('Y-m-d', strtotime("-1 day", $fromDate));
                    $fromDate = Date('Y-m-d', strtotime("-{$dateDiff} day", $toDate));
                }
                else {
                    if ($fromDate != '') {
                        $toDate = Date('Y-m-d', strtotime("-1 day", $fromDate));
                        $fromDate = '1970-01-01';
                    }
                }

                break;
                
            case 'three_latest_years':
                $fromDate = Date('Y-01-01', strtotime('-6 year' . Date('Y-01-01')));
                $toDate = Date('Y-12-31', strtotime('-3 year' . Date('Y-01-01')));
                break;
        }

        if ($maxToIsCurrent) {
            $toDate = min($toDate, Date('Y-m-d'));
        }

        return [
            'from_date' => (empty($fromDate) ? '' : $fromDate. ' 00:00:00'),
            'to_date' => (empty($toDate) ? '' : $toDate. ' 23:59:39')
        ];
    }

    static function getRoleForFilter($addAllOption = true) {
        $rolesForFilter = [];
        
        $allRoles = Settings_Roles_Record_Model::getAll();

        if ($addAllOption) {
            $rolesForFilter = [
                '0' => vtranslate('LBL_REPORT_ALL', 'Reports')
            ];
        }

        foreach ($allRoles as $role) {
            $rolesForFilter[$role->get('roleid')] = $role->get('rolename');
        }

        return  $rolesForFilter;
    }

    static function getAllDepartments($addAllOption = true) {
        global $adb;

        $departments = [];

        $basicDepartments = Vtiger_Util_Helper::getPickListValues('users_department');

        if ($addAllOption) {
            $departments = [
                '0' => vtranslate('LBL_REPORT_ALL', 'Reports')
            ];
        }

        foreach ($basicDepartments as $department) {
            $departments[$department] = vtranslate($department, 'Users');
        }

        return  $departments;
    }

    static function getRangesByIntervalMonthInRange($start, $end, $interval, $inMonth = true) {
        if (empty($start) || empty($end) || empty($interval)) {
            return [];
        }

        $orginStart = Date('Y-m-d', strtotime($start));

        if ($inMonth) {
            $start = Date('Y-m-01', strtotime($start));
        }

        $timeStart = strtotime($start);
        $timeEnd = strtotime($end);
        $timeTemp = $timeStart;
        $ranges = [];

        while ($timeTemp <= $timeEnd) {            
            $range = [
                'from' => MAX(Date('Y-m-d', $timeTemp), $orginStart)
            ];

            $timeTemp = strtotime("+{$interval} month", $timeTemp);

            if ($timeTemp > $timeEnd) {
                $range['to'] = Date('Y-m-d', $timeEnd);
                $ranges[] = $range;
                break;
            }
            
            $range['to'] = Date('Y-m-d', strtotime('-1 day', $timeTemp));            
            $ranges[] = $range;
        }

        return $ranges;
    }

    static function getUsersByRoleforFilter($roles, $addEmptyOption = true, $addAllOption = true) {
        global $adb, $fullNameConfig;
        $users = [];

        if ($addEmptyOption) {
            $users[''] = vtranslate('LBL_REPORT_CHOOSE_EMPLOYEE', 'Reports');
        }

        if ($addAllOption) {
            $users['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        if (empty($roles) || $roles == '0' || (is_array($roles) && in_array('0', $roles))) {
            $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');
            $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users";
    
            $result = $adb->pquery($sql);
    
            while ($row = $adb->fetchByAssoc($result)) {
                $users[$row['id']] = trim($row['user_full_name']);
            }
        }
        else {
            if (!is_array($roles)) {
                $roles = [$roles];
            }

            foreach ($roles as $role) {
                if (empty($role)) continue;
                
                $role = Settings_Roles_Record_Model::getInstanceById($role);
                $employees = $role->getUsers();

                foreach ($employees as $userId => $employee) {
                    $firstName = $employee->get('first_name');
                    $lastName = $employee->get('last_name');

                    if ($fullNameConfig['full_name_order'][0] == 'firstname') {
                        $fullName = "{$firstName} {$lastName}";
                    }
                    else {
                        $fullName = "{$lastName} {$firstName}";
                    }

                    $users[$userId] = trim($fullName);
                }
            }
        }       
                
        return $users;
    }

    static function getUsersByDepartment($department, $addEmptyOption = true, $addAllOption = true) {
        global $adb;
        $users = [];

        if ($addEmptyOption) {
            $users[''] = vtranslate('LBL_REPORT_CHOOSE_EMPLOYEE', 'Reports');
        }

        if ($addAllOption) {
            $users['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        if (empty($department) || $department == '0' || (is_array($department) && in_array('0', $department))) {
            $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users";

            $result = $adb->pquery($sql);
    
            while ($row = $adb->fetchByAssoc($result)) {
                $users[$row['id']] = trim($row['user_full_name']);
            }
        }
        else {
            if (!is_array($department)) {
                $department = [$department];
            }

            $deparmentString = implode("', '", $department);
            $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE users_department IN ('{$deparmentString}')";

            $result = $adb->pquery($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $users[$row['id']] = trim($row['user_full_name']);
            }
        }
        
        return $users;
    }

    static function getUsersGroupByRole($roles) {
        global $adb, $fullNameConfig;

        $roleUsers = [];

        if (empty($roles) || $roles == '0' || (is_array($roles) && in_array('0', $roles))) {
            $sql = "SELECT GROUP_CONCAT(roleid) FROM vtiger_role";
            $roles = $adb->getOne($sql);
            $roles = explode(',', $roles);
        }

        foreach ($roles as $role) {
            if (empty($role)) continue;
            
            $role = Settings_Roles_Record_Model::getInstanceById($role);
            $employees = $role->getUsers();
            $roleUsers[$role->getId()] = [];

            foreach ($employees as $userId => $employee) {
                $firstName = $employee->get('first_name');
                $lastName = $employee->get('last_name');

                if ($fullNameConfig['full_name_order'][0] == 'firstname') {
                    $fullName = "{$firstName} {$lastName}";
                }
                else {
                    $fullName = "{$lastName} {$firstName}";
                }

                $roleUsers[$role->getId()][$userId] = trim($fullName);
            }
        }

        return $roleUsers;
    }
    
    static function getUsersGroupByDepartment($departments) {
        global $adb, $fullNameConfig;

        $roleUsers = [];

        if (empty($departments) || $departments == '0' || (is_array($departments) && in_array('0', $departments))) {
            $sql = "SELECT GROUP_CONCAT(groupid) FROM vtiger_groups WHERE is_custom = 0";
            $departments = $adb->getOne($sql);
            $departments = explode(',', $departments);
        }

        if (!array($departments)) {
            $departments = [$departments];
        }

        foreach ($departments as $deparment) {
            if (empty($deparment)) continue;
            
            $employees = self::getUsersByDepartment($deparment, false, false);
            $roleUsers[$deparment] = [];

            foreach ($employees as $userId => $name) {
                $roleUsers[$deparment][$userId] = $name;
            }
        }

        return $roleUsers;
    }


    static function getIndustryValues($addEmptyOption = false, $addAllOption = false, $addNoOption = true) {
        $industries = [];

        if ($addEmptyOption) {
            $industries[''] = vtranslate('LBL_REPORT_CHOOSE_INDUSTRY', 'Reports');
        }

        if ($addAllOption) {
            $industries['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }
        
        if ($addNoOption) {
            $industries['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        }

        $pickListValues = Vtiger_Util_Helper::getPickListValues('industry');

        foreach ($pickListValues as $key => $pickListValue) {
            $industries[$pickListValue] = vtranslate($pickListValue);
        }

        return $industries;
    }
    
    static function getSourceValues($addEmptyOption = false, $addAllOption = false, $addNoOption = true) {
        $sources = [];

        if ($addEmptyOption) {
            $sources[''] = vtranslate('LBL_REPORT_CHOOSE_SOURCE', 'Reports');
        }

        if ($addAllOption) {
            $sources['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }
        
        if ($addNoOption) {
            $sources['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        }

        $pickListValues = Vtiger_Util_Helper::getPickListValues('leadsource');

        foreach ($pickListValues as $key => $pickListValue) {
            $sources[$pickListValue] = vtranslate($pickListValue);
        }

        return $sources;
    }

    static function getPotentialFailedReasonValues($addEmptyOption = false, $addAllOption = false) {
        $reasons = [];

        if ($addEmptyOption) {
            $reasons[''] = vtranslate('LBL_REPORT_CHOOSE_REASON', 'Reports');
        }

        if ($addAllOption) {
            $reasons['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $pickListValues = Vtiger_Util_Helper::getPickListValues('potentiallostreason');

        foreach ($pickListValues as $key => $pickListValue) {
            $reasons[$pickListValue] = vtranslate($pickListValue, 'Potentials');
        }

        return $reasons;
    }
    
    static function getProvinceValues($addEmptyOption = false, $addAllOption = false, $addNoOption = true, $fromModule = 'Accounts') {
        global $adb;
        $provinces = [];

        if ($addEmptyOption) {
            $provinces[''] = vtranslate('LBL_REPORT_CHOOSE_PROVINCE', 'Reports');
        }

        if ($addAllOption) {
            $provinces['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }
        
        if ($addNoOption) {
            $provinces['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        }
        
        $sql = "SELECT DISTINCT BINARY bill_city AS bill_city
            FROM vtiger_account
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = accountid)
            INNER JOIN vtiger_accountbillads ON (accountid = accountaddressid)
            WHERE bill_city IS NOT NULL AND bill_city != ''
            ORDER BY bill_city";

        if ($fromModule == 'Contacts') {
            $sql = "SELECT DISTINCT BINARY mailingcity AS bill_city
                FROM vtiger_contactdetails
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                WHERE mailingcity IS NOT NULL AND mailingcity != ''
                ORDER BY bill_city";
        }

        
        if ($fromModule == 'Leads') {
            $sql = "SELECT DISTINCT BINARY city AS bill_city
                FROM vtiger_leaddetails
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND leadid = crmid)
                INNER JOIN vtiger_leadaddress ON (leadaddressid = leadid)
                WHERE city IS NOT NULL AND city != ''
                ORDER BY bill_city";
        }

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $provinces[$row['bill_city']] = $row['bill_city'];
        }

        return $provinces;
    }

    static function getAllProvinceValues($addEmptyOption = false, $addAllOption = false, $addNoOption = true, $fromModules = ['Accounts']) {
        $provinces = [];

        if ($addEmptyOption) {
            $provinces[''] = vtranslate('LBL_REPORT_CHOOSE_PROVINCE', 'Reports');
        }

        if ($addAllOption) {
            $provinces['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }
        
        if ($addNoOption) {
            $provinces['1'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        }

        foreach ($fromModules as $module) {
            $temp = self::getProvinceValues(false, false, false, $module);

            if (count($provinces)) {
                $provinces = array_merge($provinces, $temp);
            }
            else {
                $provinces = $temp;
            }
        }

        return array_unique($provinces);
    }

    static function getPotentialCustomerTypeValues($addEmptyOption = false, $addAllOption = false) {
        $customerTypes = [];

        if ($addEmptyOption) {
            $customerTypes[''] = vtranslate('LBL_REPORT_CHOOSE_CUSTOMER_TYPE', 'Reports');
        }

        if ($addAllOption) {
            $customerTypes['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $pickListValues = Vtiger_Util_Helper::getPickListValues('opportunity_type');

        foreach ($pickListValues as $key => $pickListValue) {
            $customerTypes[$pickListValue] = vtranslate($pickListValue, 'Potentials');
        }

        return $customerTypes;
    }

    static function getPredictionTimeOptions() {
        return [
            '3_next_months' => vtranslate('LBL_REPORT_THREE_NEXT_MONTHS', 'Reports'),
            '6_next_months' => vtranslate('LBL_REPORT_SIX_NEXT_MONTHS', 'Reports'),
            '12_next_months' => vtranslate('LBL_REPORT_TWELVE_NEXT_MONTHS', 'Reports'),
            'next_year' => vtranslate('LBL_REPORT_NEXT_YEAR', 'Reports'),
        ];
    }

    static function getCustomerGroups($getWarningOnly = true, $fullData = false) {
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);
        $groups = [];
        $returnGroups = [];

        if (!isset($currentConfig['customer_groups']) || !isset($currentConfig['customer_groups']['groups']) || empty($currentConfig['sales_forecast']['min_successful_percentage'])) {
            return false;
        }
        else {
             $groups = $currentConfig['customer_groups']['groups'];
        }

        foreach ($groups as $group) {
            $fromValue = CurrencyField::convertToDBFormat($group['from_value']);            
            $toValue = CurrencyField::convertToDBFormat($group['to_value']);

            $label = $group['group_name'] . ' (' . (CurrencyField::convertToUserFormat($fromValue)) . (!empty($toValue) ? ' - ' . (CurrencyField::convertToUserFormat($toValue)) : '') . ')';
                    
            if ($getWarningOnly) {
                if ($group['alert_group'] && $fromValue > 1) {
                    if ($fullData) {
                        $returnGroups[$group['group_id']] = array_merge(['label' =>  $label], $group);
                    }
                    else {
                        $returnGroups[$group['group_id']] = $label;
                    }
                }
            }
            else {
                if ($fullData) {
                    $returnGroups[$group['group_id']] = array_merge(['label' =>  $label], $group);
                }
                else {
                    $returnGroups[$group['group_id']] = $label;
                }
            }
        }

        return $returnGroups;
    }

    static function getProducts($addEmptyOption = false, $addAllOption = false) {
        global $adb;

        $products = [];

        if ($addEmptyOption) {
            $products[''] = vtranslate('LBL_REPORT_CHOOSE_PRODUCT', 'Reports');
        }

        if ($addAllOption) {
            $products['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $sql = "SELECT productid, productname
            FROM vtiger_products
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = productid)";
        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $products[$row['productid']] = $row['productname'];
        }

        return $products;
    }

    static function getServices($addEmptyOption = false, $addAllOption = false) {
        global $adb;

        $services = [];

        if ($addEmptyOption) {
            $services[''] = vtranslate('LBL_REPORT_CHOOSE_SERVICE', 'Reports');
        }

        if ($addAllOption) {
            $services['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $sql = "SELECT serviceid, servicename
            FROM vtiger_service
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = serviceid)";
        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $services[$row['serviceid']] = $row['servicename'];
        }

        return $services;
    }

    static function formatDayToLongDays($days) {
        if ($days < 7) {
            return $days . ' ' . strtolower(vtranslate('LBL_REPORT_DAY', 'Reports'));
        }

        if ($days < 30) {
            return (int)($days / 7) . ' ' . strtolower(vtranslate('LBL_REPORT_WEEK', 'Reports')) . ($days % 7 > 0 ? ' ' . self::formatDayToLongDays($days % 7) : '');
        }

        if ($days < 365) {
            return (int)($days / 30) . ' ' . strtolower(vtranslate('LBL_REPORT_MONTH', 'Reports')) . ($days % 30 > 0 ? ' ' . self::formatDayToLongDays($days % 30) : '');
        }

        return ($days % 365 > 0 ? vtranslate('LBL_REPORT_GREATER_THAN', 'Reports') . ' ' : '') . (int)($days / 365) . ' ' . strtolower(vtranslate('LBL_REPORT_YEAR', 'Reports'));
    }

    static function getDisplayedByForAnalyzeReport() {
        return [
            'SOURCE' => vtranslate('LBL_REPORT_SOURCE', 'Reports'),
            'PROVINCE' => vtranslate('LBL_REPORT_PROVINCE', 'Reports'),
            'GENDER' => vtranslate('LBL_REPORT_GENDER', 'Reports')
        ];
    }

    static function getDisplayedByForSalesFunnelReport() {
        return [
            'all' => vtranslate('LBL_REPORT_ALL', 'Reports'),
            'employee' => vtranslate('LBL_REPORT_EMPLOYEE', 'Reports'),
            'campaign' => vtranslate('LBL_REPORT_CAMPAIGN', 'Reports'),
        ];
    }

    static function getAllCompanySize($addEmptyOption = false, $addAllOption = false) {
        $companySizes = [];

        if ($addEmptyOption) {
            $companySizes[''] = vtranslate('LBL_REPORT_CHOOSE_COMPANY_SIZE', 'Reports');
        }

        if ($addAllOption) {
            $companySizes['0'] = vtranslate('LBL_REPORT_ALL', 'Reports');
        }

        $pickListValues = Vtiger_Util_Helper::getPickListValues('accounts_company_size');

        foreach ($pickListValues as $key => $pickListValue) {
            $companySizes[$pickListValue] = vtranslate($pickListValue, 'Accounts');
        }

        return $companySizes;
    }

    static function getGroupsOfProducts() {
        global $adb;
        $products = [];

        $sql = "SELECT productid, productcategory
            FROM vtiger_products
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = productid)";
        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $products[$row['productid']] = (empty($row['productcategory']) ? vtranslate('LBL_REPORT_UNDEFINED', 'Reports') : $row['productcategory']);
        }

        return $products;
    }

    static function getGroupsOfServices() {
        global $adb;
        $services = [];

        $sql = "SELECT serviceid, servicecategory
            FROM vtiger_service
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = serviceid)";
        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $services[$row['serviceid']] = (empty($row['servicecategory']) ? vtranslate('LBL_REPORT_UNDEFINED', 'Reports') : $row['servicecategory']);
        }

        return $services;
    }

    static function getGeographyReportModules() {
        return [
            '' => vtranslate('LBL_REPORT_CHOOSE_MODULE', 'Reports'),
            'Accounts' => vtranslate('Accounts', 'Accounts'),
            'Meeting' => vtranslate('Meeting', 'Events'),
            'SalesOrder' => vtranslate('SalesOrder', 'SalesOrder'),
            'HelpDesk' => vtranslate('HelpDesk', 'HelpDesk'),
            'Potentials' => vtranslate('Potentials', 'Potentials'),
            'Contacts' => vtranslate('Contacts', 'Contacts'),
            'Leads' => vtranslate('Leads', 'Leads'),
        ];
    }
}