<?php

/**
 * OverdueTasksWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_OverdueTasksWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'label' => vtranslate('Subject', 'Calendar'),
            ],
            [
                'name' => 'time_end',
                'label' => vtranslate('End Time', 'Calendar'),
            ]
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb, $current_user;

        $data = [];

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Calendar');

        $sql = "SELECT
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_activity.due_date AS time_end
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                vtiger_activity.due_date >= DATE('{$periodInfo['from_date']}')
                AND vtiger_activity.due_date <= DATE('{$periodInfo['to_date']}')
                AND vtiger_activity.activitytype = 'Task'
                AND vtiger_activity.status IN ('Planned', 'In Progress', 'Pending Input')
                AND vtiger_activity.due_date < DATE(NOW())
                {$aclQuery}
            ORDER BY
                vtiger_activity.due_date DESC,
                CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) DESC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }

        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0)
            WHERE
                vtiger_activity.due_date >= DATE('{$periodInfo['from_date']}')
                AND vtiger_activity.due_date <= DATE('{$periodInfo['to_date']}')
                AND vtiger_activity.activitytype = 'Task'
                AND vtiger_activity.status IN ('Planned', 'In Progress', 'Pending Input')
                AND vtiger_activity.due_date < DATE(NOW())
                {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $timeEndDateTimeField = new DateTimeField($row['time_end']);
            $row['time_end'] = $timeEndDateTimeField->getDisplayDate($current_user);
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