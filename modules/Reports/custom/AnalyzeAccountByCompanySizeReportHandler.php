<?php

/*
    AnalyzeAccountByCompanySizeReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.18
*/

require_once('modules/Reports/custom/SummarySalesByMarketReportHander.php');

class AnalyzeAccountByCompanySizeReportHandler extends SummarySalesByMarketReportHander {

    protected $targetReport = 'ANALYZE_ACCOUNT_BY_COMPANY_SIZE';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_COMPANY_SIZE', 'Reports') => '32%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '32%',
            vtranslate('LBL_REPORT_NUMBER_RATE', 'Reports') => '32%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];

        foreach ($reportData as $key => $row) {
            $data[] = [$row['accounts_company_size'], (float)$row['account_number']];      
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
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $data = [];
        $no = 1;
 
        // Get all
        $sql = "SELECT COUNT(accountid)
           FROM vtiger_account
           INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = accountid)
           WHERE accountid != '{$personalAccountId}' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'";

        $allAccountNumber = $adb->getOne($sql);

        // Get data
        $sql = "SELECT 0 AS no, accounts_company_size, COUNT(accountid) AS account_number
            FROM (
                SELECT IFNULL(vtiger_account.accounts_company_size, '') AS accounts_company_size, accountid, sortorderid
                FROM vtiger_account
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = accountid)
                LEFT JOIN vtiger_accounts_company_size ON (vtiger_account.accounts_company_size = vtiger_accounts_company_size.accounts_company_size)
                WHERE accountid != '{$personalAccountId}' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ) AS temp
            GROUP BY accounts_company_size
            ORDER BY sortorderid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $row['no'] = $no++;
            $row['account_number'] = (int)$row['account_number'];
            $row['ratio'] = (int)$row['account_number'] / (int)$allAccountNumber *  100;

            if (empty($row['accounts_company_size'])) {
                $row['accounts_company_size'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }
            else {
                $row['accounts_company_size'] = html_entity_decode(vtranslate($row['accounts_company_size'], 'Accounts'));
            }

            if ($forExport) {
                $row['ratio'] = formatNumberToUser($row['ratio'], 'float') . '%';
            }
            
            $data[] = $row;
        }

        return array_values($data);
    }
}