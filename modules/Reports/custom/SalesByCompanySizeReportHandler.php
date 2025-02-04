<?php

/*
    SalesByCompanySizeReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.08
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class SalesByCompanySizeReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesByCompanySizeReportWidgetFilter.tpl';
    protected $reportObject = 'COMPANY_SIZE';

    public function getReportData($params, $forExport = false){
        global $adb;
      
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $allCompanySize = Reports_CustomReport_Helper::getAllCompanySize(true, false);
        $allCompanySize[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        $data = [];
        $no = 0;

        foreach ($allCompanySize as $companySizeKey => $companySize) {
            // For current period
            $data[$companySizeKey] = [
                'id' => (!$forExport ? $companySizeKey : ++$no),
                'name' => $companySize,
                'sales' => 0,
                'potential_sales' => 0
            ];
        }
        
        // For all data   
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports'); 
        
        // Get sales order
        $sql = "SELECT IF(vtiger_account.accounts_company_size IS NULL OR vtiger_account.accounts_company_size = '', '', vtiger_account.accounts_company_size) AS company_size, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            LEFT JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY company_size";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['company_size']]['sales'] = (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT IF(vtiger_account.accounts_company_size IS NULL OR vtiger_account.accounts_company_size = '', '', vtiger_account.accounts_company_size) AS company_size, SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            LEFT JOIN vtiger_account ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY company_size";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['company_size']]['potential_sales'] = (float)$row['potential_sales'];            
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
    