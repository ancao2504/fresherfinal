<?php

/*
    AnalyzeOutboundCallByPurposeReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.18
*/

require_once('modules/Reports/custom/SummarySalesByMarketReportHander.php');

class AnalyzeOutboundCallByPurposeReportHandler extends SummarySalesByMarketReportHander {

    protected $targetReport = 'ANALYZE_CALL_BY_PURPOSE';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_EVENTS_CALL_PURPOSE', 'Events') => '32%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '32%',
            vtranslate('LBL_REPORT_NUMBER_RATE', 'Reports') => '32%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];

        foreach ($reportData as $key => $row) {
            if ($row['call_number'] == 0) continue;

            $data[] = [$row['events_call_purpose'], (float)$row['call_number']];      
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
            FROM  vtiger_events_call_purpose
            LEFT JOIN (
                vtiger_activity
                INNER JOIN  vtiger_crmentity ON (deleted = 0 AND crmid = activityid)
            ) ON (vtiger_activity.events_call_purpose = vtiger_events_call_purpose.events_call_purpose AND 
                activitytype = 'Call' AND events_call_direction = 'Outbound' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')";

        $allCallNumber = $adb->getOne($sql);

        // Get data
        $sql = "SELECT 0 AS no, vtiger_events_call_purpose.events_call_purpose, COUNT(activityid) AS call_number
            FROM  vtiger_events_call_purpose
            LEFT JOIN (
                vtiger_activity
                INNER JOIN  vtiger_crmentity ON (deleted = 0 AND crmid = activityid)
            ) ON (vtiger_activity.events_call_purpose = vtiger_events_call_purpose.events_call_purpose AND 
                activitytype = 'Call' AND events_call_direction = 'Outbound' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
            GROUP BY vtiger_events_call_purpose.events_call_purpose
            ORDER BY sortorderid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $row['no'] = $no++;
            $row['call_number'] = (int)$row['call_number'];
            $row['ratio'] = round((int)$row['call_number'] / (int)$allCallNumber *  100, 2);

            if (empty($row['events_call_purpose'])) {
                $row['events_call_purpose'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }
            else {
                $row['events_call_purpose'] = html_entity_decode(vtranslate($row['events_call_purpose'], 'Events'));
            }

            if ($forExport) {
                $row['ratio'] = formatNumberToUser($row['ratio'], 'float') . '%';
            }
            
            $data[] = $row;
        }

        return array_values($data);
    }
}