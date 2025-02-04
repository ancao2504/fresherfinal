<?php

/*
    TopCustomerByRevenueReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.20
*/

require_once('modules/Reports/custom/TopCustomerBySalesReportHandler.php');

class TopCustomerByRevenueReportHandler extends TopCustomerBySalesReportHandler {

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '',
            vtranslate('LBL_REPORT_CUSTOMER', 'Reports') => '50%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_QUOTE_NUMBER', 'Reports') => '',
            vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports') =>  '',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '',
        ];
    }
    
    protected function getChartData(array $params) {
        $data = parent::getChartData($params);
        
        if ($data !== false) $data['links'] = [];
        
        return $data;
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $personalAccount = Accounts_Data_Helper::getPersonalAccount();
        $customerType = $params['target'];        
        $extSelect = 'vtiger_account.accountname AS record_name, vtiger_account.accountid AS record_id';
        $extJoin = '';
        $extWhere = " AND vtiger_account.accountid != '{$personalAccountId}'";
        $extLimit = '';
        $data = [];
        $no = 1;

        if (isset($params['top']) && !empty($params['top'])) {
            $extLimit .= ' LIMIT ' . $params['top'];
        }
        else {
            $extLimit .= ' LIMIT 10';
        }

        if ($customerType == 'Contact') {
            $contactFullNameField = getSqlForNameInDisplayFormat(['firstname' => 'vtiger_contactdetails.firstname', 'lastname' => 'vtiger_contactdetails.lastname'], 'Contacts');
            $extSelect = "{$contactFullNameField} AS record_name, vtiger_contactdetails.contactid AS record_id";
            $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
            $extWhere = " AND vtiger_account.accountid = '{$personalAccountId}'";
        }

        // Data for sale order
        $sql = "SELECT '0' AS no, record_id, record_name, 0 AS potential_number, 0 AS quote_number,
                COUNT(DISTINCT salesorderid) AS saleorder_number, SUM(amount_vnd) AS amount
            FROM (
                SELECT DISTINCT record_id, record_name, salesorderid, cpreceiptid, amount_vnd
                FROM (
                    SELECT {$extSelect}, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    {$extJoin}
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed'
                        {$extWhere} AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')
            
                    UNION ALL
            
                    SELECT {$extSelect}, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
                    INNER JOIN vtiger_crmentity AS account_crmentity ON (vtiger_account.accountid = account_crmentity.crmid AND account_crmentity.deleted = 0)
                    {$extJoin}
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_category = 'sales' AND sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed'
                        {$extWhere} AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY record_id
            ORDER BY amount DESC, saleorder_number DESC
            {$extLimit}";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['amount'] = (float)$row['amount'];

            // Generate link for report for sales order
            if (!$forExport) {
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['account_id', 'c', $row['record_name']],
                        ['sostatus', 'n', 'Cancelled,Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['account_id', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['sostatus', 'n', 'Cancelled,Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['saleorder_link'] = getListViewLinkWithSearchParams('SalesOrder', $conditions);

                // Generate link for report for potential                
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['related_to', 'c', $row['record_name']],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['related_to', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['potential_link'] =  getListViewLinkWithSearchParams('Potentials', $conditions);

                // Generate link for report for quote             
                if ($customerType != 'Contact') {
                    $conditions = [[
                        ['account_id', 'c', $row['record_name']],
                        ['quotestage', 'n', 'Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }
                else {
                    $conditions = [[
                        ['account_id', 'c', decodeUTF8($personalAccount->get('accountname'))],
                        ['contact_id', 'c', $row['record_name']],
                        ['quotestage', 'n', 'Created'],
                        ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                    ]];
                }

                $row['quote_link'] =  getListViewLinkWithSearchParams('Quotes', $conditions);
            }

            $data[$row['record_id']] = $row;            
        }

        if ($forChart) {
            return array_values($data);
        }

        if (count($data)) {
            $customerIds = array_keys($data);
            $customerIds = implode("','", $customerIds);
            $extField = 'vtiger_potential.related_to';
            $extWhere = '';

            if ($customerType == 'Contact') {
                $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
                $extWhere = " AND (vtiger_potential.related_to = '{$personalAccountId}' OR vtiger_potential.related_to IS NULL OR vtiger_potential.related_to = '')";
                $extField = 'vtiger_potential.contact_id';
            }

            // Count potential
            $sql = "SELECT {$extField} AS record_id, COUNT(potentialid) AS potential_number
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potentialid = potential_crmentity.crmid AND potential_crmentity.deleted = 0)
                {$extJoin}
                WHERE {$extField} IN ('{$customerIds}') {$extWhere} AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                GROUP BY record_id";

            $result = $adb->pquery($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['record_id']]['potential_number'] = $row['potential_number'];
            }

            $extField = 'vtiger_quotes.accountid';
            $extWhere = '';

            if ($customerType == 'Contact') {
                $extJoin = "INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND vtiger_contactdetails.contactid = contact_crmentity.crmid)";
                $extWhere = " AND (vtiger_quotes.accountid = '{$personalAccountId}' OR vtiger_quotes.accountid IS NULL OR vtiger_quotes.accountid = '')";
                $extField = 'vtiger_quotes.contactid';
            }

            // Count quote
            $sql = "SELECT {$extField} AS record_id, COUNT(quoteid) AS quote_number
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (vtiger_quotes.quoteid = quote_crmentity.crmid AND quote_crmentity.deleted = 0)
                {$extJoin}
                WHERE vtiger_quotes.quotestage != 'Created' AND {$extField} IN ('{$customerIds}') {$extWhere}
                GROUP BY record_id";

            $result = $adb->pquery($sql);

            while ($row = $adb->fetchByAssoc($result)) {
                $data[$row['record_id']]['quote_number'] = $row['quote_number'];
            }
        }
        
        if ($forExport) {
            foreach ($data as $key => $value) {
                unset($data[$key]['record_id']);
                $data[$key]['amount'] = [
                    'value' => $value['amount'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }    
}