<?php
/*
    TopEmployeesByRevenueReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.20
*/

require_once('modules/Reports/custom/TopEmployeesBySalesReportHandler.php');

class TopEmployeesByRevenueReportHandler extends TopEmployeesBySalesReportHandler {
    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '',
        ];
    }

    protected function getChartData(array $params) {
        $data = parent::getChartData($params);
        
        if ($data !== false) $data['links'] = [];
        
        return $data;
    }

    protected function getReportData($params, $forExport = false) {
        global $fullNameConfig, $adb;

        $sqlParams = [];        
        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Update params for where
        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND vtiger_cpreceipt.paid_date >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND vtiger_cpreceipt.paid_date <= ?";
            $sqlParams[] = $period['to_date'];
        }

        // Get user from department
        if (!empty($params['department']) && $params['department'] != '0') {
            $employees = Reports_CustomReport_Helper::getUsersByDepartment($params['department']);
            $employees = array_keys($employees);
            
            if (!count($employees)) return [];

            $employees = implode("', '", $employees);
            $extWhere .= " AND salesorder_crmentity.main_owner_id IN ('{$employees}')";
        }

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for revenue
        $sql = "SELECT '0' AS no, user_full_name, COUNT(DISTINCT salesorderid) AS number, 0 AS amount, SUM(amount_vnd) AS db_amount, id
            FROM (
                SELECT DISTINCT id, user_full_name, salesorderid, cpreceiptid, amount_vnd
                FROM (
                    SELECT {$fullNameField} AS user_full_name, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_users.id, vtiger_cpreceipt.amount_vnd
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (vtiger_users.id = salesorder_crmentity.main_owner_id)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' {$extWhere}
            
                    UNION ALL
            
                    SELECT {$fullNameField} AS user_full_name, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_users.id, vtiger_cpreceipt.amount_vnd
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (vtiger_users.id = salesorder_crmentity.main_owner_id)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' {$extWhere}
                ) AS temp1
            ) AS temp2
            GROUP BY id
            ORDER BY db_amount DESC, number DESC";

        if (isset($params['top']) && !empty($params['top'])) {
            $sql .= ' LIMIT ' . $params['top'];
        }
        else {
            $sql .= ' LIMIT 10';
        }

        $result = $adb->pquery($sql, array_merge($sqlParams, $sqlParams));
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['amount'] = CurrencyField::convertToUserFormat($row['db_amount'], null, true);
            $row['number'] = (int)$row['number'];
            $row['user_full_name'] = trim($row['user_full_name']);

            if ($forExport) {
                unset($row['db_amount']);
                unset($row['id']);
            }
            else {
                // Generate link for report for sales order
                $conditions = [[
                    ['main_owner_id', 'c', $row['id']],
                    ['sostatus', 'n', 'Cancelled,Created'],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $row['saleorder_link'] = getListViewLinkWithSearchParams('SalesOrder', $conditions);
            }

            $data[] = $row;            
        }

        return $data;
    }
}