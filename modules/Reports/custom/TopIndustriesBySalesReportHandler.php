<?php

/*
    TopIndustriesBySalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopIndustriesBySalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'INDUSTRY_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_INDUSTRY', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['industry']), (float)$row['sales']];
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

        // Get sales order
        $sql = "SELECT vtiger_account.industry AS industry, SUM(vtiger_salesorder.total) AS sales, COUNT(vtiger_salesorder.salesorderid) AS number
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND vtiger_account.industry IS NOT NULL AND vtiger_account.industry != ''
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry
            ORDER BY sales DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['sales'] = (float)$row['sales'];
            $row['industry'] = vtranslate($row['industry']);

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}