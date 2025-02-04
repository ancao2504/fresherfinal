<?php

/**
 * Name: DebitSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_DebitSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'receipt_total',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_TOTAL', 'Home'),
            ],
            [
                'name' => 'receipt_collected',
                'label' => vtranslate('LBL_DASHBOARD_COLLECTED', 'Home'),
            ],
            [
                'name' => 'receipt_left',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_LEFT', 'Home'),
            ],
            [
                'name' => 'receipt_overdue',
                'label' => vtranslate('LBL_DASHBOARD_RECEIPT_OVERDUE', 'Home'),
            ],
            [
                'name' => 'payment_total',
                'label' => vtranslate('LBL_DASHBOARD_PAYMENT_TOTAL', 'Home'),
            ],
            [
                'name' => 'payment_paid',
                'label' => vtranslate('LBL_DASHBOARD_PAYMENT_PAID', 'Home'),
            ],
            [
                'name' => 'payment_left',
                'label' => vtranslate('LBL_DASHBOARD_PAYMENT_LEFT', 'Home'),
            ],
            [
                'name' => 'payment_overdue',
                'label' => vtranslate('LBL_DASHBOARD_PAYMENT_OVERDUE', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $data['receipt_total'] = [];
        $data['receipt_collected'] = [];
        $data['receipt_left'] = [];
        $data['receipt_overdue'] = [];
        $data['payment_total'] = [];
        $data['payment_paid'] = [];
        $data['payment_left'] = [];
        $data['payment_overdue'] = [];

        // Receipt
        $sql = "SELECT SUM(vtiger_cpreceipt.amount_vnd)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid
                AND vtiger_crmentity.setype = 'CPReceipt'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cpreceipt.cpreceipt_status <> 'cancelled'
            )
            WHERE
                DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['receipt_total']['value'] = $adb->getOne($sql) ?? 0;

        $sql = "SELECT SUM(vtiger_cpreceipt.amount_vnd)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid
                AND vtiger_crmentity.setype = 'CPReceipt'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cpreceipt.cpreceipt_status = 'completed'
            )
            WHERE
                DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['receipt_collected']['value'] = $adb->getOne($sql) ?? 0;
        $data['receipt_left']['value'] = $data['receipt_total']['value'] - $data['receipt_collected']['value'];

        $sql = "SELECT SUM(vtiger_cpreceipt.amount_vnd)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid
                AND vtiger_crmentity.setype = 'CPReceipt'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cpreceipt.cpreceipt_status = 'not_completed'
            )
            WHERE
                DATE(vtiger_cpreceipt.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cpreceipt.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')
                AND DATE(vtiger_cpreceipt.expiry_date) < DATE(NOW())";

        $data['receipt_overdue']['value'] = $adb->getOne($sql) ?? 0;

        // Payment
        $sql = "SELECT SUM(vtiger_cppayment.amount_vnd)
            FROM vtiger_cppayment
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid
                AND vtiger_crmentity.setype = 'CPPayment'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cppayment.cppayment_status <> 'cancelled'
            )
            WHERE
                DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['payment_total']['value'] = $adb->getOne($sql) ?? 0;

        $sql = "SELECT SUM(vtiger_cppayment.amount_vnd)
            FROM vtiger_cppayment
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid
                AND vtiger_crmentity.setype = 'CPPayment'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cppayment.cppayment_status = 'completed'
            )
            WHERE
                DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['payment_paid']['value'] = $adb->getOne($sql) ?? 0;
        $data['payment_left']['value'] = $data['payment_total']['value'] - $data['payment_paid']['value'];

        $sql = "SELECT SUM(vtiger_cppayment.amount_vnd)
            FROM vtiger_cppayment
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid
                AND vtiger_crmentity.setype = 'CPPayment'
                AND vtiger_crmentity.deleted = 0
                AND vtiger_cppayment.cppayment_status = 'not_completed'
            )
            WHERE
                DATE(vtiger_cppayment.expiry_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cppayment.expiry_date) <= DATE('{$periodFilterInfo['to_date']}')
                AND DATE(vtiger_cppayment.expiry_date) < DATE(NOW())";

        $data['payment_overdue']['value'] = $adb->getOne($sql) ?? 0;

        // Format data
        $data['receipt_total']['value'] = $this->formatNumberToUser($data['receipt_total']['value']);
        $data['receipt_collected']['value'] = $this->formatNumberToUser($data['receipt_collected']['value']);
        $data['receipt_left']['value'] = $this->formatNumberToUser($data['receipt_left']['value']);
        $data['receipt_overdue']['value'] = $this->formatNumberToUser($data['receipt_overdue']['value']);
        $data['payment_total']['value'] = $this->formatNumberToUser($data['payment_total']['value']);
        $data['payment_paid']['value'] = $this->formatNumberToUser($data['payment_paid']['value']);
        $data['payment_left']['value'] = $this->formatNumberToUser($data['payment_left']['value']);
        $data['payment_overdue']['value'] = $this->formatNumberToUser($data['payment_overdue']['value']);

        return $data;
    }
}