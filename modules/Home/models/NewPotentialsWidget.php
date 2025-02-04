<?php

/**
 * NewPotentialsWidget
 * Author: Phu Vo
 * Date: 2020.08.28
 */

class Home_NewPotentialsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('Potential Name', 'Potentials'),
            ],
            [
                'name' => 'sales_stage',
                'label' => vtranslate('Sales Stage', 'Potentials'),
            ],
            [
                'name' => 'closingdate',
                'label' => vtranslate('Expected Close Date', 'Potentials'),
                'type' => 'number',
            ],
            [
                'name' => 'amount',
                'label' => vtranslate('Amount', 'Potentials'),
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
        $aclQuery = CRMEntity::getListViewSecurityParameter('Potentials');

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.setype AS record_module,
                vtiger_potential.sales_stage,
                vtiger_potential.closingdate,
                vtiger_potential.amount 
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                {$aclQuery}
            ORDER BY vtiger_crmentity.createdtime DESC";

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                {$aclQuery}";
        
        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['amount'] = $this->formatNumberToUser($row['amount']);
            $row['sales_stage'] = $this->getFieldDisplayValue($row['sales_stage'], 'sales_stage', 'Potentials');
            $row['closingdate'] = $this->getFieldDisplayValue($row['closingdate'], 'closingdate', 'Potentials');
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