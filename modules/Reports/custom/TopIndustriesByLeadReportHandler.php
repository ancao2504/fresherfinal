<?php

/*
    TopIndustriesByLeadReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopIndustriesByLeadReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'INDUSTRY_LEAD';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_INDUSTRY', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports') =>  '49%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['industry']), (float)$row['lead_number']];
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
        $sql = "SELECT 0 AS no, industry, COUNT(leadid) AS lead_number
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE industry IS NOT NULL AND industry != '' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry
            ORDER BY lead_number DESC
            LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['lead_number'] = (int)$row['lead_number'];
            $row['industry'] = vtranslate($row['industry']);

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}