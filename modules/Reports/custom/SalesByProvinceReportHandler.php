<?php

/*
    SalesByProvinceReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.15
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class SalesByProvinceReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesByProvinceReportWidgetFilter.tpl';
    protected $reportObject = 'PROVINCE';

    public function getReportData($params, $forExport = false){
        global $adb;
      
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $data = [];
        $no = 0;

        // For all data   
        $totalSales = 0;
        $totalPotentialSales = 0;
        
        // Get sales order
        $sql = "SELECT 0 AS no, IF (vtiger_salesorder.accountid  = '{$personalAccountId}', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS bill_city,
                SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
            )  ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY bill_city
            ORDER BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);            
            
            if ($row['bill_city'] === '1') {
                $row['bill_city'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }

            $data[$row['bill_city']]['id'] = ++$no; 
            $data[$row['bill_city']]['name'] = $row['bill_city'];
            $data[$row['bill_city']]['sales'] = (float)$row['sales'];
            $totalSales += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT 0 AS no, IF (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS bill_city,
                SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
            ) ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id AND (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = ''))
            LEFT JOIN (
                vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            ) ON (vtiger_potential.related_to = vtiger_account.accountid)    
            WHERE potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY bill_city
            ORDER BY bill_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            if ($row['bill_city'] === '1') {
                $row['bill_city'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }

            $data[$row['bill_city']]['id'] = ++$no;           
            $data[$row['bill_city']]['name'] = $row['bill_city'];
            $data[$row['bill_city']]['potential_sales'] = (float)$row['potential_sales'];            
            $totalPotentialSales += (float)$row['potential_sales'];
        }

        if (count($data) == 0) {
            return [];
        }

        $data['all'] = [
            'id' => ($forExport ? '' : 'all'),
            'name' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
            'sales' => $totalSales,
            'potential_sales' => $totalPotentialSales
        ];

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
    