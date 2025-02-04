<?php

/*
    AnalyzeCampaignROIReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.19
*/

require_once('modules/Reports/custom/SalesByProductGroupReportHandler.php');

class AnalyzeCampaignROIReportHandler extends SalesByProductGroupReportHandler {

    protected $reportObject = 'CAMPAIGN_ROI';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '50%',            
            vtranslate('LBL_REPORT_ACTUAL_COST', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_ACTUAL_REVENUE', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_ACTUAL_ROI', 'Reports') =>  '15%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data[] = ['Element', vtranslate('LBL_REPORT_ACTUAL_COST', 'Reports'), vtranslate('LBL_REPORT_ACTUAL_REVENUE', 'Reports'), vtranslate('LBL_REPORT_ACTUAL_ROI', 'Reports')];

        foreach ($reportData as $key => $column) {
            $data[] = [$column['campaignname'], (float)$column['actualcost'], (float)$column['actual_revenue'], (float)$column['actualroi']];
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for sales
        $sql = "SELECT 0 AS no, campaignid, campaignname, actualcost, actual_revenue, actualroi
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND campaignid = crmid)
            WHERE createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['actualcost'] = (float)$row['actualcost'];
            $row['actual_revenue'] = (float)$row['actual_revenue'];
            $row['actualroi'] = (float)$row['actualroi'];
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }        

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['actualcost'] = [
                    'value' => $value['actualcost'],
                    'type' => 'currency'
                ];
                
                $data[$key]['actual_revenue'] = [
                    'value' => $value['actual_revenue'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }
}
    