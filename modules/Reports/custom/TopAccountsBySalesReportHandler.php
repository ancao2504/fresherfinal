<?php

/*
    TopAccountsBySalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopAccountsBySalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'ACCOUNT_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CUSTOMER_COMPANY', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['accountname']), (float)$row['sales']];
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
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();

        // Data for sales
        $sql = "SELECT 0 AS no, vtiger_account.accountid, vtiger_account.accountname, SUM(vtiger_salesorder.total) AS sales, COUNT(vtiger_salesorder.salesorderid) AS sales_count
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid AND vtiger_account.accountid != '{$personalAccountId}')
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_account.accountid
            ORDER BY sales DESC
            LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['sales'] = (float)$row['sales'];
            $row['sales_count'] = (int)$row['sales_count'];
        
            if ($forExport) {
                unset($row['accountid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}