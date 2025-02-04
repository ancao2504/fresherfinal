<?php

/*
    ProvinceSalesByIndustryReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.16
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class ProvinceSalesByIndustryReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/ProvinceSalesByIndustryReportWidgetFilter.tpl';
    protected $reportObject = 'PROVINCE_BY_INDUSTRIES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_INDUSTRY', 'Reports') => '50%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '23%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') => '23%',
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        if (empty($params['province']) || empty($params['industries'])) {
            return [];
        }

        $province = $params['province'];
        $industries = $params['industries'];
        $allIndustries = Reports_CustomReport_Helper::getIndustryValues(false, false, true);
        $industriesPlusCondition = '';

        if ($province == '1') {
            $provinceSQL = "AND (bill_city = '' OR bill_city IS NULL)";
        }
        else {
            $provinceSQL = "AND bill_city = '{$province}'";
        }

        if ($industries == '0' || (is_array($industries) && in_array('0', $industries))) {
            $industries = array_keys($allIndustries);
        }

        // Update label for no industry        
        $allIndustries[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        
        // Replace no industry with empty value
        if (in_array('1', $industries)) {
            $industries[array_search('1', $industries)] = '';
            $industriesPlusCondition = " OR vtiger_account.industry = '' OR vtiger_account.industry IS NULL";
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $industryIds = implode("','", $industries);

        $data = [];
        $no = 0;

        foreach ($industries as $industryId) {
            $data[$industryId] = [
                'id' => (!$forExport ? $industryId : ++$no),
                'name' => $allIndustries[$industryId],
                'sales' => 0,
                'potential_sales' => 0
            ];
        }

        // For all data   
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports'); 
        
        // Get sales order
        $sql = "SELECT IF(vtiger_account.industry IS NULL OR vtiger_account.industry = '', '', vtiger_account.industry) AS industry, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE vtiger_account.accountid != '{$personalAccountId}' AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND (vtiger_account.industry IN ('$industryIds') {$industriesPlusCondition}) {$provinceSQL}
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['sales'] = (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT IF(vtiger_account.industry IS NULL OR vtiger_account.industry = '', '', vtiger_account.industry) AS industry, SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            INNER JOIN vtiger_account ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            WHERE vtiger_account.accountid != '{$personalAccountId}' AND (vtiger_account.industry IN ('$industryIds') {$industriesPlusCondition}) {$provinceSQL} AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['potential_sales'] = (float)$row['potential_sales'];            
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
    