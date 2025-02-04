<?php

/*
    CustomerConversionRateByDepartmentReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.14
*/

require_once('modules/Reports/custom/CustomerConversionRateByEmployeeReportHandler.php');

class CustomerConversionRateByDepartmentReportHandler extends CustomerConversionRateByEmployeeReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/CustomerConversionRateByDepartmentReportWidgetFilter.tpl';
    protected $reportObject = 'DEPARTMENT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('Phòng ban', 'Users') =>  '45%',
            vtranslate('Ticket', 'Reports') =>  '40%',
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;
        
        if (empty($params['departments'])) {
            return [];
        }
        
        // Get employees
        $departments = $params['departments'];
        $allDepartments = Reports_CustomReport_Helper::getAllDepartments();
        $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
        $employees = array_keys($employees);
        $departmentEmployees = Reports_CustomReport_Helper::getUsersGroupByDepartment($departments);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $prevPeriod = Reports_CustomReport_Helper::getPrevPeriodFromFilter($params, true);
        $employeeIds = implode("', '", $employees);        
        $employeeDepartment = [];


        foreach ($employees as $employee) {
            foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
                if (array_key_exists($employee, $departmentEmployee)) {
                    $employeeDepartment[$employee][] = $departmentId;
                }
            }
        }
      
        $data = [];
        $prevData = [];
        $no = 0;

       foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
            // For current period

            $data[$departmentId] = [
                'id' => (!$forExport ? $departmentId : ++$no),
                'name' => $allDepartments[$departmentId],
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
            $prevData[$departmentId] = [
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
                $data[$departmentId] = array_merge($data[$departmentId], [ 
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['lead'] += (int)$row['cur_lead_num'];
                $data['all']['lead'] += (int)$row['cur_lead_num'];
                $prevData[$departmentId]['lead'] += (int)$row['prev_lead_num'];
                $prevData['all']['lead'] += (int)$row['prev_lead_num'];
                
                $data[$departmentId]['converted_lead'] += (int)$row['cur_converted_lead_num'];
                $data['all']['converted_lead'] += (int)$row['cur_converted_lead_num'];
                $prevData[$departmentId]['converted_lead'] += (int)$row['prev_converted_lead_num'];
                $prevData['all']['converted_lead'] += (int)$row['prev_converted_lead_num'];
            }
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['potential'] += (int)$row['cur_potential_num'];
                $data['all']['potential'] += (int)$row['cur_potential_num'];
                $prevData[$departmentId]['potential'] += (int)$row['prev_potential_num'];
                $prevData['all']['potential'] += (int)$row['prev_potential_num'];
            }
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['closed_won_potential'] += (int)$row['cur_closed_won_potential_num'];
                $data['all']['closed_won_potential'] += (int)$row['cur_closed_won_potential_num'];
                $prevData[$departmentId]['closed_won_potential'] += (int)$row['prev_closed_won_potential_num'];
                $prevData['all']['closed_won_potential'] += (int)$row['prev_closed_won_potential_num'];
            }
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['avg_deal_days'] = $row['avg_deal_days'];
            }
        }

        // Get quote
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_quote_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_quote_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_quotes.quoteid
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE vtiger_quotes.quotestage NOT IN ('Created') AND vtiger_quotes.potentialid IN NOT NULL AND vtiger_quotes.potentialid <> '' AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp 
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['quote'] += (int)$row['cur_quote_num'];
                $data['all']['quote'] += (int)$row['cur_quote_num'];
                $prevData[$departmentId]['quote'] += (int)$row['prev_quote_num'];
                $prevData['all']['quote'] += (int)$row['prev_quote_num'];
            }
        } 
        
        // Get sales order
        $sql = "SELECT main_owner_id, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_salesorder_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_salesorder_num
            FROM (
                SELECT DISTINCT lead_crmentity.main_owner_id, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['salesorder'] += (int)$row['cur_salesorder_num'];
                $data['all']['salesorder'] += (int)$row['cur_salesorder_num'];
                $prevData[$departmentId]['salesorder'] += (int)$row['prev_salesorder_num'];
                $prevData['all']['salesorder'] += (int)$row['prev_salesorder_num'];
            }
        }

        // Get revenue
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
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['sales'] += (float)$row['cur_sales_num'];
                $data['all']['sales'] += (float)$row['cur_sales_num'];
                $prevData[$departmentId]['sales'] += (float)$row['prev_sales_num'];
                $prevData['all']['sales'] += (float)$row['prev_sales_num'];
            }
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
                    WHERE vtiger_cpreceipt.cpreceipt_status = 'completed' AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
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
                    WHERE vtiger_cpreceipt.cpreceipt_status = 'completed' AND lead_crmentity.main_owner_id IN ('{$employeeIds}') AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['revenue'] += (float)$row['cur_revenue_num'];
                $prevData[$departmentId]['revenue'] += (float)$row['cur_revenue_num'];
                $data['all']['revenue'] += (float)$row['cur_revenue_num'];
                $data['all']['revenue'] += (float)$row['cur_revenue_num'];
            }
        }

        // Calculate value percentage
        foreach ($data as $departmentId => $roleData) {
            $lead = $roleData['lead'];
            $convertedLead = $roleData['converted_lead'];
            $potential = $roleData['potential'];
            $quote = $roleData['quote'];
            $salesorder = $roleData['salesorder'];
            $closedWonPotential = $roleData['closed_won_potential'];
            
            if ($lead > 0) {
                $data[$departmentId]['lead_to_converted'] = round($convertedLead / $lead * 100);
                $data[$departmentId]['lead_to_potential'] = round($potential / $lead * 100);
                $data[$departmentId]['lead_to_quote'] = round($quote / $lead * 100);
                $data[$departmentId]['lead_to_closed_won_potential'] = round($closedWonPotential / $lead * 100);
                $data[$departmentId]['lead_to_salesorder'] = round($salesorder / $lead * 100);
            }

            if (!$forExport) {
                $prevConvertedLead = $prevData[$departmentId]['converted_lead'];
                $prevLead = $prevData[$departmentId]['lead'];
                $prevPotential = $prevData[$departmentId]['potential'];                
                $prevClosedWonPotential = $prevData[$departmentId]['closed_won_potential'];
                $prevQuote = $prevData[$departmentId]['quote'];
                $prevSalesorder = $prevData[$departmentId]['salesorder'];

                if ($prevLead > 0) {
                    $prevData[$departmentId]['lead_to_converted'] = round((($prevConvertedLead / $prevLead) - 1) * 100);
                    $prevData[$departmentId]['lead_to_potential'] = round((($prevPotential / $prevLead) - 1) * 100);
                    $prevData[$departmentId]['lead_to_quote'] = round((($prevQuote / $prevLead) - 1) * 100);
                    $prevData[$departmentId]['lead_to_closed_won_potential'] = round((($prevClosedWonPotential / $prevLead) - 1) * 100);
                    $prevData[$departmentId]['lead_to_salesorder'] = round((($prevSalesorder / $prevLead) - 1) * 100);
                }

                $data[$departmentId]['cp_lead_to_converted'] = $data[$departmentId]['lead_to_converted'] - $prevData[$departmentId]['lead_to_converted'];
                $data[$departmentId]['cp_lead_to_potential'] = $data[$departmentId]['lead_to_potential'] - $prevData[$departmentId]['lead_to_potential'];
                $data[$departmentId]['cp_lead_to_quote'] = $data[$departmentId]['lead_to_quote'] - $prevData[$departmentId]['lead_to_quote'];
                $data[$departmentId]['cp_lead_to_closed_won_potential'] = $data[$departmentId]['lead_to_closed_won_potential'] - $prevData[$departmentId]['lead_to_closed_won_potential'];
                $data[$departmentId]['cp_lead_to_salesorder'] = $data[$departmentId]['lead_to_salesorder'] - $prevData[$departmentId]['lead_to_salesorder'];
            }

            $data[$departmentId]['lead_to_converted'] = round($data[$departmentId]['lead_to_converted'], 0);
            $data[$departmentId]['lead_to_potential'] = round($data[$departmentId]['lead_to_potential'], 0);
            $data[$departmentId]['lead_to_quote'] = round($data[$departmentId]['lead_to_quote'], 0);
            $data[$departmentId]['lead_to_closed_won_potential'] = round($data[$departmentId]['lead_to_closed_won_potential'], 0);
            $data[$departmentId]['lead_to_salesorder'] = round($data[$departmentId]['lead_to_salesorder'], 0);

            if ($forExport) {
                if ($departmentId == 'all') {
                    $data[$departmentId]['lead_to_potential'] = '';
                    $data[$departmentId]['lead_to_quote'] = '';
                    $data[$departmentId]['lead_to_salesorder'] = '';
                    $data[$departmentId]['lead_to_closed_won_potential'] = '';
                    $data[$departmentId]['lead_to_salesorder'] = '';
                }
                else {
                    $data[$departmentId]['lead_to_potential'] .= '%';
                    $data[$departmentId]['lead_to_quote'] .= '%';
                    $data[$departmentId]['lead_to_salesorder'] .= '%';
                    $data[$departmentId]['lead_to_closed_won_potential'] .= '%';
                    $data[$departmentId]['lead_to_salesorder'] .= '%';
                }

                $data[$departmentId]['sales'] = [
                    'value' => $data[$departmentId]['sales'],
                    'type' => 'currency'
                ];
                
                $data[$departmentId]['revenue'] = [
                    'value' => $data[$departmentId]['revenue'],
                    'type' => 'currency'
                ];
            }
        }

        $data = array_values($data);

        return $data;
    }
}