<?php

/**
 * CustomerWillReachHigherMemberLevelWidget
 * Author: Phu Vo
 * Date: 2020.08.28
 */

class Home_CustomerWillReachHigherMemberLevelWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('Account Name', 'Accounts'),
            ],
            [
                'name' => 'address',
                'label' => vtranslate('LBL_DASHBOARD_ADDRESS'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('Email'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone'),
            ],
            [
                'name' => 'sales',
                'label' => vtranslate('LBL_DASHBOARD_CURRENT_SALES'),
                'type' => 'number',
            ],
            [
                'name' => 'missing_value',
                'label' => vtranslate('LBL_REPORT_MISSING_MONEY', 'Reports'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb, $current_user;
            
        $data = [];
        $total = 0;

        $customerGroups = Reports_CustomReport_Helper::getCustomerGroups(true, true);
        $customerGroup = $customerGroups[$params['customer_group']];

        if (!empty($customerGroup)) {
            $toValue = CurrencyField::convertToDBFormat($customerGroup['from_value']) - 1;
            $fromValue = $toValue + 1 - CurrencyField::convertToDBFormat($customerGroup['alert_value']);
            $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
            $customerType = $params['target'];
    
            if ($customerType == 'Account') {
                $sql = "SELECT
                        accountid AS record_id,
                        accountname AS record_name,
                        setype AS record_module,
                        address,
                        email,
                        phone,
                        sales,
                        ({$toValue} - sales + 1) AS missing_value
                    FROM (
                        SELECT vtiger_account.accountid, vtiger_account.accountname, IFNULL(vtiger_account.email1, vtiger_account.email2) AS email, vtiger_account.phone, SUM(vtiger_salesorder.total) AS sales, account_crmentity.setype AS setype, vtiger_accountbillads.bill_street AS address
                        FROM vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                        INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                        LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                        WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid != '{$personalAccountId}'
                        GROUP BY vtiger_account.accountid
                    ) as temp
                    WHERE sales BETWEEN ? AND ?
                    ORDER BY sales DESC";

                $totalSql = "SELECT COUNT(accountid)
                    FROM (
                        SELECT vtiger_account.accountid, SUM(vtiger_salesorder.total) AS sales
                        FROM vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                        INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                        LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                        WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid != '{$personalAccountId}'
                        GROUP BY vtiger_account.accountid
                    ) as temp
                    WHERE sales BETWEEN ? AND ?";
            }
            else {
                $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');

                $sql = "SELECT
                        contactid AS record_id,
                        record_name,
                        setype AS record_module,
                        mailingstreet AS address,
                        email,
                        phone,
                        sales,
                        ({$toValue} - sales + 1) AS missing_value
                    FROM (
                        SELECT vtiger_contactdetails.contactid, {$contactFullNameField} AS record_name, IFNULL(vtiger_account.email1, vtiger_account.email2) AS email, vtiger_account.phone, SUM(vtiger_salesorder.total) AS sales, contact_crmentity.setype, vtiger_contactaddress.mailingstreet
                        FROM vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                        INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                        INNER JOIN vtiger_contactdetails ON (vtiger_salesorder.contactid = vtiger_contactdetails.contactid)
                        INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                        LEFT JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                        WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid = '{$personalAccountId}'
                        GROUP BY vtiger_contactdetails.contactid
                    ) as temp
                    WHERE sales BETWEEN ? AND ?
                    ORDER BY sales DESC";

                $totalSql = "SELECT COUNT(contactid)
                    FROM (
                        SELECT
                            vtiger_contactdetails.contactid, SUM(vtiger_salesorder.total) AS sales
                        FROM vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                        INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                        INNER JOIN vtiger_contactdetails ON (vtiger_salesorder.contactid = vtiger_contactdetails.contactid)
                        INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                        LEFT JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                        WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.accountid = '{$personalAccountId}'
                        GROUP BY vtiger_contactdetails.contactid
                   ) as temp
                    WHERE sales BETWEEN ? AND ?";
            }

            if (!empty($params['length'])) {
                $sql .= " LIMIT {$params['length']}";
                if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
            }
            
            $result = $adb->pquery($sql, [$fromValue, $toValue]);
            $total = $adb->getOne($totalSql, [$fromValue, $toValue]);
    
            while ($row = $adb->fetchByAssoc($result)) {
                $row = decodeUTF8($row);
                $row['sales'] = $this->formatNumberToUser($row['sales']);
                $row['missing_value'] = $this->formatNumberToUser($row['missing_value']);
                $data[] = $row;
            }
        }

        $result = [
            'draw' => intval($params['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => array_values($data),
            'offset' => $params['start'],
            'length' => $params['length'],
        ];

        return $result;
    }
}