<?php

/*
    AnalyzeSuccessPotentialByTypeReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.18
*/

require_once('modules/Reports/custom/SummarySalesByMarketReportHander.php');

class AnalyzeSuccessPotentialByTypeReportHandler extends SummarySalesByMarketReportHander {

    protected $targetReport = 'ANALYZE_POTENTIAL_BY_TYPE';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('Loại khách hàng', 'Reports') => '32%',
            vtranslate('Giá trị cơ hội', 'Reports') =>  '32%',
            vtranslate('Tỷ lệ trên giá trị', 'Reports') => '32%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('Giá trị cơ hội', 'Reports')]];

        foreach ($reportData as $key => $row) {
            $data[] = [$row['potentialtype'], (float)$row['potential_amount']];      
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

        $sql = "SELECT SUM(amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE sales_stage = 'Closed Won' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'";

        $totalPotentialAmount = $adb->getOne($sql);

        $sql = "SELECT 0 AS no, potentialtype, SUM(amount) AS potential_amount
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE sales_stage = 'Closed Won' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY potentialtype
            ORDER BY potentialtype DESC";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $row['no'] = $no++;
            $row['potentialtype'] = !empty($row['potentialtype']) ? vtranslate($row['potentialtype'], 'Potentials') : vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            $row['ratio'] = (int)$row['potential_amount'] / (int)$totalPotentialAmount *  100;

            if ($forExport) {
                $row['ratio'] = formatNumberToUser($row['ratio'], 'float') . '%';
            }
            
            $data[] = $row;
        }

        return array_values($data);
    }
}