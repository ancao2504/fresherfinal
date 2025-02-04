<?php

/**
 * Name: ReceiptsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_ReceiptsWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getDefaultParams() {
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
                'name' => 'bill_street',
                'label' => vtranslate('Billing Address', 'Accounts'),
            ],
            [
                'name' => 'assigned_user_id',
                'label' => vtranslate('LBL_ASSIGNED_TO', 'Accounts'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone', 'Accounts'),
            ],
            [
                'name' => 'total_amount',
                'label' => vtranslate('LBL_DASHBOARD_TOTAL_RECEIPT_AMOUNT', 'Home'),
                'type' => 'number',
            ],
            [
                'name' => 'paid_amount',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_PAID_AMOUNT', 'Home'),
                'type' => 'number',
            ],
            [
                'name' => 'amount',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_LEFT_AMOUNT', 'Home'),
                'type' => 'number',
            ],
            [
                'name' => 'overdue_amount',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_OVERDUE_AMOUNT', 'Home'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Accounts');

        $sql = "SELECT *
            FROM (
                SELECT
                    vtiger_crmentity.label AS record_name,
                    vtiger_crmentity.crmid AS record_id,
                    vtiger_crmentity.setype AS record_module,
                    vtiger_accountbillads.bill_street AS bill_street,
                    vtiger_crmentity.smownerid AS assigned_user_id,
                    vtiger_account.phone AS phone,
                    SUM(IFNULL(total_receipt.amount_vnd, 0)) AS total_amount,
                    SUM(IFNULL(paid_receipt.amount_vnd, 0)) AS paid_amount,
                    SUM(IFNULL(overdue_receipt.amount_vnd, 0)) AS overdue_amount,
                    vtiger_crmentity.createdtime,
                    vtiger_crmentity.modifiedtime
                FROM vtiger_account
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0)
                LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cpreceipt
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0 AND vtiger_cpreceipt.cpreceipt_status <> 'cancelled')
                    WHERE
                        DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cpreceipt.expiry_date IS NULL
                        OR vtiger_cpreceipt.expiry_date = ''
                ) AS total_receipt ON (total_receipt.account_id = vtiger_account.accountid)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cpreceipt
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0 AND vtiger_cpreceipt.cpreceipt_status = 'completed')
                    WHERE
                        DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cpreceipt.expiry_date IS NULL
                        OR vtiger_cpreceipt.expiry_date = ''
                ) AS paid_receipt ON (paid_receipt.account_id = vtiger_account.accountid)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cpreceipt
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0 AND vtiger_cpreceipt.cpreceipt_status = 'not_completed' AND DATE(expiry_date) <= DATE(NOW()))
                    WHERE
                        DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cpreceipt.expiry_date IS NULL
                        OR vtiger_cpreceipt.expiry_date = ''
                ) AS overdue_receipt ON (overdue_receipt.account_id = vtiger_account.accountid)
                WHERE 1 = 1 {$aclQuery}
                GROUP BY vtiger_crmentity.crmid
            ) AS temp
            WHERE total_amount > 0 AND total_amount > paid_amount
            ORDER BY modifiedtime DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT IFNULL(COUNT(crmid), 0)
            FROM (
                SELECT
                    vtiger_crmentity.crmid,
                    SUM(IFNULL(total_receipt.amount_vnd, 0)) AS total_amount,
                    SUM(IFNULL(paid_receipt.amount_vnd, 0)) AS paid_amount,
                    vtiger_crmentity.createdtime,
                    vtiger_crmentity.modifiedtime
                FROM vtiger_account
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_account.accountid AND vtiger_crmentity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0)
                LEFT JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cpreceipt
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0 AND vtiger_cpreceipt.cpreceipt_status <> 'cancelled')
                    WHERE
                        DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cpreceipt.expiry_date IS NULL
                        OR vtiger_cpreceipt.expiry_date = ''
                ) AS total_receipt ON (total_receipt.account_id = vtiger_account.accountid)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cpreceipt
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0 AND vtiger_cpreceipt.cpreceipt_status = 'completed')
                    WHERE
                        DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cpreceipt.expiry_date IS NULL
                        OR vtiger_cpreceipt.expiry_date = ''
                ) AS paid_receipt ON (paid_receipt.account_id = vtiger_account.accountid)
                WHERE 1 = 1 {$aclQuery}
                GROUP BY vtiger_crmentity.crmid
            ) AS temp
            WHERE total_amount > 0 AND total_amount > paid_amount";
        
        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql) ?? 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['amount'] = $row['total_amount'] - $row['paid_amount'];
            $row['bill_street'] = $this->getFieldDisplayValue($row['bill_street'], 'bill_street', 'Accounts');
            $row['assigned_user_id'] = $this->getFieldDisplayValue($row['assigned_user_id'], 'assigned_user_id', 'Accounts');
            $row['phone'] = $this->getFieldDisplayValue($row['phone'], 'phone', 'Accounts');
            $row['total_amount'] = $this->formatNumberToUser($row['total_amount']);
            $row['paid_amount'] = $this->formatNumberToUser($row['paid_amount']);
            $row['amount'] = $this->formatNumberToUser($row['amount']);
            $row['overdue_amount'] = $this->formatNumberToUser($row['overdue_amount']);

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