<?php

/**
 * CustomerHaveNoSOInPeriodWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_CustomerHaveNoSOInPeriodWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getDefaultParams() {
        $defaultParams = [
            'period_days' => 30,
            'target' => 'Accounts',
        ];

        return $defaultParams;
    }

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_FULL_NAME'),
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
                'name' => 'latest_date',
                'label' => vtranslate('LBL_DASHBOARD_LAST_SO_DATE'),
            ],
            [
                'name' => 'inactive_days',
                'label' => vtranslate('LBL_DASHBOARD_SINCE'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $params['period_days'] = Vtiger_Integer_UIType::convertToDBFormat($params['period_days']);

        if ($params['target'] == 'Account') {
            $accountAclQuery = CRMEntity::getListViewSecurityParameter('Accounts');
            $sql = "SELECT *
                FROM (
                    SELECT
                        vtiger_account.accountid AS record_id,
                        'Accounts' AS record_module,
                        vtiger_account.accountname AS record_name,
                        vtiger_account.email1 AS email,
                        vtiger_account.phone,
                        MAX(IF(salesorder_crmentity.createdtime IS NULL, '', salesorder_crmentity.createdtime)) AS latest_date,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, account_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                    INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    LEFT JOIN vtiger_users ON (account_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                    ) ON (vtiger_account.accountid = vtiger_salesorder.accountid)
                    WHERE vtiger_account.accountid != '{$personalAccountId}' {$accountAclQuery}
                    GROUP BY vtiger_account.accountid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";

            $totalSql = "SELECT COUNT(record_id)
                FROM (
                    SELECT
                        vtiger_account.accountid AS record_id,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, account_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                    INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    LEFT JOIN vtiger_users ON (account_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                    ) ON (vtiger_account.accountid = vtiger_salesorder.accountid)
                    WHERE vtiger_account.accountid != '{$personalAccountId}' {$accountAclQuery}
                    GROUP BY vtiger_account.accountid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');
            $contactAclQuery = CRMEntity::getListViewSecurityParameter('Contacts');

            $sql = "SELECT *
                FROM (
                    SELECT vtiger_contactdetails.contactid AS record_id,
                        'Contacts' AS record_module,
                        {$contactFullNameField} AS record_name,
                        vtiger_contactdetails.email,
                        vtiger_contactdetails.mobile AS phone,
                        MAX(IF(salesorder_crmentity.createdtime IS NULL, '', salesorder_crmentity.createdtime)) AS latest_date,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, contact_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                    LEFT JOIN vtiger_users ON (contact_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (
                        vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                    ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
                    WHERE vtiger_contactdetails.contacts_type = 'Customer' {$contactAclQuery}
                    GROUP BY vtiger_contactdetails.contactid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";

            $totalSql = "SELECT COUNT(record_id)
                FROM (
                    SELECT
                        vtiger_contactdetails.contactid AS record_id,
                        DATEDIFF(NOW(), MAX(IF(salesorder_crmentity.createdtime IS NULL, contact_crmentity.createdtime, salesorder_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                    LEFT JOIN vtiger_users ON (contact_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (
                        vtiger_salesorder
                        INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                    ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
                    WHERE vtiger_contactdetails.contacts_type = 'Customer' {$contactAclQuery}
                    GROUP BY vtiger_contactdetails.contactid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}";
        }

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            // Handle time
            if (!empty($row['latest_date'])) {
                $date = new DateTimeField($row['latest_date']);
                $row['latest_date'] = $date->getDisplayDate();
            }

            // Handle time ago
            $row['inactive_days'] = Reports_CustomReport_Helper::formatDayToLongDays($row['inactive_days']);

            $data[] = $row;
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