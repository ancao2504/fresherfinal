<?php

/*
    TopServicesBySalesNumberReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.20
*/

require_once('modules/Reports/custom/TopProductsBySalesNumberReportHandler.php');

class TopServicesBySalesNumberReportHandler extends TopProductsBySalesNumberReportHandler {
    protected $targetModule = 'Services';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_SERVICE_CODE', 'Reports') =>  '',
            vtranslate('LBL_REPORT_SERVICE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '',
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        // Data for sale order
        $sql = "SELECT 0 AS no, vtiger_service.serviceid AS productid, vtiger_service.serviceid AS product_no, vtiger_service.servicename AS productname,
            SUM(vtiger_inventoryproductrel.quantity) AS number, 0 AS amount, SUM(vtiger_inventoryproductrel.margin) AS db_amount
            FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS first_crmentity ON (salesorderid = first_crmentity.crmid AND first_crmentity.deleted = 0)
                INNER JOIN vtiger_inventoryproductrel ON (vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_service ON (vtiger_inventoryproductrel.productid = vtiger_service.serviceid)
                INNER JOIN vtiger_crmentity AS service_crmentity ON (service_crmentity.crmid = vtiger_service.serviceid AND service_crmentity.deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled')";

        $sqlParams = [];
        
        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Update params for where
        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND first_crmentity.createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND first_crmentity.createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere} GROUP BY productid
            ORDER BY number DESC";

        if (isset($params['top']) && !empty($params['top'])) {
            $sql .= ' LIMIT ' . $params['top'];
        }
        else {
            $sql .= ' LIMIT 10';
        }

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['amount'] = CurrencyField::convertToUserFormat($row['db_amount'], null, true);
            $row['number'] = (int)$row['number'];

            if ($forExport) {
                unset($row['db_amount']);
                unset($row['productid']);
            }

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}