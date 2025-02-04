<?php

/*
    TopSourcesByWonPotentialSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopSourcesByWonPotentialSalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'SOURCE_POTENTIAL_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_LEAD_SOURCE', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['leadsource']), (float)$row['potential_sales']];
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
        $sql = "SELECT 0 as no, leadsource, SUM(amount) AS potential_sales, COUNT(potentialid) AS potential_number
            FROM vtiger_potential 
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = potentialid)
            INNER JOIN vtiger_users ON (main_owner_id = id)
            WHERE potentialresult = 'Closed Won' AND leadsource != '' AND leadsource IS NOT NULL
                AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY leadsource
            ORDER BY potential_sales DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['potential_sales'] = (float)$row['potential_sales'];
            $row['potential_number'] = (int)$row['potential_number'];
            $row['leadsource'] = vtranslate($row['leadsource']);

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}