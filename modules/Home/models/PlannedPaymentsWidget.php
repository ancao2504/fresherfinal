<?php

/**
 * Name: PlannedPaymentsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_PlannedPaymentsWidget_Model extends Home_BaseListCustomDashboard_Model {

    function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('LBL_CODE', 'CPPayment'),
            ],
            [
                'name' => 'cppayment_category',
                'label' => vtranslate('LBL_CPPAYMENT_CATEGORY', 'CPPayment'),
            ],
            [
                'name' => 'cppayment_subcategory',
                'label' => vtranslate('LBL_CPPAYMENT_SUBCATEGORY', 'CPPayment'),
            ],
            [
                'name' => 'vendor_id',
                'label' => vtranslate('LBL_VENDOR', 'CPPayment'),
            ],
            [
                'name' => 'main_owner_id',
                'label' => vtranslate('LBL_ASSIGNED_TO', 'CPPayment'),
            ],
            [
                'name' => 'phone',
                'label' => vtranslate('Phone', 'Vendors'),
            ],
            [
                'name' => 'amount_vnd',
                'label' => vtranslate('LBL_AMOUNT_VND', 'CPPayment'),
                'type' => 'number',
            ],
            [
                'name' => 'expiry_date',
                'label' => vtranslate('LBL_EXPIRY_DATE', 'CPPayment'),
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

        $aclQuery = CRMEntity::getListViewSecurityParameter('CPPayment');

        // Process filter
        $extraWhere = '';

        if (!empty($params['cppayment_category'])) $extraWhere .= " AND vtiger_cppayment.cppayment_category = '{$params['cppayment_category']}' ";
        if (!empty($params['cppayment_subcategory'])) $extraWhere .= " AND vtiger_cppayment.cppayment_subcategory = '{$params['cppayment_subcategory']}' ";
        if ($params['debit'] == 'over_due') $extraWhere .= ' AND vtiger_cppayment.expiry_date < DATE(NOW()) ';
        if ($params['debit'] == 'expected') $extraWhere .= ' AND vtiger_cppayment.expiry_date >= DATE(NOW()) ';

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_cppayment.code AS record_name,
                vtiger_cppayment.cppayment_category AS cppayment_category,
                vtiger_cppayment.cppayment_subcategory AS cppayment_subcategory,
                vtiger_cppayment.vendor_id AS vendor_id,
                vtiger_crmentity.main_owner_id AS main_owner_id,
                vendor.phone AS phone,
                vtiger_cppayment.amount_vnd AS amount_vnd,
                vtiger_cppayment.expiry_date AS expiry_date,
                DATEDIFF(DATE(vtiger_cppayment.expiry_date), DATE(NOW())) AS days_left
            FROM vtiger_cppayment
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.CPPaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0)
                INNER JOIN (
                    SELECT vtiger_vendor.*
                    FROM vtiger_vendor
                    INNER JOIN vtiger_crmentity ON (
                        vtiger_crmentity.crmid = vtiger_vendor.vendorid
                        AND vtiger_crmentity.setype = 'Vendors'
                        AND vtiger_crmentity.deleted = 0
                    )
                ) AS vendor  ON (vendor.vendorid = vtiger_cppayment.vendor_id)
            WHERE
                vtiger_cppayment.cppayment_status = 'not_completed'
                {$extraWhere} {$aclQuery}
            ORDER BY vtiger_cppayment.expiry_date ASC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_cppayment
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cppayment.CPPaymentid AND vtiger_crmentity.setype = 'CPPayment' AND vtiger_crmentity.deleted = 0)
            INNER JOIN (
                SELECT vtiger_vendor.*
                FROM vtiger_vendor
                INNER JOIN vtiger_crmentity ON (
                    vtiger_crmentity.crmid = vtiger_vendor.vendorid
                    AND vtiger_crmentity.setype = 'Vendors'
                    AND vtiger_crmentity.deleted = 0
                )
            ) AS vendor ON (vendor.vendorid = vtiger_cppayment.vendor_id)
            WHERE
                vtiger_cppayment.cppayment_status = 'not_completed'
                {$extraWhere} {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['code'] = $this->getFieldDisplayValue($row['code'], 'code', 'CPPayment');
            $row['cppayment_category'] = $this->getFieldDisplayValue($row['cppayment_category'], 'cppayment_category', 'CPPayment');
            $row['cppayment_subcategory'] = $this->getFieldDisplayValue($row['cppayment_subcategory'], 'cppayment_subcategory', 'CPPayment');
            $row['vendor_id'] = $this->getFieldDisplayValue($row['vendor_id'], 'vendor_id', 'CPPayment');
            $row['main_owner_id'] = $this->getFieldDisplayValue($row['main_owner_id'], 'main_owner_id', 'CPPayment');
            $row['phone'] = $this->getFieldDisplayValue($row['phone'], 'phone', 'Vendors');
            $row['amount_vnd'] = $this->getFieldDisplayValue($row['amount_vnd'], 'amount_vnd', 'CPPayment');
            $row['expiry_date'] = $this->getFieldDisplayValue($row['expiry_date'], 'expiry_date', 'CPPayment');

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