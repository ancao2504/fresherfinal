<?php

/**
 * PlannedCallsWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class home_PlannedCallsWidget_Model extends Home_BaseListCustomDashboard_Model {

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
                'name' => 'events_call_direction',
                'label' => vtranslate('LBL_EVENTS_CALL_DIRECTION', 'Calendar'),
            ],
            [
                'name' => 'taskpriority',
                'label' => vtranslate('Priority', 'Calendar'),
            ],
            [
                'name' => 'time_start',
                'label' => vtranslate('Time Start', 'Calendar'),
            ],
            [
                'name' => 'time_end',
                'label' => vtranslate('End Time', 'Calendar'),
            ],
        ];

        return $widgetHeaders;
    }

    function getWidgetData($params) {
        global $adb;

        $data = [];

        $periodInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $aclQuery = CRMEntity::getListViewSecurityParameter('Calendar');

        $sql = "SELECT
                vtiger_crmentity.label AS record_name,
                vtiger_crmentity.crmid AS record_id,
                vtiger_crmentity.setype AS record_module,
                vtiger_activity.events_call_direction,
                vtiger_activity.priority AS taskpriority,
                CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) AS time_start,
                CONCAT(vtiger_activity.due_date, ' ', vtiger_activity.time_end) AS time_end 
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0) 
            WHERE
                CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) >= '{$periodInfo['from_date']}'
                AND CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) <= '{$periodInfo['to_date']}' 
                AND vtiger_activity.activitytype = 'Call' 
                AND vtiger_activity.eventstatus = 'Planned'
                AND CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) >= NOW()
                {$aclQuery}
            ORDER BY CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) ASC";

        if (!empty($params['length'])) {
            $sql .= " LIMIT {$params['length']}";
            if (!empty($params['start'])) $sql .= " OFFSET {$params['start']}";
        }
        
        $totalSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_activity.activityid AND vtiger_crmentity.setype = 'Calendar' AND vtiger_crmentity.deleted = 0) 
            WHERE
                CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) >= '{$periodInfo['from_date']}'
                AND CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) <= '{$periodInfo['to_date']}' 
                AND vtiger_activity.activitytype = 'Call' 
                AND vtiger_activity.eventstatus = 'Planned'
                AND CONCAT(vtiger_activity.date_start, ' ', vtiger_activity.time_start) >= NOW()
                {$aclQuery}";

        $result = $adb->pquery($sql);
        $total = $adb->getOne($totalSql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $dateTimeUIType = new Vtiger_Datetime_UIType();
            $row['time_start'] = $dateTimeUIType->getDisplayValue($row['time_start']);
            $row['time_end'] = $dateTimeUIType->getDisplayValue($row['time_end']);
            $row['events_call_direction'] = $this->getFieldDisplayValue($row['events_call_direction'], 'events_call_direction', 'Events');
            $row['taskpriority'] = $this->getFieldDisplayValue($row['taskpriority'], 'taskpriority', 'Events');
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