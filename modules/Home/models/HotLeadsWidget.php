<?php

/**
 * HotLeadsWidget
 * Author: Phu Vo
 * Date: 2020.08.28
 */

class Home_HotLeadsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('LBL_FULL_NAME'),
            ],
            [
                'name' => 'leadstatus',
                'label' => vtranslate('Lead Status', 'Leads'),
            ],
            [
                'name' => 'email',
                'label' => vtranslate('Email', 'Leads'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $total = 0;

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Leads');

        $sql = "SELECT
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.setype AS record_module,
                vtiger_leaddetails.leadstatus,
                vtiger_leaddetails.email,
                MAX(IFNULL(modtracker.changedon, vtiger_crmentity.createdtime)) AS changeon
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0) 
            LEFT JOIN (
                SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
                WHERE vtiger_modtracker_detail.fieldname = 'rating'
                ORDER BY vtiger_modtracker_basic.changedon DESC
            ) AS modtracker ON (modtracker.crmid = vtiger_crmentity.crmid)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                AND vtiger_leaddetails.rating = 'Hot' {$aclQuery}
            GROUP BY vtiger_crmentity.crmid
            ORDER BY changeon DESC";

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}')
                AND vtiger_leaddetails.rating = 'Hot' {$aclQuery}";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['leadstatus'] = $this->getFieldDisplayValue($row['leadstatus'], 'leadstatus', 'Leads');
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