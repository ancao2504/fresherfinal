<?php

/*
    SourceSalesByProvinceReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.16
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class SourceSalesByProvinceReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SourceSalesByProvinceReportWidgetFilter.tpl';
    protected $reportObject = 'SOURCE_BY_PROVINCES';

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
      
        if (empty($params['source']) || empty($params['provinces'])) {
            return [];
        }

        $source = $params['source'];
        $provinces = $params['provinces'];
        $allProvinces = Reports_CustomReport_Helper::getAllProvinceValues(false, false, true, ['Accounts', 'Contacts']);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();

        if ($source == '1') {
            $sourceSql = "AND (vtiger_salesorder.leadsource = '' OR vtiger_salesorder.leadsource IS NULL)";
        }
        else {
            $sourceSql = "AND vtiger_salesorder.leadsource = '{$source}'";
        }

        if ($provinces == '0' || (is_array($provinces) && in_array('0', $provinces))) {
            $provinces = array_keys($allProvinces);
        }

        // Update label for no source        
        $allProvinces[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
    
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
        $sql = "SELECT IF(vtiger_salesorder.accountid  = '{$personalAccountId}', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS mailing_city, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            LEFT JOIN (
                vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
            ) ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid AND vtiger_salesorder.accountid = '{$personalAccountId}')
            INNER JOIN (
                vtiger_account
                INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
            ) ON (vtiger_salesorder.accountid = vtiger_account.accountid) 
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') {$sourceSql}
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY mailing_city";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            if (isset($data[$row['mailing_city']])) {
                $data[$row['mailing_city']]['sales'] = (float)$row['sales'];
                $data['all']['sales'] += (float)$row['sales'];
            }
        }

        if ($source == '1') {
            $sourceSql = "AND (vtiger_potential.leadsource = '' OR vtiger_potential.leadsource IS NULL)";
        }
        else {
            $sourceSql = "AND vtiger_potential.leadsource = '{$source}'";
        }

        // Get potential
        $sql = "SELECT IF(vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '', IF(mailingcity IS NULL OR mailingcity = '', '1', mailingcity),  IF(bill_city IS NULL OR bill_city = '', '1', bill_city)) AS mailing_city, SUM(vtiger_potential.amount) AS potential_sales
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
            WHERE 1=1 {$sourceSql} AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY mailingcity";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            if (isset($data[$row['mailing_city']])) {
                $data[$row['mailing_city']]['potential_sales'] = (float)$row['potential_sales'];            
                $data['all']['potential_sales'] += (float)$row['potential_sales'];
            }
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
    