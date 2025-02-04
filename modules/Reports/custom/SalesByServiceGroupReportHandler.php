<?php

/*
    SalesByServiceGroupReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.25
*/

require_once('modules/Reports/custom/SalesByProductGroupReportHandler.php');

class SalesByServiceGroupReportHandler extends SalesByProductGroupReportHandler {

    protected $reportObject = 'SERVICE';

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data[] = ['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), vtranslate('LBL_REPORT_QUOTE_SALES', 'Reports'), vtranslate('LBL_REPORT_SALES_NUMBER', 'Reports')];

        foreach ($reportData as $key => $column) {
            if ($key == count($reportData) - 1) break;
            
            $data[] = [$column['name'], (float)$column['sales'], (float)$column['quote_sales'], (int)$column['sales_number']];
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;
        
        $allServiceGroups = Vtiger_Util_Helper::getPickListValues('servicecategory');
        $allServiceGroups['undefined'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
        $servicesWithGroups = Reports_CustomReport_Helper::getGroupsOfServices();
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;
        
        // Generate first data
        foreach ($allServiceGroups as $groupId => $groupLabel) {
            $data[$groupLabel] = [
                'id' => ($forExport ? ++$no : $groupId),
                'name' => $groupLabel,
                'sales_number' => 0,
                'sales' => 0,
                'quote_sales' => 0,
            ];
        }

        $data['all'] = current($data);
        $data['all']['id'] = ($forExport ? '' : 'all');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Data for sale order
        $sql = "SELECT vtiger_service.serviceid, SUM(vtiger_inventoryproductrel.quantity) AS sales_number, SUM(vtiger_inventoryproductrel.margin + vtiger_inventoryproductrel.purchase_cost) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_service ON (vtiger_inventoryproductrel.productid = vtiger_service.serviceid)
            INNER JOIN vtiger_crmentity AS service_crmentity ON (service_crmentity.crmid = vtiger_service.serviceid AND service_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_service.serviceid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$servicesWithGroups[$row['serviceid']]]['sales_number'] = (int)$row['sales_number'];            
            $data['all']['sales_number'] += (int)$row['sales_number'];

            $data[$servicesWithGroups[$row['serviceid']]]['sales'] = (float)$row['sales'];            
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Data for quotes
        $sql = "SELECT vtiger_service.serviceid, SUM(vtiger_inventoryproductrel.quantity) AS sales_number, SUM(vtiger_inventoryproductrel.margin + vtiger_inventoryproductrel.purchase_cost) AS quote_sales
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity AS quote_crmentity ON (vtiger_quotes.quoteid = quote_crmentity.crmid AND quote_crmentity.deleted = 0)
            INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_quotes.quoteid)
            INNER JOIN vtiger_service ON (vtiger_inventoryproductrel.productid = vtiger_service.serviceid)
            INNER JOIN vtiger_crmentity AS service_crmentity ON (service_crmentity.crmid = vtiger_service.serviceid AND service_crmentity.deleted = 0)
            WHERE quotestage NOT IN ('Created') AND quote_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_service.serviceid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$servicesWithGroups[$row['serviceid']]]['quote_sales'] = (float)$row['quote_sales'];            
            $data['all']['quote_sales'] += (float)$row['quote_sales'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];
                
                $data[$key]['quote_sales'] = [
                    'value' => $value['quote_sales'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}
    