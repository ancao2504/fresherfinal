<?php

/**
 * NewSalesOrdersWidget
 * Author: Phu Vo
 * Date: 2020.08.28
 */

class Home_NewSalesOrdersWidget_Model extends Home_BaseListCustomDashboard_Model {

    public function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'record_name',
                'label' => vtranslate('SalesOrder No', 'SalesOrder'),
            ],
            [
                'name' => 'sostatus',
                'label' => vtranslate('Status', 'SalesOrder'),
            ],
            [
                'name' => 'account',
                'label' => vtranslate('Account Name', 'SalesOrder'),
            ],
            [
                'name' => 'createdtime',
                'label' => vtranslate('Created Time', 'SalesOrder'),
            ],
            [
                'name' => 'total',
                'label' => vtranslate('Total', 'SalesOrder'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $total = 0;
        
        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_salesorder.salesorder_no AS record_name,
                vtiger_crmentity.setype AS record_module,
                vtiger_salesorder.sostatus,
                account_entity.label AS account,
                vtiger_crmentity.createdtime,
                vtiger_salesorder.total 
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_crmentity AS account_entity ON (account_entity.crmid = vtiger_salesorder.accountid AND account_entity.setype = 'Accounts' AND account_entity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
            ORDER BY vtiger_crmentity.createdtime DESC";

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_crmentity AS account_entity ON (account_entity.crmid = vtiger_salesorder.accountid AND account_entity.setype = 'Accounts' AND account_entity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['sostatus'] = $this->getFieldDisplayValue($row['sostatus'], 'sostatus', 'SalesOrder');
            $dateTimeUIType = new Vtiger_Datetime_UIType();
            $row['createdtime'] = $dateTimeUIType->getDisplayValue($row['createdtime']);
            $row['total'] = $this->formatNumberToUser($row['total']);
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