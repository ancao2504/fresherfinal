<?php

/*
    AnalyzeCallByResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.19
*/

require_once('modules/Reports/custom/SummarySalesByMarketReportHander.php');

class AnalyzeCallByResultReportHandler extends SummarySalesByMarketReportHander {

    protected $targetReport = 'ANALYZE_CALL_BY_RESULT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_EVENTS_CALL_RESULT', 'Events') => '32%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '32%',
            vtranslate('LBL_REPORT_NUMBER_RATE', 'Reports') => '32%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];

        foreach ($reportData as $key => $row) {
            if ($row['call_number'] == 0) continue;

            $data[] = [$row['events_call_result'], (float)$row['call_number']];      
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;
          
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 1;
 
        // Get all
        $sql = "SELECT COUNT(activityid)
            FROM  vtiger_events_call_result
            LEFT JOIN (
                vtiger_activity
                INNER JOIN  vtiger_crmentity ON (deleted = 0 AND crmid = activityid)
            ) ON (vtiger_activity.events_call_result = vtiger_events_call_result.events_call_result AND 
                activitytype = 'Call' AND eventstatus='Held' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')";

        $allCallNumber = $adb->getOne($sql);

        // Get data
        $sql = "SELECT 0 AS no, vtiger_events_call_result.events_call_result, COUNT(activityid) AS call_number
            FROM  vtiger_events_call_result
            LEFT JOIN (
                vtiger_activity
                INNER JOIN  vtiger_crmentity ON (deleted = 0 AND crmid = activityid)
            ) ON (vtiger_activity.events_call_result = vtiger_events_call_result.events_call_result AND 
                activitytype = 'Call' AND eventstatus='Held' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
            GROUP BY vtiger_events_call_result.events_call_result
            ORDER BY sortorderid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $row['no'] = $no++;
            $row['call_number'] = (int)$row['call_number'];
            $row['ratio'] = round((int)$row['call_number'] / (int)$allCallNumber *  100, 2);

            if (empty($row['events_call_result'])) {
                $row['events_call_result'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }
            else {
                $row['events_call_result'] = html_entity_decode(vtranslate($row['events_call_result'], 'Events'));
            }

            if ($forExport) {
                $row['ratio'] = formatNumberToUser($row['ratio'], 'float') . '%';
            }
            
            $data[] = $row;
        }

        return array_values($data);
    }
}