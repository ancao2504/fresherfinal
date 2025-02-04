<?php

/*
    TopEmployeesByOverdueReceiptReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopEmployeesByOverdueReceiptReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'USER_RECEIPT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '49%',
            vtranslate('Tổng công nợ', 'Reports') =>  '25%',
            vtranslate('Tổng giá trị công nợ', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('Tổng giá trị công nợ', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['user_full_name']), (float)$row['receipt_amount']];
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
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for receipt
        $sql = "SELECT 0 AS no, id, {$fullNameField} AS user_full_name, COUNT(cpreceiptid) AS receipt_number, SUM(amount_vnd) AS receipt_amount
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (crmid = cpreceiptid AND deleted = 0)
            INNER JOIN vtiger_users ON (main_owner_id = id)
            WHERE 
                DATE(vtiger_cpreceipt.expiry_date) < DATE(NOW())
                AND cpreceipt_status = 'not_completed'
                AND (expiry_date BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
            GROUP BY main_owner_id
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