<?php

/**
 * CustomerUnfollowedInPeriodWidget
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_CustomerUnfollowedInPeriodWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('LBL_DASHBOARD_LAST_CONTACT_DATE'),
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
                        MAX(IFNULL(activity_crmentity.modifiedtime, '')) AS latest_date,
                        DATEDIFF(NOW(), MAX(IFNULL(activity_crmentity.modifiedtime, account_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    LEFT JOIN vtiger_users ON (account_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_seactivityrel
                        INNER JOIN vtiger_activity ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid AND (activitytype = 'Emails' OR eventstatus IN ('Held', 'Completed') OR status IN ('Completed')))
                        INNER JOIN vtiger_crmentity AS activity_crmentity ON (vtiger_activity.activityid = activity_crmentity.crmid AND activity_crmentity.deleted = 0)) ON (vtiger_seactivityrel.crmid = vtiger_account.accountid
                    )
                    WHERE vtiger_account.accountid != '{$personalAccountId}' {$accountAclQuery}
                    GROUP BY vtiger_account.accountid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";

            $totalSql = "SELECT COUNT(record_id)
                FROM (
                    SELECT
                        vtiger_account.accountid AS record_id,
                        DATEDIFF(NOW(), MAX(IFNULL(activity_crmentity.modifiedtime, account_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                    LEFT JOIN vtiger_users ON (account_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_seactivityrel
                        INNER JOIN vtiger_activity ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid AND (activitytype = 'Emails' OR eventstatus IN ('Held', 'Completed') OR status IN ('Completed')))
                        INNER JOIN vtiger_crmentity AS activity_crmentity ON (vtiger_activity.activityid = activity_crmentity.crmid AND activity_crmentity.deleted = 0)) ON (vtiger_seactivityrel.crmid = vtiger_account.accountid
                    )
                    WHERE vtiger_account.accountid != '{$personalAccountId}' {$accountAclQuery}
                    GROUP BY vtiger_account.accountid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";
        }
        else {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');
            $contactAclQuery = CRMEntity::getListViewSecurityParameter('Contacts');

            $sql = "SELECT *
                FROM (
                    SELECT
                        vtiger_contactdetails.contactid AS record_id,
                        'Contacts' AS record_module,
                        {$contactFullNameField} AS record_name,
                        vtiger_contactdetails.email,
                        vtiger_contactdetails.mobile AS phone,
                        MAX(IFNULL(activity_crmentity.modifiedtime, '')) AS latest_date,
                        DATEDIFF(NOW(), MAX(IFNULL(activity_crmentity.modifiedtime, contact_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (vtiger_contactdetails.contactid = contact_crmentity.crmid AND contact_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                    LEFT JOIN vtiger_users ON (contact_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_seactivityrel
                        INNER JOIN vtiger_activity ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid AND (activitytype = 'Emails' OR eventstatus IN ('Held', 'Completed') OR status IN ('Completed')))
                        INNER JOIN vtiger_crmentity AS activity_crmentity ON (vtiger_activity.activityid = activity_crmentity.crmid AND activity_crmentity.deleted = 0)) ON (vtiger_seactivityrel.crmid = vtiger_contactdetails.contactid
                    )
                    WHERE vtiger_contactdetails.contacts_type = 'Customer' {$contactAclQuery}
                    GROUP BY vtiger_contactdetails.contactid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";

            $totalSql = "SELECT COUNT(record_id)
                FROM (
                    SELECT
                        vtiger_contactdetails.contactid AS record_id,
                        DATEDIFF(NOW(), MAX(IFNULL(activity_crmentity.modifiedtime, contact_crmentity.createdtime))) AS inactive_days
                    FROM vtiger_contactdetails
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (vtiger_contactdetails.contactid = contact_crmentity.crmid AND contact_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid)
                    LEFT JOIN vtiger_users ON (contact_crmentity.main_owner_id = vtiger_users.id)
                    LEFT JOIN (vtiger_seactivityrel
                        INNER JOIN vtiger_activity ON (vtiger_seactivityrel.activityid = vtiger_activity.activityid AND (activitytype = 'Emails' OR eventstatus IN ('Held', 'Completed') OR status IN ('Completed')))
                        INNER JOIN vtiger_crmentity AS activity_crmentity ON (vtiger_activity.activityid = activity_crmentity.crmid AND activity_crmentity.deleted = 0)) ON (vtiger_seactivityrel.crmid = vtiger_contactdetails.contactid
                    )
                    WHERE vtiger_contactdetails.contacts_type = 'Customer' {$contactAclQuery}
                    GROUP BY vtiger_contactdetails.contactid
                ) AS temp
                WHERE inactive_days > {$params['period_days']}
                ORDER BY inactive_days";
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