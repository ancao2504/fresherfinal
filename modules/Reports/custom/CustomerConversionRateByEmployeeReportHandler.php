<?php

/*
    CustomerConversionRateByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.12
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class CustomerConversionRateByEmployeeReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/CustomerConversionRateByEmployeeReport/CustomerConversionRateByEmployeeReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/CustomerConversionRateByEmployeeReport/CustomerConversionRateByEmployeeReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/CustomerConversionRateByEmployeeReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/CustomerConversionRateByEmployeeReportDetail.js';
    protected $reportObject = 'EMPLOYEE';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], true, false),
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'input_validators' => [
                "from_date" => [
                    "mandatory" => false,
                    "presence" => true,
                    "quickcreate" => false,
                    "masseditable" => false,
                    "defaultvalue" => false,
                    "type" => "date",
                    "name" => "from_date",
                    "label" => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                "to_date" => [
                    "mandatory" => false,
                    "presence" => true,
                    "quickcreate" => false,
                    "masseditable" => false,
                    "defaultvalue" => false,
                    "type" => "date",
                    "name" => "to_date",
                    "label" => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_' . $this->reportObject, 'Reports') => '30%',
            vtranslate('LBL_REPORT_LEAD', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_CONVERTED_LEAD', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_CONVERTED_LEAD_RATIO', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_POTENTIAL', 'Reports') => '',
            vtranslate('LBL_REPORT_RATIO_LEAD_TO_POTENTIAL', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_QUOTE', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_RATIO_LEAD_TO_QUOTE', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_CLOSED_WON_POTENTIAL', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_RATIO_LEAD_TO_CLOSED_WON_POTENTIAL', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_AVERAGE_DAYS_FOR_WON_POTENTIAL', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_SALES_ORDER', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_RATIO_LEAD_TO_SALES_ORDER', 'Reports') =>  '7%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '9%',
            vtranslate('LBL_REPORT_REVENUE', 'Reports') =>  '9%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_REVENUE', 'Reports'), vtranslate('LBL_REPORT_RATIO_LEAD_TO_POTENTIAL', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            if ($row['id'] == 'all') {
                break;
            }

            $data[] = [html_entity_decode($row['name']), (float)$row['revenue'], (float)($row['lead_to_potential'])];
            $links[] = '';
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        if (empty($params['employees'])) {
            return [];
        }

        // Get employees
        $employees = $params['employees'];
        $departments = $params['departments'];

        if (in_array('0', $employees)) {
            if (in_array('', $departments)) {
                $departments = '';
            }

            $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
            $employees = array_keys($employees);
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $prevPeriod = Reports_CustomReport_Helper::getPrevPeriodFromFilter($params, true);
        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];
        $prevData = [];
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            // For current period
            $data[$row['id']] = [
                'id' => (!$forExport ? $row['id'] : ++$no),
                'name' => trim($row['user_full_name']),
                'lead' => 0,
                'converted_lead' => 0,
                'lead_to_converted' => 0,
                'potential' => 0,
                'lead_to_potential' => 0,
                'quote' => 0,
                'lead_to_quote' => 0,
                'closed_won_potential' => 0,
                'lead_to_closed_won_potential' => 0,
                'avg_deal_days' => '',
                'salesorder' => 0,
                'lead_to_salesorder' => 0,
                'sales' => 0,
                'revenue' => 0
            ];

            // For old period
            $prevData[$row['id']] = [
                'lead' => 0,
                'converted_lead' => 0,
                'potential' => 0,
                'lead_to_potential' => 0,
                'quote' => 0,
                'lead_to_quote' => 0,
                'closed_won_potential' => 0,
                'lead_to_closed_won_potential' => 0,
                'salesorder' => 0,
                'lead_to_salesorder' => 0,
            ];

            if (!$forExport) {
                $data[$row['id']] = array_merge($data[$row['id']], [
                    'lead_link' => '',
                    'potential_link' => '',
                    'quote_link' => '',
                    'salesorder_link' => '',
                    'cp_lead_to_potential' => 0,
                    'cp_lead_to_quote' => 0,
                    'cp_lead_to_salesorder' => 0,
                ]);
            }
        }

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');
        $prevData['all'] = current($prevData);
        $prevData['all']['id'] = (!$forExport ? 'all' : '');

        // Get leads
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_lead_num,
            SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_lead_num,
            SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS cur_converted_lead_num,
            SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS prev_converted_lead_num
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND leadid = crmid)
            WHERE main_owner_id IN ('{$employeeIds}') AND (createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['lead'] = $row['cur_lead_num'];
            $data['all']['lead'] += $row['cur_lead_num'];
            $prevData[$row['main_owner_id']]['lead'] = $row['prev_lead_num'];
            $prevData['all']['lead'] += $row['prev_lead_num'];

            $data[$row['main_owner_id']]['converted_lead'] = $row['cur_converted_lead_num'];
            $data['all']['converted_lead'] += $row['cur_converted_lead_num'];
            $prevData[$row['main_owner_id']]['converted_lead'] = $row['prev_converted_lead_num'];
            $prevData['all']['converted_lead'] += $row['prev_converted_lead_num'];
        }

        // Get potential
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_potential_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE lead_crmentity.main_owner_id IN ('{$employeeIds}') AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['potential'] = $row['cur_potential_num'];
            $data['all']['potential'] += $row['cur_potential_num'];
            $prevData[$row['main_owner_id']]['potential'] = $row['prev_potential_num'];
            $prevData['all']['potential'] += $row['prev_potential_num'];
        }

        // Get closed won potential
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_closed_won_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_closed_won_potential_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE lead_crmentity.main_owner_id IN ('{$employeeIds}') AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                    AND vtiger_potential.potentialresult = 'Closed Won'
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['closed_won_potential'] = $row['cur_closed_won_potential_num'];
            $data['all']['closed_won_potential'] += $row['cur_closed_won_potential_num'];
            $prevData[$row['main_owner_id']]['closed_won_potential'] = $row['prev_closed_won_potential_num'];
            $prevData['all']['closed_won_potential'] += $row['prev_closed_won_potential_num'];
        }

        // Get average deal days
        $sql = "SELECT main_owner_id, AVG(DATEDIFF(changedon, createdtime)) AS avg_deal_days
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid, MIN(vtiger_modtracker_basic.changedon) AS changedon
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_basic.crmid = potentialid)
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_basic.id = vtiger_modtracker_detail.id AND fieldname = 'potentialresult' AND postvalue = 'Closed Won')
                WHERE lead_crmentity.main_owner_id IN ('{$employeeIds}') AND lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                    AND vtiger_potential.potentialresult = 'Closed Won'
                GROUP BY main_owner_id, vtiger_leaddetails.leadid
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['avg_deal_days'] = $row['avg_deal_days'];
        }

        // Get quote
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_quote_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_quote_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE lead_crmentity.main_owner_id IN ('{$employeeIds}') AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['quote'] = $row['cur_quote_num'];
            $data['all']['quote'] += $row['cur_quote_num'];
            $prevData[$row['main_owner_id']]['quote'] = $row['prev_quote_num'];
            $prevData['all']['quote'] += $row['prev_quote_num'];
        }

        // Get sales order
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_salesorder_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_salesorder_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['salesorder'] = $row['cur_salesorder_num'];
            $data['all']['salesorder'] += $row['cur_salesorder_num'];
            $prevData[$row['main_owner_id']]['salesorder'] = $row['prev_salesorder_num'];
            $prevData['all']['salesorder'] += $row['prev_salesorder_num'];
        }

        // Get sales
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', total, 0)) AS cur_sales_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', total, 0)) AS prev_sales_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid, vtiger_salesorder.total
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE lead_crmentity.main_owner_id IN ('{$employeeIds}') AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                    AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['sales'] = $row['cur_sales_num'];
            $data['all']['sales'] += $row['cur_sales_num'];
            $prevData[$row['main_owner_id']]['sales'] = $row['prev_sales_num'];
            $prevData['all']['sales'] += $row['prev_sales_num'];
        }

        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', amount_vnd, 0)) AS cur_revenue_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', amount_vnd, 0)) AS prev_revenue_num
            FROM (
                SELECT DISTINCT main_owner_id, salesorderid, cpreceiptid, amount_vnd, createdtime
                FROM (
                    SELECT lead_crmentity.main_owner_id, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_status = 'completed' AND cpreceipt_category = 'sales' AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')

                    UNION ALL

                    SELECT lead_crmentity.main_owner_id, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE vtiger_cpreceipt.cpreceipt_status = 'completed' AND cpreceipt_category = 'sales' AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['revenue'] = $row['cur_revenue_num'];
            $prevData[$row['main_owner_id']]['revenue'] = $row['cur_revenue_num'];
            $data['all']['revenue'] += $row['cur_revenue_num'];
            $prevData['all']['revenue'] += $row['cur_revenue_num'];
        }

        // Calculate value percentage
        foreach ($data as $userId => $userData) {
            $lead = $userData['lead'];
            $convertedLead = $userData['converted_lead'];
            $potential = $userData['potential'];
            $closedWonPotential = $userData['closed_won_potential'];
            $quote = $userData['quote'];
            $salesorder = $userData['salesorder'];

            if ($lead > 0) {
                $data[$userId]['lead_to_converted'] = round($convertedLead / $lead * 100);
                $data[$userId]['lead_to_potential'] = round($potential / $lead * 100);
                $data[$userId]['lead_to_quote'] = round($quote / $lead * 100);
                $data[$userId]['lead_to_closed_won_potential'] = round($closedWonPotential / $lead * 100);
                $data[$userId]['lead_to_salesorder'] = round($salesorder / $lead * 100);
            }

            if (!$forExport) {
                $prevLead = $prevData[$userId]['lead'];
                $prevConvertedLead = $prevData[$userId]['converted_lead'];
                $prevClosedWonPotential = $prevData[$userId]['closed_won_potential'];
                $prevPotential = $prevData[$userId]['potential'];
                $prevQuote = $prevData[$userId]['quote'];
                $prevSalesorder = $prevData[$userId]['salesorder'];

                if ($prevLead > 0) {
                    $prevData[$userId]['lead_to_converted'] = round((($prevConvertedLead / $prevLead) - 1) * 100);
                    $prevData[$userId]['lead_to_potential'] = round((($prevPotential / $prevLead) - 1) * 100);
                    $prevData[$userId]['lead_to_quote'] = round((($prevQuote / $prevLead) - 1) * 100);
                    $prevData[$userId]['lead_to_closed_won_potential'] = round((($prevClosedWonPotential / $prevLead) - 1) * 100);
                    $prevData[$userId]['lead_to_salesorder'] = round((($prevSalesorder / $prevLead) - 1) * 100);
                }

                $data[$userId]['cp_lead_to_converted'] = $data[$userId]['lead_to_converted'] - $prevData[$userId]['lead_to_converted'];
                $data[$userId]['cp_lead_to_potential'] = $data[$userId]['lead_to_potential'] - $prevData[$userId]['lead_to_potential'];
                $data[$userId]['cp_lead_to_quote'] = $data[$userId]['lead_to_quote'] - $prevData[$userId]['lead_to_quote'];
                $data[$userId]['cp_lead_to_closed_won_potential'] = $data[$userId]['lead_to_closed_won_potential'] - $prevData[$userId]['lead_to_closed_won_potential'];
                $data[$userId]['cp_lead_to_salesorder'] = $data[$userId]['lead_to_salesorder'] - $prevData[$userId]['lead_to_salesorder'];
            }

            $data[$userId]['lead_to_converted'] = round($data[$userId]['lead_to_converted'], 0);
            $data[$userId]['lead_to_potential'] = round($data[$userId]['lead_to_potential'], 0);
            $data[$userId]['lead_to_quote'] = round($data[$userId]['lead_to_quote'], 0);
            $data[$userId]['lead_to_closed_won_potential'] = round($data[$userId]['lead_to_closed_won_potential'], 0);
            $data[$userId]['lead_to_salesorder'] = round($data[$userId]['lead_to_salesorder'], 0);

            if ($forExport) {

                if ($userId == 'all') {
                    $data[$userId]['lead_to_converted'] = '';
                    $data[$userId]['lead_to_potential'] = '';
                    $data[$userId]['lead_to_quote'] = '';
                    $data[$userId]['lead_to_closed_won_potential'] = '';
                    $data[$userId]['lead_to_salesorder'] = '';
                }
                else {
                    $data[$userId]['lead_to_converted'] .= '%';
                    $data[$userId]['lead_to_potential'] .= '%';
                    $data[$userId]['lead_to_quote'] .= '%';
                    $data[$userId]['lead_to_closed_won_potential'] .= '%';
                    $data[$userId]['lead_to_salesorder'] .= '%';
                }

                $data[$userId]['sales'] = [
                    'value' => $data[$userId]['sales'],
                    'type' => 'currency'
                ];

                $data[$userId]['revenue'] = [
                    'value' => $data[$userId]['revenue'],
                    'type' => 'currency'
                ];
            }
        }

        $data = array_values($data);

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);   

        $viewer->display('modules/Reports/tpls/CustomerConversionRateByEmployeeReport/CustomerConversionRateByEmployeeReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, false, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}