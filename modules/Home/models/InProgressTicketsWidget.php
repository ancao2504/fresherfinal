<?php

/**
 * Name: InProgressTicketsWidget.php
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_InProgressTicketsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('Subject', 'HelpDesk'),
            ],
            [
                'name' => 'ticketpriorities',
                'label' => vtranslate('Priority', 'HelpDesk'),
            ],
            [
                'name' => 'ticketcategories',
                'label' => vtranslate('Category', 'HelpDesk'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];
        
        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('HelpDesk');

        $sql = "SELECT
                vtiger_troubletickets.title AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_troubletickets.priority AS ticketpriorities,
                vtiger_troubletickets.category AS ticketcategories,
                MAX(IFNULL(modtracker.changedon, vtiger_crmentity.createdtime)) AS changeon
            FROM vtiger_troubletickets
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_troubletickets.ticketid AND vtiger_crmentity.setype = 'HelpDesk' AND vtiger_crmentity.deleted = 0) 
            LEFT JOIN (
                SELECT vtiger_modtracker_basic.* FROM vtiger_modtracker_basic
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
                WHERE vtiger_modtracker_detail.fieldname = 'ticketstatus'
                ORDER BY vtiger_modtracker_basic.changedon DESC
            ) AS modtracker ON (modtracker.crmid = vtiger_crmentity.crmid)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_troubletickets.status = 'In Progress' {$aclQuery}
            GROUP BY vtiger_crmentity.crmid
            ORDER BY changeon DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_troubletickets
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_troubletickets.ticketid AND vtiger_crmentity.setype = 'HelpDesk' AND vtiger_crmentity.deleted = 0) 
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodInfo['to_date']}') 
                AND vtiger_troubletickets.status = 'In Progress' {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['ticketpriorities'] = $this->getFieldDisplayValue($row['ticketpriorities'], 'ticketpriorities', 'HelpDesk');
            $row['ticketcategories'] = $this->getFieldDisplayValue($row['ticketcategories'], 'ticketcategories', 'HelpDesk');
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