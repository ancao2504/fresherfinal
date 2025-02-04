<?php

/**
 * Name: NewSaleContractsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_NewSaleContractsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('Contract No', 'ServiceContracts'),
            ],
            [
                'name' => 'contract_status',
                'label' => vtranslate('Status', 'ServiceContracts'),
            ],
            [
                'name' => 'sc_related_to',
                'label' => vtranslate('Related to', 'ServiceContracts'),
            ],
            [
                'name' => 'createdtime',
                'label' => vtranslate('Created Time', 'ServiceContracts'),
            ],
            [
                'name' => 'start_date',
                'label' => vtranslate('Start Date', 'ServiceContracts'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('ServiceContracts');

        $sql = "SELECT
                vtiger_servicecontracts.contract_no AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_servicecontracts.contract_status AS contract_status,
                vtiger_servicecontracts.sc_related_to AS sc_related_to,
                vtiger_crmentity.createdtime AS createdtime,
                vtiger_servicecontracts.start_date AS start_date
            FROM vtiger_servicecontracts
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid AND vtiger_crmentity.setype = 'ServiceContracts' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_servicecontracts.servicecontracts_type = 'sell' {$aclQuery}
            ORDER BY vtiger_crmentity.createdtime DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_servicecontracts
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid AND vtiger_crmentity.setype = 'ServiceContracts' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_servicecontracts.servicecontracts_type = 'sell' {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['contract_status'] = $this->getFieldDisplayValue($row['contract_status'], 'contract_status', 'ServiceContracts');
            $row['sc_related_to'] = $this->getFieldDisplayValue($row['sc_related_to'], 'sc_related_to', 'ServiceContracts');
            $row['createdtime'] = $this->getFieldDisplayValue($row['createdtime'], 'createdtime', 'ServiceContracts');
            $row['start_date'] = $this->getFieldDisplayValue($row['start_date'], 'start_date', 'ServiceContracts');
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