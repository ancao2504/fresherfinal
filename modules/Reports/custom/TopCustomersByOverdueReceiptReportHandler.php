<?php

/*
    TopCustomersByOverdueReceiptReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCustomersByOverdueReceiptReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CUSTOMER_RECEIPT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('Công ty', 'Reports') =>  '49%',
            vtranslate('Tổng công nợ', 'Reports') =>  '25%',
            vtranslate('Tổng giá trị công nợ', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('Tổng giá trị công nợ', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['account_name']), (float)$row['receipt_amount']];
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

        // Data for receipt
        $sql = "SELECT 0 AS no, account_entity.crmid AS id, account_entity.label AS account_name, COUNT(vtiger_cpreceipt.cpreceiptid) AS receipt_number, SUM(vtiger_cpreceipt.amount_vnd) AS receipt_amount
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_account ON (vtiger_cpreceipt.account_id = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_entity ON (account_entity.crmid = vtiger_account.accountid AND account_entity.deleted = 0)
            WHERE 
                DATE(vtiger_cpreceipt.expiry_date) < DATE(NOW())
                AND cpreceipt_status = 'not_completed'
                AND (expiry_date BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
            GROUP BY vtiger_cpreceipt.account_id
            HAVING receipt_amount > 0
            ORDER BY receipt_amount DESC
            LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['no'] = $no++;
            $row['receipt_number'] = (int)$row['receipt_number'];
            $row['receipt_amount'] = (float)$row['receipt_amount'];

            if ($forExport) {
                unset($row['id']);
            }
            
            $data[] = $row;
        }

        return $data;
    }
}