<?php

/*
    TopEmployeesByConversionRatioReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/TopEmployeesByPotentialSalesReportHandler.php');

class TopEmployeesByConversionRatioReportHandler extends TopEmployeesByPotentialSalesReportHandler {
    protected $targetModule = 'CONVERSION_LEAD';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '40%',
            vtranslate('LBL_REPORT_LEAD_NUMBER', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_CONVERTED', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_CONVERTED_LEAD_RATIO', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '24%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_CONVERTED_LEAD_RATIO', 'Reports'), vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['user_full_name']), (float)$row['conversion_rate'], (float)$row['potential_sales']];
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
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for conversion rate
        $sql = "SELECT id, user_full_name, COUNT(leadid) AS lead_number, SUM(IF(potential_number > 0, 1, 0)) AS converted_lead_number,
                SUM(IF(potential_number > 0, 1, 0)) / COUNT(leadid) AS conversion_rate, SUM(amount) AS potential_sales
            FROM
            (
                SELECT lead_crmentity.main_owner_id AS id, {$fullNameField} as user_full_name, leadid, COUNT(DISTINCT vtiger_potential.potentialid) AS potential_number, SUM(vtiger_potential.amount) AS amount
                FROM vtiger_leaddetails
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.crmid = leadid AND lead_crmentity.deleted = 0)
                INNER JOIN vtiger_users ON (lead_crmentity.main_owner_id = id)
                LEFT JOIN vtiger_potential ON ( vtiger_potential.potentialid = vtiger_leaddetails.potential_converted_id AND vtiger_potential.isconvertedfromlead = 1)
                WHERE lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY id, leadid
            ) AS temp
            GROUP BY id
            HAVING converted_lead_number > 0
            ORDER BY conversion_rate DESC
            LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $data[] = [
                'id' => ($forExport ? $no++ : $row['id']),
                'user_full_name' => $row['user_full_name'],
                'lead_number' => (int)$row['lead_number'],
                'converted_lead_number' => (int)$row['converted_lead_number'],
                'conversion_rate' => (float)$row['conversion_rate'] * 100,
                'potential_sales' => $row['potential_sales'],
            ];
        }

        return $data;
    }
}