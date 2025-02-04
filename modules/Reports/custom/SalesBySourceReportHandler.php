<?php

/*
    SalesBySourceReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.15
*/

require_once('modules/Reports/custom/SalesByIndustryReportHandler.php');

class SalesBySourceReportHandler extends SalesByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesBySourceReportWidgetFilter.tpl';
    protected $reportObject = 'SOURCE';

    public function getReportData($params, $forExport = false){
        global $adb;
      
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;

        // For all data   
        $totalSales = 0;
        $totalPotentialSales = 0;
        
        // Get sales order
        $sql = "SELECT 0 AS no, IF(vtiger_salesorder.leadsource = '' OR vtiger_salesorder.leadsource iS NULL, '', vtiger_salesorder.leadsource) AS leadsource, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);            
            
            if (empty($row['leadsource'])) {
                $row['leadsource'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }

            $data[$row['leadsource']]['id'] = ++$no; 
            $data[$row['leadsource']]['name'] = vtranslate($row['leadsource'], 'Leads');
            $data[$row['leadsource']]['sales'] = (float)$row['sales'];
            $totalSales += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT 0 AS no, IF(leadsource = '' OR leadsource iS NULL, '', leadsource) AS leadsource, SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            WHERE potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY leadsource
            ORDER BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
             
            if (empty($row['leadsource'])) {
                $row['leadsource'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }

            $data[$row['leadsource']]['id'] = ++$no;           
            $data[$row['leadsource']]['name'] = vtranslate($row['leadsource'], 'Leads');
            $data[$row['leadsource']]['potential_sales'] = (float)$row['potential_sales'];            
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
    