<?php

/*
    CustomerConversionRateByIndustryReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.14
*/

require_once('modules/Reports/custom/CustomerConversionRateByEmployeeReportHandler.php');

class CustomerConversionRateByIndustryReportHandler extends CustomerConversionRateByEmployeeReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/CustomerConversionRateByIndustryReportWidgetFilter.tpl';
    protected $reportObject = 'INDUSTRY';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
            'industries' => Reports_CustomReport_Helper::getIndustryValues(false, true),
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
        
        $viewer = new Vtiger_Viewer();
        $viewer->assign('PARAMS', $params);
        $viewer->assign('FILTER_META', $this->reportFilterMeta);

        return $viewer->fetch($this->reportFilterTemplate);
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        if (empty($params['industries'])) {
            return [];
        }

        // Get industries
        $industries = $params['industries'];
        $industriesPlusCondition = '';
        $allIndustries = Reports_CustomReport_Helper::getIndustryValues(false, false, true);

        if ($industries == '0' || (is_array($industries) && in_array('0', $industries))) {
            $industries = array_keys($allIndustries);
        }

        // Update label for no industry
        $allIndustries[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');

        // Replace no industry with empty value
        if (in_array('1', $industries)) {
            $industries[array_search('1', $industries)] = '';
            $industriesPlusCondition = " OR vtiger_leaddetails.industry = '' OR vtiger_leaddetails.industry IS NULL";
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $prevPeriod = Reports_CustomReport_Helper::getPrevPeriodFromFilter($params, true);
        $industryIds = implode("','", $industries);

        $data = [];
        $prevData = [];
        $no = 0;

       foreach ($industries as $industryId) {
            // For current period
            $data[$industryId] = [
                'id' => (!$forExport ? $industryId : ++$no),
                'name' => $allIndustries[$industryId],
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
            $prevData[$industryId] = [
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
                $data[$industryId] = array_merge($data[$industryId], [
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
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_lead_num,
                SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_lead_num,
                SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS cur_converted_lead_num,
                SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS prev_converted_lead_num
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND leadid = crmid)
            WHERE (industry IN ('$industryIds') {$industriesPlusCondition}) AND (createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['industry']]['lead'] = $row['cur_lead_num'];
            $data['all']['lead'] += $row['cur_lead_num'];
            $prevData[$row['industry']]['lead'] = $row['prev_lead_num'];
            $prevData['all']['lead'] += $row['prev_lead_num'];

            $data[$row['industry']]['converted_lead'] = $row['cur_converted_lead_num'];
            $data['all']['converted_lead'] += $row['cur_converted_lead_num'];
            $prevData[$row['industry']]['converted_lead'] = $row['prev_converted_lead_num'];
            $prevData['all']['converted_lead'] += $row['prev_converted_lead_num'];
        }

        // Get potential
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_potential_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_potential.potentialid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['potential'] = $row['cur_potential_num'];
            $data['all']['potential'] += $row['cur_potential_num'];
            $prevData[$row['industry']]['potential'] = $row['prev_potential_num'];
            $prevData['all']['potential'] += $row['prev_potential_num'];
        }

        // Get closed won potential
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_potential_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_potential.potentialid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                    AND vtiger_potential.potentialresult = 'Closed Won'
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['industry']]['closed_won_potential'] = $row['cur_potential_num'];
            $data['all']['closed_won_potential'] += $row['cur_potential_num'];
            $prevData[$row['industry']]['closed_won_potential'] = $row['prev_potential_num'];
            $prevData['all']['closed_won_potential'] += $row['prev_potential_num'];
        }

        // Get avg deal days
        $sql = "SELECT industry,  AVG(DATEDIFF(changedon, createdtime)) AS avg_deal_days
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, MIN(vtiger_modtracker_basic.changedon) AS changedon
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_basic.crmid = potentialid)
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_basic.id = vtiger_modtracker_detail.id AND fieldname = 'potentialresult' AND postvalue = 'Closed Won')
                WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                    AND vtiger_potential.potentialresult = 'Closed Won'
                GROUP BY industry, vtiger_leaddetails.leadid
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['industry']]['avg_deal_days'] = $row['avg_deal_days'];
        }

        // Get quote
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_quote_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_quote_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_quotes.quoteid
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['quote'] = $row['cur_quote_num'];
            $data['all']['quote'] += $row['cur_quote_num'];
            $prevData[$row['industry']]['quote'] = $row['prev_quote_num'];
            $prevData['all']['quote'] += $row['prev_quote_num'];
        }

        // Get sales order
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_salesorder_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_salesorder_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['salesorder'] = $row['cur_salesorder_num'];
            $data['all']['salesorder'] += $row['cur_salesorder_num'];
            $prevData[$row['industry']]['salesorder'] = $row['prev_salesorder_num'];
            $prevData['all']['salesorder'] += $row['prev_salesorder_num'];
        }

        // Get revenue
        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', total, 0)) AS cur_sales_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', total, 0)) AS prev_sales_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.industry, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid, vtiger_salesorder.total
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                    AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['sales'] = $row['cur_sales_num'];
            $data['all']['sales'] += $row['cur_sales_num'];
            $prevData[$row['industry']]['sales'] = $row['prev_sales_num'];
            $prevData['all']['sales'] += $row['prev_sales_num'];
        }

        $sql = "SELECT industry, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', amount_vnd, 0)) AS cur_revenue_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', amount_vnd, 0)) AS prev_revenue_num
            FROM (
                SELECT DISTINCT industry, salesorderid, cpreceiptid, amount_vnd, createdtime
                FROM (
                    SELECT vtiger_leaddetails.industry, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')

                    UNION ALL

                    SELECT vtiger_leaddetails.industry, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
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
                    WHERE (vtiger_leaddetails.industry IN ('$industryIds') {$industriesPlusCondition}) AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['revenue'] = $row['cur_revenue_num'];
            $prevData[$row['industry']]['revenue'] = $row['cur_revenue_num'];
            $data['all']['revenue'] += $row['cur_revenue_num'];
            $data['all']['revenue'] += $row['cur_revenue_num'];
        }

        // Calculate value percentage
        foreach ($data as $industryId => $industryData) {
            $lead = $industryData['lead'];
            $potential = $industryData['potential'];
            $quote = $industryData['quote'];
            $salesorder = $industryData['salesorder'];
            $closedWonPotential = $industryData['closed_won_potential'];
            $convertedLead = $industryData['converted_lead'];

            if ($lead > 0) {
                $data[$industryId]['lead_to_converted'] = round($convertedLead / $lead * 100);
                $data[$industryId]['lead_to_potential'] = round($potential / $lead * 100);
                $data[$industryId]['lead_to_quote'] = round($quote / $lead * 100);
                $data[$industryId]['lead_to_closed_won_potential'] = round($closedWonPotential / $lead * 100);
                $data[$industryId]['lead_to_salesorder'] = round($salesorder / $lead * 100);
            }

            if (!$forExport) {
                $prevLead = $prevData[$industryId]['lead'];
                $prevPotential = $prevData[$industryId]['potential'];
                $prevQuote = $prevData[$industryId]['quote'];
                $prevSalesorder = $prevData[$industryId]['salesorder'];
                $prevConvertedLead = $prevData[$industryId]['converted_lead'];
                $prevClosedWonPotential = $prevData[$industryId]['closed_won_potential'];

                if ($prevLead > 0) {
                    $prevData[$industryId]['lead_to_converted'] = round($prevConvertedLead / $prevLead * 100);
                    $prevData[$industryId]['lead_to_potential'] = round($prevPotential / $prevLead * 100);
                    $prevData[$industryId]['lead_to_quote'] = round($prevQuote / $prevLead * 100);
                    $prevData[$industryId]['lead_to_closed_won_potential'] = round($prevClosedWonPotential / $prevLead * 100);
                    $prevData[$industryId]['lead_to_salesorder'] = round($prevSalesorder / $prevLead * 100);
                }

                $data[$industryId]['cp_lead_to_converted'] = $data[$industryId]['cp_lead_to_converted'] - $prevData[$industryId]['cp_lead_to_converted'];
                $data[$industryId]['cp_lead_to_potential'] = $data[$industryId]['lead_to_potential'] - $prevData[$industryId]['lead_to_potential'];
                $data[$industryId]['cp_lead_to_quote'] = $data[$industryId]['lead_to_quote'] - $prevData[$industryId]['lead_to_quote'];
                $data[$industryId]['cp_lead_to_closed_won_potential'] = $data[$industryId]['lead_to_closed_won_potential'] - $prevData[$industryId]['lead_to_closed_won_potential'];
                $data[$industryId]['cp_lead_to_salesorder'] = $data[$industryId]['lead_to_salesorder'] - $prevData[$industryId]['lead_to_salesorder'];
            }

            $data[$industryId]['lead_to_converted'] = round($data[$industryId]['lead_to_converted'], 0);
            $data[$industryId]['lead_to_potential'] = round($data[$industryId]['lead_to_potential'], 0);
            $data[$industryId]['lead_to_quote'] = round($data[$industryId]['lead_to_quote'], 0);
            $data[$industryId]['lead_to_closed_won_potential'] = round($data[$industryId]['lead_to_closed_won_potential'], 0);
            $data[$industryId]['lead_to_salesorder'] = round($data[$industryId]['lead_to_salesorder'], 0);

            if ($forExport) {

                if ($industryId == 'all') {
                    $data[$industryId]['lead_to_potential'] = '';
                    $data[$industryId]['lead_to_quote'] = '';
                    $data[$industryId]['lead_to_salesorder'] = '';
                    $data[$industryId]['lead_to_closed_won_potential'] = '';
                    $data[$industryId]['lead_to_salesorder'] = '';
                }
                else {
                    $data[$industryId]['lead_to_potential'] .= '%';
                    $data[$industryId]['lead_to_quote'] .= '%';
                    $data[$industryId]['lead_to_salesorder'] .= '%';
                    $data[$industryId]['lead_to_closed_won_potential'] .= '%';
                    $data[$industryId]['lead_to_salesorder'] .= '%';
                }

                $data[$industryId]['sales'] = [
                    'value' => $data[$industryId]['sales'],
                    'type' => 'currency'
                ];

                $data[$industryId]['revenue'] = [
                    'value' => $data[$industryId]['revenue'],
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

        $viewer->display('modules/Reports/tpls/CustomerConversionRateByEmployeeReport/CustomerConversionRateByEmployeeReport.tpl');
    }
}