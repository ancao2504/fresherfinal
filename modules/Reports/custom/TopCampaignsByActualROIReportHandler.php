<?php

/*
    TopCampaignsByActualROIReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCampaignsByActualROIReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CAMPAIGN_ACTUAL_ROI';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_ACTUAL_ROI', 'Reports') =>  '49%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_ACTUAL_ROI', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['campaignname']), (float)$row['actualroi']];
            $links[] = '';
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for sales
        $sql = "SELECT 0 AS no, campaignid, campaignname, actualroi
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND campaignid = crmid)
            WHERE actualroi > 0 AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ORDER BY actualroi DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['actualroi'] = (float)$row['actualroi'];
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}