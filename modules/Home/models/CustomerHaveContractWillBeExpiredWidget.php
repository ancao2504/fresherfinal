<?php

/**
 * CustomerHaveContractWillBeExpiredWidget
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_CustomerHaveContractWillBeExpiredWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('Account Name', 'Accounts'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('Email', 'Accounts'),
            ],
            [
                'name' => 'address',
                'label' => vtranslate('LBL_DASHBOARD_ADDRESS'),
            ],
            [
                'name' => 'contract_no',
                'label' => vtranslate('Contract No', 'ServiceContracts'),
            ],
            [
                'name' => 'due_date',
                'label' => vtranslate('Due date', 'ServiceContracts'),
            ],
            [
                'name' => 'days_left',
                'label' => vtranslate('LBL_DASHBOARD_ACTIVE_DAY_LEFT'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Accounts');

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.setype AS record_module,
                vtiger_account.email1 AS email,
                vtiger_accountbillads.bill_street AS address,
                vtiger_servicecontracts.contract_no,
                vtiger_servicecontracts.due_date,
                DATEDIFF(DATE(vtiger_servicecontracts.due_date), DATE(NOW())) AS days_left
            FROM vtiger_account
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.setype = 'Accounts' && vtiger_crmentity.deleted = 0)
            INNER JOIN (
                vtiger_servicecontracts
                INNER JOIN vtiger_crmentity AS servicecontract_entity ON (servicecontract_entity.crmid = vtiger_servicecontracts.servicecontractsid AND servicecontract_entity.setype = 'ServiceContracts' AND servicecontract_entity.deleted = 0) 
            ) ON (vtiger_servicecontracts.sc_related_to = vtiger_account.accountid)
            LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE
                vtiger_account.accountid <> {$personalAccountId}
                AND vtiger_servicecontracts.due_date >= DATE('{$periodFilterInfo['from_date']}')
                AND vtiger_servicecontracts.due_date <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_servicecontracts.due_date >= DATE(NOW()) {$aclQuery}
            ORDER BY vtiger_servicecontracts.due_date ASC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid) 
            FROM vtiger_account
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.setype = 'Accounts' && vtiger_crmentity.deleted = 0)
            INNER JOIN (
                vtiger_servicecontracts
                INNER JOIN vtiger_crmentity AS servicecontract_entity ON (servicecontract_entity.crmid = vtiger_servicecontracts.servicecontractsid AND servicecontract_entity.setype = 'ServiceContracts' AND servicecontract_entity.deleted = 0) 
            ) ON (vtiger_servicecontracts.sc_related_to = vtiger_account.accountid)
            LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE
                vtiger_account.accountid <> {$personalAccountId}
                AND vtiger_servicecontracts.due_date >= DATE('{$periodFilterInfo['from_date']}')
                AND vtiger_servicecontracts.due_date <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_servicecontracts.due_date >= DATE(NOW()) {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $birthdayTimeField = new DateTimeField($row['due_date']);
            $row['due_date'] = $birthdayTimeField->getDisplayDate($current_user);

            // Handle long days
            $row['days_left'] = Reports_CustomReport_Helper::formatDayToLongDays($row['days_left']);

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