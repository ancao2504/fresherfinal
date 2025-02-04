<?php

/*
    TopIndustriesByWonPotentialSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopIndustriesByWonPotentialSalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'INDUSTRY_POTENTIAL_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_INDUSTRY', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['industry']), (float)$row['potential_sales']];
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

        // Get potential
        $sql = "SELECT vtiger_account.industry AS industry, SUM(vtiger_potential.amount) AS potential_sales, COUNT(vtiger_potential.potentialid) AS potential_number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            INNER JOIN vtiger_account ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE vtiger_potential.potentialresult = 'Closed Won'
                AND (
                    vtiger_account.industry IS NOT NULL
                    OR vtiger_account.industry != ''
                )
                AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry
            HAVING industry <> ''
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
            $row['industry'] = vtranslate($row['industry']);

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}