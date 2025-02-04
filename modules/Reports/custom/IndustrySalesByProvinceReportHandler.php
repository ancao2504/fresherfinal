<?php

/*
    IndustrySalesByProvinceReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.16
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class IndustrySalesByProvinceReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/IndustrySalesByProvinceReportWidgetFilter.tpl';
    protected $reportObject = 'INDUSTRY_BY_PROVINCES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_PROVINCE', 'Reports') => '50%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '23%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') => '23%',
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;
      
        if (empty($params['industry']) || empty($params['provinces'])) {
            return [];
        }

        $industry = $params['industry'];
        $provinces = $params['provinces'];
        $allProvinces = Reports_CustomReport_Helper::getProvinceValues(false, false, true);
        $provincesPlusCondition = '';

        if ($industry == '1') {
            $industrySql = "AND (vtiger_account.industry = '' OR vtiger_account.industry IS NULL)";
        }
        else {
            $industrySql = "AND vtiger_account.industry = '{$industry}'";
        }

        if ($provinces == '0' || (is_array($provinces) && in_array('0', $provinces))) {
            $provinces = array_keys($allProvinces);
        }

        // Update label for no industry        
        $allProvinces[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        
        // Replace no industry with empty value
        if (in_array('1', $provinces)) {
            $provinces[array_search('1', $provinces)] = '';
            $provincesPlusCondition = " OR bill_city = '' OR bill_city IS NULL";
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;

        foreach ($provinces as $province) {
            $data[$province] = [
                'id' => (!$forExport ? $province : ++$no),
                'name' => $allProvinces[$province],
                'sales' => 0,
                'potential_sales' => 0
            ];
        }
        
        $provinces = implode("','", $provinces);

        // For all data   
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports'); 
        
        // Get sales order
        $sql = "SELECT IF(bill_city IS NULL OR bill_city = '', '', bill_city) AS bill_city, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND (bill_city IN ('$provinces') {$provincesPlusCondition}) {$industrySql}
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['bill_city']]['sales'] = (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT IF(bill_city IS NULL OR bill_city = '', '', bill_city) AS bill_city, SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            INNER JOIN vtiger_account ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE (bill_city IN ('$provinces') {$provincesPlusCondition}) {$industrySql} AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['bill_city']]['potential_sales'] = (float)$row['potential_sales'];            
            $data['all']['potential_sales'] += (float)$row['potential_sales'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];
                
                $data[$key]['potential_sales'] = [
                    'value' => $value['potential_sales'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }
}
    