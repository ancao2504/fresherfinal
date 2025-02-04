<?php

/*
    PotentialBySalesStageReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.18
*/

require_once('modules/Reports/custom/TopEmployeesByPotentialSalesReportHandler.php');

class PotentialBySalesStageReportHandler extends TopEmployeesByPotentialSalesReportHandler {
    protected $targetModule = 'POTENTIAL_SALES_STAGE';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('Sales Stage', 'Potentials') =>  '49%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports'), vtranslate('LBL_REPORT_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['sales_stage']), (float)$row['potential_sales'], (float)$row['potential_number']];
            $links[] = '';
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    function getSummaryData($reportData) {
        return false;
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for potential
        $sql = "SELECT 0 AS no, vtiger_sales_stage.sales_stage, COUNT(potentialid) AS potential_number, SUM(amount) AS potential_sales
        FROM vtiger_sales_stage
        LEFT JOIN (
            vtiger_potential INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = potentialid)
        ) ON (vtiger_potential.sales_stage = vtiger_sales_stage.sales_stage AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
        GROUP BY vtiger_sales_stage.sales_stage
        ORDER BY vtiger_sales_stage.sortorderid";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $row['no'] = $no++;
            $row['potential_number'] = (int)$row['potential_number'];
            $row['potential_sales'] = (float)$row['potential_sales'];
            $row['sales_stage'] = vtranslate($row['sales_stage'], 'Potentials');

            $data[] = $row;
        }

        return $data;
    }
}