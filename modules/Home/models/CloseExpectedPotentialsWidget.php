<?php

/**
 * CloseExpectedPotentialsWidget
 * Author: Phu Vo
 * Date: 2020.06.28
 */

class Home_CloseExpectedPotentialsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('Potential Name', 'Potentials'),
            ],
            [
                'name' => 'sales_stage',
                'label' => vtranslate('Sales Stage', 'Potentials'),
            ],
            [
                'name' => 'closingdate',
                'label' => vtranslate('Expected Close Date', 'Potentials'),
            ],
            [
                'name' => 'amount',
                'label' => vtranslate('Amount', 'Potentials'),
                'type' => 'number',
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];

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
            INNER JOIN vtiger_crmentity ON (vtiger_potential.potentialid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_potential.closingdate) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_potential.closingdate) <= DATE('{$periodInfo['to_date']}')
                AND DATE(closingdate) > DATE(NOW())
                AND potentialresult NOT IN ('Closed Won', 'Closed Lost')
                {$aclQuery}
            ORDER BY vtiger_potential.closingdate ASC, vtiger_potential.closingdate DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_potential.potentialid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_potential.closingdate) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_potential.closingdate) <= DATE('{$periodInfo['to_date']}')
                AND DATE(closingdate) > DATE(NOW())
                AND potentialresult NOT IN ('Closed Won', 'Closed Lost')
                {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['sales_stage'] = $this->getFieldDisplayValue($row['sales_stage'], 'sales_stage', 'Potentials');
            $row['closingdate'] = $this->getFieldDisplayValue($row['closingdate'], 'closingdate', 'Potentials');
            $row['amount'] = $this->formatNumberToUser($row['amount']);
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