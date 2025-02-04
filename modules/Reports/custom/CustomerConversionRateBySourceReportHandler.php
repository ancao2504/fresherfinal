<?php

/*
    CustomerConversionRateBySourceReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.14
*/

require_once('modules/Reports/custom/CustomerConversionRateByEmployeeReportHandler.php');

class CustomerConversionRateBySourceReportHandler extends CustomerConversionRateByEmployeeReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/CustomerConversionRateBySourceReportWidgetFilter.tpl';
    protected $reportObject = 'SOURCE';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
            'sources'=> Reports_CustomReport_Helper::getSourceValues(false, true),
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

        if (empty($params['sources'])) {
            return [];
        }

        // Get sources
        $sources = $params['sources'];
        $sourcesPlusCondition = '';
        $allSources = Reports_CustomReport_Helper::getSourceValues(false, false, true);

        if ($sources == '0' || (is_array($sources) && in_array('0', $sources))) {
            $sources = array_keys($allSources);
        }

        // Update label for no source
        $allSources[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');

        // Replace no industry with empty value
        if (in_array('1', $sources)) {
            $sources[array_search('1', $sources)] = '';
            $sourcesPlusCondition = "OR vtiger_leaddetails.leadsource = '' OR vtiger_leaddetails.leadsource IS NULL";
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $prevPeriod = Reports_CustomReport_Helper::getPrevPeriodFromFilter($params, true);
        $sourceIds = implode("','", $sources);

        $data = [];
        $prevData = [];
        $no = 0;

       foreach ($sources as $sourceId) {
            // For current period
            $data[$sourceId] = [
                'id' => (!$forExport ? $sourceId : ++$no),
                'name' => $allSources[$sourceId],
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
            $prevData[$sourceId] = [
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
                $data[$sourceId] = array_merge($data[$sourceId], [
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
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_lead_num,
                SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_lead_num,
                SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS cur_converted_lead_num,
                SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}' AND vtiger_leaddetails.converted = 1, 1, 0)) AS prev_converted_lead_num
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND leadid = crmid)
            WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['leadsource']]['lead'] = $row['cur_lead_num'];
            $data['all']['lead'] += $row['cur_lead_num'];
            $prevData[$row['leadsource']]['lead'] = $row['prev_lead_num'];
            $prevData['all']['lead'] += $row['prev_lead_num'];

            $data[$row['leadsource']]['converted_lead'] = $row['cur_converted_lead_num'];
            $data['all']['converted_lead'] += $row['cur_converted_lead_num'];
            $prevData[$row['leadsource']]['converted_lead'] = $row['prev_converted_lead_num'];
            $prevData['all']['converted_lead'] += $row['prev_converted_lead_num'];
        }

        // Get potential
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_potential_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_potential.potentialid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['leadsource']]['potential'] = $row['cur_potential_num'];
            $data['all']['potential'] += $row['cur_potential_num'];
            $prevData[$row['leadsource']]['potential'] = $row['prev_potential_num'];
            $prevData['all']['potential'] += $row['prev_potential_num'];
        }

        // Get closed won potential
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_potential_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_potential_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_potential.potentialid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                    AND vtiger_potential.potentialresult = 'Closed Won'
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['leadsource']]['closed_won_potential'] = $row['cur_potential_num'];
            $data['all']['closed_won_potential'] += $row['cur_potential_num'];
            $prevData[$row['leadsource']]['closed_won_potential'] = $row['prev_potential_num'];
            $prevData['all']['closed_won_potential'] += $row['prev_potential_num'];
        }

        // Get avg deal days
        $sql = "SELECT leadsource,  AVG(DATEDIFF(changedon, createdtime)) AS avg_deal_days
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, MIN(vtiger_modtracker_basic.changedon) AS changedon
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_basic.crmid = potentialid)
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_basic.id = vtiger_modtracker_detail.id AND fieldname = 'potentialresult' AND postvalue = 'Closed Won')
                WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                    AND vtiger_potential.potentialresult = 'Closed Won'
                GROUP BY leadsource, vtiger_leaddetails.leadid
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['leadsource']]['avg_deal_days'] = $row['avg_deal_days'];
        }

        // Get quote
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_quote_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_quote_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_quotes.quoteid
                FROM vtiger_quotes
                INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['leadsource']]['quote'] = $row['cur_quote_num'];
            $data['all']['quote'] += $row['cur_quote_num'];
            $prevData[$row['leadsource']]['quote'] = $row['prev_quote_num'];
            $prevData['all']['quote'] += $row['prev_quote_num'];
        }

        // Get sales order
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', 1, 0)) AS cur_salesorder_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', 1, 0)) AS prev_salesorder_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['leadsource']]['salesorder'] = $row['cur_salesorder_num'];
            $data['all']['salesorder'] += $row['cur_salesorder_num'];
            $prevData[$row['leadsource']]['salesorder'] = $row['prev_salesorder_num'];
            $prevData['all']['salesorder'] += $row['prev_salesorder_num'];
        }

        // Get revenue
        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', total, 0)) AS cur_sales_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', total, 0)) AS prev_sales_num
            FROM (
                SELECT DISTINCT vtiger_leaddetails.leadsource, lead_crmentity.createdtime, vtiger_leaddetails.leadid, vtiger_salesorder.salesorderid, vtiger_salesorder.total
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                    AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
            ) AS temp
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['leadsource']]['sales'] = $row['cur_sales_num'];
            $data['all']['sales'] += $row['cur_sales_num'];
            $prevData[$row['leadsource']]['sales'] = $row['prev_sales_num'];
            $prevData['all']['sales'] += $row['prev_sales_num'];
        }

        $sql = "SELECT leadsource, SUM(IF(createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}', amount_vnd, 0)) AS cur_revenue_num, SUM(IF(createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}', amount_vnd, 0)) AS prev_revenue_num
            FROM (
                SELECT DISTINCT leadsource, salesorderid, cpreceiptid, amount_vnd, createdtime
                FROM (
                    SELECT vtiger_leaddetails.leadsource, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
                    INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
                    INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')

                    UNION ALL

                    SELECT vtiger_leaddetails.leadsource, vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, lead_crmentity.createdtime
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
                    WHERE (vtiger_leaddetails.leadsource IN ('$sourceIds') {$sourcesPlusCondition}) AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled')
                        AND (lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' OR lead_crmentity.createdtime BETWEEN '{$prevPeriod['from_date']}' AND '{$prevPeriod['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY leadsource";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['leadsource']]['revenue'] = $row['cur_revenue_num'];
            $prevData[$row['leadsource']]['revenue'] = $row['cur_revenue_num'];
            $data['all']['revenue'] += $row['cur_revenue_num'];
            $data['all']['revenue'] += $row['cur_revenue_num'];
        }

        // Calculate value percentage
        foreach ($data as $sourceId => $sourceData) {
            $lead = $sourceData['lead'];
            $potential = $sourceData['potential'];
            $quote = $sourceData['quote'];
            $salesorder = $sourceData['salesorder'];
            $closedWonPotential = $sourceData['closed_won_potential'];
            $convertedLead = $sourceData['converted_lead'];

            if ($lead > 0) {
                $data[$sourceId]['lead_to_converted'] = round($convertedLead / $lead * 100);
                $data[$sourceId]['lead_to_potential'] = round($potential / $lead * 100);
                $data[$sourceId]['lead_to_quote'] = round($quote / $lead * 100);
                $data[$sourceId]['lead_to_closed_won_potential'] = round($closedWonPotential / $lead * 100);
                $data[$sourceId]['lead_to_salesorder'] = round($salesorder / $lead * 100);
            }

            if (!$forExport) {
                $prevLead = $prevData[$sourceId]['lead'];
                $prevPotential = $prevData[$sourceId]['potential'];
                $prevQuote = $prevData[$sourceId]['quote'];
                $prevSalesorder = $prevData[$sourceId]['salesorder'];
                $prevConvertedLead = $prevData[$sourceId]['converted_lead'];
                $prevClosedWonPotential = $prevData[$sourceId]['closed_won_potential'];

                if ($prevLead > 0) {
                    $prevData[$sourceId]['lead_to_converted'] = round($prevConvertedLead / $prevLead * 100);
                    $prevData[$sourceId]['lead_to_potential'] = round($prevPotential / $prevLead * 100);
                    $prevData[$sourceId]['lead_to_quote'] = round($prevQuote / $prevLead * 100);
                    $prevData[$sourceId]['lead_to_closed_won_potential'] = round($prevClosedWonPotential / $prevLead * 100);
                    $prevData[$sourceId]['lead_to_salesorder'] = round($prevSalesorder / $prevLead * 100);
                }

                $data[$sourceId]['cp_lead_to_converted'] = $data[$sourceId]['cp_lead_to_converted'] - $prevData[$sourceId]['cp_lead_to_converted'];
                $data[$sourceId]['cp_lead_to_potential'] = $data[$sourceId]['lead_to_potential'] - $prevData[$sourceId]['lead_to_potential'];
                $data[$sourceId]['cp_lead_to_quote'] = $data[$sourceId]['lead_to_quote'] - $prevData[$sourceId]['lead_to_quote'];
                $data[$sourceId]['cp_lead_to_closed_won_potential'] = $data[$sourceId]['lead_to_closed_won_potential'] - $prevData[$sourceId]['lead_to_closed_won_potential'];
                $data[$sourceId]['cp_lead_to_salesorder'] = $data[$sourceId]['lead_to_salesorder'] - $prevData[$sourceId]['lead_to_salesorder'];
            }

            $data[$sourceId]['lead_to_converted'] = round($data[$sourceId]['lead_to_converted'], 0);
            $data[$sourceId]['lead_to_potential'] = round($data[$sourceId]['lead_to_potential'], 0);
            $data[$sourceId]['lead_to_quote'] = round($data[$sourceId]['lead_to_quote'], 0);
            $data[$sourceId]['lead_to_closed_won_potential'] = round($data[$sourceId]['lead_to_closed_won_potential'], 0);
            $data[$sourceId]['lead_to_salesorder'] = round($data[$sourceId]['lead_to_salesorder'], 0);

            if ($forExport) {

                if ($sourceId == 'all') {
                    $data[$sourceId]['lead_to_potential'] = '';
                    $data[$sourceId]['lead_to_quote'] = '';
                    $data[$sourceId]['lead_to_salesorder'] = '';
                    $data[$sourceId]['lead_to_closed_won_potential'] = '';
                    $data[$sourceId]['lead_to_salesorder'] = '';
                }
                else {
                    $data[$sourceId]['lead_to_potential'] .= '%';
                    $data[$sourceId]['lead_to_quote'] .= '%';
                    $data[$sourceId]['lead_to_salesorder'] .= '%';
                    $data[$sourceId]['lead_to_closed_won_potential'] .= '%';
                    $data[$sourceId]['lead_to_salesorder'] .= '%';
                }

                $data[$sourceId]['sales'] = [
                    'value' => $data[$sourceId]['sales'],
                    'type' => 'currency'
                ];

                $data[$sourceId]['revenue'] = [
                    'value' => $data[$sourceId]['revenue'],
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