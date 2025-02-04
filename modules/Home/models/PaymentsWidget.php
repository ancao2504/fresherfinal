<?php

/**
 * Name: PaymentsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_PaymentsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('LBL_VENDOR', 'CPPayment'),
            ],
            [
                'name' => 'street',
                'label' => vtranslate('Street', 'Vendors'),
            ],
            [
                'name' => 'assigned_user_id',
                'label' => vtranslate('LBL_ASSIGNED_TO', 'Vendors'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone', 'Vendors'),
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
        $aclQuery = CRMEntity::getListViewSecurityParameter('Vendors');

        $sql = "SELECT *
            FROM (
                SELECT
                    vtiger_crmentity.label AS record_name,
                    vtiger_crmentity.crmid AS record_id,
                    vtiger_crmentity.setype AS record_module,
                    vtiger_vendor.street AS street,
                    vtiger_crmentity.smownerid AS assigned_user_id,
                    vtiger_vendor.phone AS phone,
                    SUM(IFNULL(total_payment.amount_vnd, 0)) AS total_amount,
                    SUM(IFNULL(paid_payment.amount_vnd, 0)) AS paid_amount,
                    SUM(IFNULL(overdue_payment.amount_vnd, 0)) AS overdue_amount,
                    vtiger_crmentity.createdtime,
                    vtiger_crmentity.modifiedtime
                FROM vtiger_vendor
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_vendor.vendorid AND vtiger_crmentity.setype = 'Vendors' AND vtiger_crmentity.deleted = 0)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cppayment
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0 AND vtiger_cppayment.cppayment_status <> 'cancelled')
                    WHERE
                        DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cppayment.expiry_date IS NULL
                        OR vtiger_cppayment.expiry_date = ''
                ) AS total_payment ON (total_payment.vendor_id = vtiger_vendor.vendorid)
                LEFT JOIN (
                    SELECT *
                        FROM vtiger_cppayment
                        INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0 AND vtiger_cppayment.cppayment_status = 'completed')
                    WHERE
                        DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cppayment.expiry_date IS NULL
                        OR vtiger_cppayment.expiry_date = ''
                ) AS paid_payment ON (paid_payment.vendor_id = vtiger_vendor.vendorid)
                LEFT JOIN (
                    SELECT *
                        FROM vtiger_cppayment
                        INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0 AND vtiger_cppayment.cppayment_status = 'not_completed' AND DATE(expiry_date) <= DATE(NOW()))
                    WHERE
                        DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cppayment.expiry_date IS NULL
                        OR vtiger_cppayment.expiry_date = ''
                ) AS overdue_payment ON (overdue_payment.vendor_id = vtiger_vendor.vendorid)
                WHERE 1 = 1 {$aclQuery}
                GROUP BY vtiger_crmentity.crmid
            ) AS temp
            WHERE total_amount > 0 AND total_amount > paid_amount
            ORDER BY modifiedtime DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(crmid)
            FROM (
                SELECT
                    vtiger_crmentity.crmid,
                    SUM(IFNULL(total_payment.amount_vnd, 0)) AS total_amount,
                    SUM(IFNULL(paid_payment.amount_vnd, 0)) AS paid_amount,
                    vtiger_crmentity.createdtime,
                    vtiger_crmentity.modifiedtime
                FROM vtiger_vendor
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_vendor.vendorid AND vtiger_crmentity.setype = 'Vendors' AND vtiger_crmentity.deleted = 0)
                LEFT JOIN (
                    SELECT *
                    FROM vtiger_cppayment
                    INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0 AND vtiger_cppayment.cppayment_status <> 'cancelled')
                    WHERE
                        DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cppayment.expiry_date IS NULL
                        OR vtiger_cppayment.expiry_date = ''
                ) AS total_payment ON (total_payment.vendor_id = vtiger_vendor.vendorid)
                LEFT JOIN (
                    SELECT *
                        FROM vtiger_cppayment
                        INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0 AND vtiger_cppayment.cppayment_status = 'completed')
                    WHERE
                        DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodInfo['from_date']}')
                        AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodInfo['to_date']}')
                        OR vtiger_cppayment.expiry_date IS NULL
                        OR vtiger_cppayment.expiry_date = ''
                ) AS paid_payment ON (paid_payment.vendor_id = vtiger_vendor.vendorid)
                WHERE 1 = 1 {$aclQuery}
                GROUP BY vtiger_crmentity.crmid
            ) AS temp
            WHERE total_amount > 0 AND total_amount > paid_amount";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['amount'] = $row['total_amount'] - $row['paid_amount'];
            $row['street'] = $this->getFieldDisplayValue($row['street'], 'street', 'Vendors');
            $row['assigned_user_id'] = $this->getFieldDisplayValue($row['assigned_user_id'], 'assigned_user_id', 'Vendors');
            $row['phone'] = $this->getFieldDisplayValue($row['phone'], 'phone', 'Vendors');
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