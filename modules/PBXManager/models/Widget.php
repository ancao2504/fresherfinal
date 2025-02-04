<?php

/*
    Widget Model
    Author: Hieu Nguyen
    Date: 2019-09-30
    Purpose: provide data for the widgets
*/

require_once('modules/Reports/ReportUtils.php');

class PBXManager_Widget_Model extends Vtiger_Base_Model {

    static $DEFAULT_DIRECTION = 'All';
    
    private static function getBaseSqlForCallsSummaryWidget() {
        $baseSql = "SELECT COUNT(p.pbxmanagerid) AS calls_count, ROUND(SUM(p.totalduration) / 60, 1) AS total_duration
            FROM vtiger_pbxmanager AS p
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = p.pbxmanagerid AND e.setype = 'PBXManager' AND e.deleted = 0)
            WHERE 1 = 1 ";

        return $baseSql;
    }

    public static function getDataForCallsSummaryTodayWidget($direction) {
        global $adb;

        $baseSql = self::getBaseSqlForCallsSummaryWidget();
        $params = [];

        if ($direction != self::$DEFAULT_DIRECTION) {
            $baseSql .= "AND p.direction = ? ";
            $params = [$direction];
        }

        // Get today data
        $todaySql = $baseSql . "AND DATE(p.starttime) = CURRENT_DATE";
        $result = $adb->pquery($todaySql, $params);
        $todayData = $adb->fetchByAssoc($result);
        $todayData['duration_per_call'] = empty($todayData['calls_count']) ? 0 : round($todayData['total_duration'] / $todayData['calls_count'], 1);

        // Get yesterday data
        $yesterdaySql = $baseSql . "AND DATE(p.starttime) = CURRENT_DATE - INTERVAL 1 DAY";
        $result = $adb->pquery($yesterdaySql, $params);
        $yesterdayData = $adb->fetchByAssoc($result);
        $yesterdayData['duration_per_call'] = empty($yesterdayData['calls_count']) ? 0 : round($yesterdayData['total_duration'] / $yesterdayData['calls_count'], 1);

        return ['today' => $todayData, 'yesterday' => $yesterdayData];
    }

    public static function getDataForCallsSummaryThisWeekWidget($direction) {
        global $adb;

        $baseSql = self::getBaseSqlForCallsSummaryWidget();
        $params = [];

        if ($direction != self::$DEFAULT_DIRECTION) {
            $baseSql .= "AND p.direction = ? ";
            $params = [$direction];
        }

        // Get this week data
        $thisWeekRange = getDateRange('this_week');
        $thisWeekSql = $baseSql . "AND DATE(p.starttime) BETWEEN '{$thisWeekRange['from']}' AND '{$thisWeekRange['to']}'";
        $result = $adb->pquery($thisWeekSql, $params);
        $thisWeekData = $adb->fetchByAssoc($result);
        $thisWeekData['duration_per_call'] = empty($thisWeekData['calls_count']) ? 0 : round($thisWeekData['total_duration'] / $thisWeekData['calls_count'], 1);

        // Get last week data
        $lastWeekRange = getDateRange('last_week');
        $lastWeekSql = $baseSql . "AND DATE(p.starttime) BETWEEN '{$lastWeekRange['from']}' AND '{$lastWeekRange['to']}'";
        $result = $adb->pquery($lastWeekSql, $params);
        $lastWeekData = $adb->fetchByAssoc($result);
        $lastWeekData['duration_per_call'] = empty($lastWeekData['calls_count']) ? 0 : round($lastWeekData['total_duration'] / $lastWeekData['calls_count'], 1);

        return ['this_week' => $thisWeekData, 'last_week' => $lastWeekData];
    }

    public static function getDataForCallsSummaryThisMonthWidget($direction) {
        global $adb;

        $baseSql = self::getBaseSqlForCallsSummaryWidget();
        $params = [];

        if ($direction != self::$DEFAULT_DIRECTION) {
            $baseSql .= "AND p.direction = ? ";
            $params = [$direction];
        }

        // Get this month data
        $thisMonthRange = getDateRange('this_month');
        $thisMonthSql = $baseSql . "AND DATE(p.starttime) BETWEEN '{$thisMonthRange['from']}' AND '{$thisMonthRange['to']}'";
        $result = $adb->pquery($thisMonthSql, $params);
        $thisMonthData = $adb->fetchByAssoc($result);
        $thisMonthData['duration_per_call'] = empty($thisMonthData['calls_count']) ? 0 : round($thisMonthData['total_duration'] / $thisMonthData['calls_count'], 1);

        // Get last month data
        $lastMonthRange = getDateRange('last_month');
        $lastMonthSql = $baseSql . "AND DATE(p.starttime) BETWEEN '{$lastMonthRange['from']}' AND '{$lastMonthRange['to']}'";
        $result = $adb->pquery($lastMonthSql, $params);
        $lastMonthData = $adb->fetchByAssoc($result);
        $lastMonthData['duration_per_call'] = empty($lastMonthData['calls_count']) ? 0 : round($lastMonthData['total_duration'] / $lastMonthData['calls_count'], 1);

        return ['this_month' => $thisMonthData, 'last_month' => $lastMonthData];
    }

    private static function getSqlForReportCallsPurposeWidget($direction, $conditionSql) {
        $callPurposeFieldName = 'events_call_purpose';
        if ($direction == 'Inbound') $callPurposeFieldName = 'events_inbound_call_purpose';

        $sql = "SELECT a.{$callPurposeFieldName} AS call_purpose, COUNT(a.activityid) AS calls_count, 
                ROUND(SUM(p.totalduration) / 60, 1) AS total_minutes 
            FROM vtiger_activity AS a
            INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = a.activityid AND ae.deleted = 0 AND ae.setype = 'Calendar') 
						INNER JOIN vtiger_pbxmanager AS p ON (p.sourceuuid = a.pbx_call_id)
            INNER JOIN vtiger_crmentity AS pe ON (pe.crmid = p.pbxmanagerid AND pe.deleted = 0 AND pe.setype = 'PBXManager')
            WHERE a.events_call_direction = '{$direction}' AND a.eventstatus = 'Held' {$conditionSql}
            GROUP BY a.{$callPurposeFieldName}";

        return $sql;
    }

    private static function getDataForReportCallsPurposeWidget($direction, $query) {
        global $adb;

        // Get temp calculation
        $result = $adb->pquery($query, []);
        $temp = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $temp[$row['call_purpose']] = $row;
        }

        if ($direction == 'Inbound') {
            $purposes = getAllPickListValues('events_inbound_call_purpose');
        }
        else {
            $purposes = getAllPickListValues('events_call_purpose');
        }
        
        $data = [];

        // Header row
        $data[] = [
            'Title', 
            vtranslate('LBL_WIDGET_NUMBER_OF_CALLS', 'PBXManager'), 
            vtranslate('LBL_WIDGET_NUMBER_OF_MINUTES', 'PBXManager')
        ];

        // Get data by each purpose
        foreach ($purposes as $purpose) {
            $data[] = [
                vtranslate($purpose, 'Events'),
                intval($temp[$purpose]['calls_count']),
                intval($temp[$purpose]['total_minutes'])
            ];
        }

        return $data;
    }

    public static function getDataForReportCallsPurposeTodayWidget($direction) {
        $conditionSql = "AND a.date_start = DATE_FORMAT(NOW(), '%Y-%m-%d')";
        $sql = self::getSqlForReportCallsPurposeWidget($direction, $conditionSql);
        $data = self::getDataForReportCallsPurposeWidget($direction, $sql);

        return $data;
    }

    public static function getDataForReportCallsPurposeThisWeekWidget($direction) {
        $thisWeekRange = getDateRange('this_week');
        $conditionSql = "AND a.date_start BETWEEN '{$thisWeekRange['from']}' AND '{$thisWeekRange['to']}'";
        $sql = self::getSqlForReportCallsPurposeWidget($direction, $conditionSql);
        $data = self::getDataForReportCallsPurposeWidget($direction, $sql);

        return $data;
    }

    public static function getDataForReportCallsPurposeThisMonthWidget($direction) {
        $thisMonthRange = getDateRange('this_month');
        $conditionSql = "AND a.date_start BETWEEN '{$thisMonthRange['from']}' AND '{$thisMonthRange['to']}'";
        $sql = self::getSqlForReportCallsPurposeWidget($direction, $conditionSql);
        $data = self::getDataForReportCallsPurposeWidget($direction, $sql);

        return $data;
    }

    private static function getDataForCompareCallsWidget($conditionSql, $direction, $dateRange, $rangeName) {
        global $adb;
        $groupField = "DATE(p.starttime)";
        $directionFilter = '';
        $params = [];

        if ($rangeName == 'this_year') {
            $groupField = 'MONTH(p.starttime)';
        }

        if ($direction != self::$DEFAULT_DIRECTION) {
            $directionFilter = "AND p.direction = ?";
            $params = [strtolower($direction)];
        }

        $sql = "SELECT {$groupField} AS group_key, COUNT(p.pbxmanagerid) AS calls_count, 
                ROUND(SUM(p.totalduration) / 60, 1) AS total_minutes 
            FROM vtiger_pbxmanager AS p
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = p.pbxmanagerid AND e.deleted = 0 AND e.setype = 'PBXManager') 
            WHERE 1 = 1 {$conditionSql} {$directionFilter}
            GROUP BY {$groupField}";

        // Get temp calculation
        $result = $adb->pquery($sql, $params);
        $temp = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $temp[$row['group_key']] = $row;
        }

        $date = $dateRange['from'];
        $data = [];

        // Header row
        $data[] = [
            'Title', 
            vtranslate('LBL_WIDGET_NUMBER_OF_CALLS', 'PBXManager'), 
            vtranslate('LBL_WIDGET_NUMBER_OF_MINUTES', 'PBXManager')
        ];

        // In year range, get data by each month in year
        if ($rangeName == 'this_year') {
            $dataKey = date('F', strtotime($date));

            for ($i = 1; $i <= 12; $i++) {
                $dataKey = vtranslate('LBL_MONTH_' . $i);

                $data[] = [
                    $dataKey,
                    intval($temp[$i]['calls_count']),
                    intval($temp[$i]['total_minutes'])
                ];
            }
        }
        // In month and week range, get data by each day in range
        else {
            while (true) {
                $dataKey = $date;

                if ($rangeName == 'this_week') {
                    $day = date('l', strtotime($date));
                    $dataKey = vtranslate($day, 'Users') ."\n". date('d/m', strtotime($date));
                }
                else if ($rangeName == 'this_month') {
                    $dataKey = date('d/m', strtotime($date));
                }

                $data[] = [
                    $dataKey,
                    intval($temp[$date]['calls_count']),
                    intval($temp[$date]['total_minutes'])
                ];

                if ($date == $dateRange['to']) break;
                $date = date('Y-m-d', strtotime($date . ' +1 days'));
            }
        }

        return $data;
    }

    public static function getDataForCompareCallsThisWeekWidget($direction) {
        $range = 'this_week';
        $thisWeekRange = getDateRange($range);
        $conditionSql = "AND DATE(p.starttime) BETWEEN '{$thisWeekRange['from']}' AND '{$thisWeekRange['to']}'";
        $data = self::getDataForCompareCallsWidget($conditionSql, $direction, $thisWeekRange, 'this_week');

        return $data;
    }

    public static function getDataForCompareCallsThisMonthWidget($direction) {
        $range = 'this_month';
        $thisMonthRange = getDateRange($range);
        $conditionSql = "AND DATE(p.starttime) BETWEEN '{$thisMonthRange['from']}' AND '{$thisMonthRange['to']}'";
        $data = self::getDataForCompareCallsWidget($conditionSql, $direction, $thisMonthRange, 'this_month');

        return $data;
    }

    public static function getDataForCompareCallsThisYearWidget($direction) {
        $range = 'this_year';
        $thisYearRange = getDateRange($range);
        $conditionSql = "AND DATE(p.starttime) BETWEEN '{$thisYearRange['from']}' AND '{$thisYearRange['to']}'";
        $data = self::getDataForCompareCallsWidget($conditionSql, $direction, $thisYearRange, $range);

        return $data;
    }

    public static function getDataForMissedCallsWidget() {
        global $adb, $current_user;

        $sql = "SELECT ce.label AS customer_name, p.customer AS customer_id, p.customertype AS customer_type, p.customernumber AS phone_number, 
                a.date_start, a.time_start, COUNT(a.activityid) AS missed_calls_count 
            FROM vtiger_activity AS a 
            INNER JOIN vtiger_crmentity AS ae ON (ae.crmid = a.activityid AND ae.deleted = 0 AND ae.setype = 'Calendar')
            INNER JOIN vtiger_pbxmanager AS p ON (p.sourceuuid = a.pbx_call_id)
            INNER JOIN vtiger_crmentity AS pe ON (pe.crmid = p.pbxmanagerid AND pe.deleted = 0 AND pe.setype = 'PBXManager')
            LEFT JOIN vtiger_crmentity AS ce ON (ce.crmid = p.customer AND ce.deleted = 0 AND ce.setype IN ('CPTarget', 'Leads', 'Contacts'))
            WHERE ae.main_owner_id = ? AND a.missed_call = 1 AND a.events_call_result != 'call_result_called_back'
            GROUP BY p.customernumber
            ORDER BY CONCAT(a.date_start, ' ', a.time_start)";
        $params = [$current_user->id];
        $result = $adb->pquery($sql, $params);
        $missedCalls = [];

        $eventModuleModel = Vtiger_Module_Model::getInstance('Events');
        $startDateField = $eventModuleModel->getField('date_start');
        $startTimeField = $eventModuleModel->getField('time_start');

        while ($row = $adb->fetchByAssoc($result)) {
            // Quick hack to make click-to-call work when the leading zero is missing
            if (substr($row['phone_number'], 0, 1) != '0') {
                $row['phone_number'] = '0' . $row['phone_number'];
            }

            $row['date_start'] = $startDateField->getDisplayValue($row['date_start']);
            $row['time_start'] = $startTimeField->getDisplayValue($row['time_start']);
            $missedCalls[] = decodeUTF8($row);
        }

        return $missedCalls;
    }

    public static function getDataForPlannedCallsWidget() {
        global $adb;
        $user = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator('Events', $user);
        $queryGenerator->setFields(['id', 'subject', 'contact_id', 'parent_id', 'date_start', 'time_start']);
		$query = $queryGenerator->getQuery();

        $extraSelect = 'customer.setype AS customer_type, customer.label AS customer_name';
        $query = addExtraSelectFields($query, $extraSelect, false);

        $extraJoin = "LEFT JOIN vtiger_crmentity AS customer ON (
                (customer.crmid = vtiger_cntactivityrel.contactid OR customer.crmid = vtiger_seactivityrel.crmid) AND customer.deleted = 0
            )";
        $query = addExtraJoinQuery($query, $extraJoin, false);
        $query .= " AND customer.setype IN ('Contacts', 'Leads', 'CPTarget')";

        // Modified by Phu Vo on 2020.07.27 to remove auto call from planned call list
        $query .= " AND vtiger_crmentity.main_owner_id = ?
                AND activitytype = 'Call'
                AND eventstatus = 'Planned'
                AND events_call_direction = 'Outbound'
                AND is_auto_call <> 1
            GROUP BY vtiger_activity.activityid
            ORDER BY CONCAT(date_start, ' ', time_start), vtiger_seactivityrel.crmid";
        // End Phu Vo

        $params = [$user->getId()];
        $result = $adb->pquery($query, $params);
        $plannedCalls = [];

        $eventModuleModel = Vtiger_Module_Model::getInstance('Events');
        $startDateField = $eventModuleModel->getField('date_start');
        $startTimeField = $eventModuleModel->getField('time_start');
        
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $customerId = !empty($row['contactid']) ? $row['contactid'] : $row['crmid'];
            $row['customer_name_with_link'] = '<a target="_blank" href="index.php?module='. $row['customer_type'] .'&view=Detail&record='. $customerId .'">'. $row['customer_name'] .'</a>';
            $row['customer_id'] = $customerId;
            $row['date_start'] = $startDateField->getDisplayValue($row['date_start']);
            $row['time_start'] = $startTimeField->getDisplayValue($row['time_start']);
            $row['phone_numbers'] = getCustomerPhoneNumbers($customerId);
            $row['highlight_color'] = self::getHighlightColorForPlannedCall($row['date_start']);
            $plannedCalls[] = decodeUTF8($row);
        }

        return $plannedCalls;
    }

    static function getHighlightColorForPlannedCall($startDate) {
        $today = strtotime(Date('Y-m-d'));
        $startDate = strtotime($startDate);

        if ($today > $startDate) {
            return 'overdue';
        }

        if ($today == $startDate) {
            return 'today';
        }

        return '';
    }
}