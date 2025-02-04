<?php

/**
 * Name: PlannedReceiptsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_PlannedReceiptsWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_CODE', 'CPReceipt'),
            ],
            [
                'name' => 'cpreceipt_category',
                'label' => vtranslate('LBL_CPRECEIPT_CATEGORY', 'CPReceipt'),
            ],
            [
                'name' => 'cpreceipt_subcategory',
                'label' => vtranslate('LBL_CPRECEIPT_SUBCATEGORY', 'CPReceipt'),
            ],
            [
                'name' => 'account_id',
                'label' => vtranslate('LBL_ACCOUNT', 'CPReceipt'),
            ],
            [
                'name' => 'main_owner_id',
                'label' => vtranslate('LBL_ASSIGNED_TO', 'CPReceipt'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone', 'Accounts'),
            ],
            [
                'name' => 'amount_vnd',
                'label' => vtranslate('LBL_AMOUNT_VND', 'CPReceipt'),
                'type' => 'number',
            ],
            [
                'name' => 'expiry_date',
                'label' => vtranslate('LBL_EXPIRY_DATE', 'CPReceipt'),
            ],
            [
                'name' => 'days_left',
                'label' => vtranslate('LBL_DASHBOARD_DAY_LEFT', 'Vtiger'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];

        $aclQuery = CRMEntity::getListViewSecurityParameter('CPReceipt');
        
        // Process filter
        $extraWhere = '';
        if (!empty($params['cpreceipt_category'])) $extraWhere .= " AND vtiger_cpreceipt.cpreceipt_category = '{$params['cpreceipt_category']}' ";
        if (!empty($params['cpreceipt_subcategory'])) $extraWhere .= " AND vtiger_cpreceipt.cpreceipt_subcategory = '{$params['cpreceipt_subcategory']}' ";
        if ($params['debit'] == 'over_due') $extraWhere .= ' AND vtiger_cpreceipt.expiry_date < DATE(NOW()) ';
        if ($params['debit'] == 'expected') $extraWhere .= ' AND vtiger_cpreceipt.expiry_date >= DATE(NOW()) ';

        $sql = "SELECT
                vtiger_cpreceipt.code AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_cpreceipt.cpreceipt_category AS cpreceipt_category,
                vtiger_cpreceipt.cpreceipt_subcategory AS cpreceipt_subcategory,
                vtiger_cpreceipt.account_id AS account_id,
                vtiger_crmentity.main_owner_id AS main_owner_id,
                account.phone AS phone,
                vtiger_cpreceipt.amount_vnd AS amount_vnd,
                vtiger_cpreceipt.expiry_date AS expiry_date,
                DATEDIFF(DATE(vtiger_cpreceipt.expiry_date), DATE(NOW())) AS days_left
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0)
            INNER JOIN (
                SELECT vtiger_account.*
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity ON (
                        vtiger_crmentity.crmid = vtiger_account.accountid
                        AND vtiger_crmentity.setype = 'Accounts'
                        AND vtiger_crmentity.deleted = 0
                    )
            ) AS account ON (account.accountid = vtiger_cpreceipt.account_id)
            WHERE
                vtiger_cpreceipt.cpreceipt_status = 'not_completed'
                {$extraWhere} {$aclQuery}
            ORDER BY vtiger_cpreceipt.expiry_date ASC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0)
            INNER JOIN (
                SELECT vtiger_account.*
                    FROM vtiger_account
                    INNER JOIN vtiger_crmentity ON (
                        vtiger_crmentity.crmid = vtiger_account.accountid
                        AND vtiger_crmentity.setype = 'Accounts'
                        AND vtiger_crmentity.deleted = 0
                    )
            ) AS account ON (account.accountid = vtiger_cpreceipt.account_id)
            WHERE
                vtiger_cpreceipt.cpreceipt_status = 'not_completed'
                {$extraWhere} {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['code'] = $this->getFieldDisplayValue($row['code'], 'code', 'CPReceipt');
            $row['cpreceipt_category'] = $this->getFieldDisplayValue($row['cpreceipt_category'], 'cpreceipt_category', 'CPReceipt');
            $row['cpreceipt_subcategory'] = $this->getFieldDisplayValue($row['cpreceipt_subcategory'], 'cpreceipt_subcategory', 'CPReceipt');
            $row['account_id'] = $this->getFieldDisplayValue($row['account_id'], 'account_id', 'CPReceipt');
            $row['main_owner_id'] = $this->getFieldDisplayValue($row['main_owner_id'], 'main_owner_id', 'CPReceipt');
            $row['phone'] = $this->getFieldDisplayValue($row['phone'], 'phone', 'Accounts');
            $row['amount_vnd'] = $this->getFieldDisplayValue($row['amount_vnd'], 'amount_vnd', 'CPReceipt');
            $row['expiry_date'] = $this->getFieldDisplayValue($row['expiry_date'], 'expiry_date', 'CPReceipt');

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