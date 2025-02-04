<?php

/*
    SalesFunnelReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.04
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesFunnelReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesFunnelReport/SalesFunnelReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesFunnelReport/SalesFunnelReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesFunnelReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'all';
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'all_campaigns' => Campaigns_Data_Model::getAllCampaigns(true),
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], false, true),
            'displayed_by_options' => Reports_CustomReport_Helper::getDisplayedByForSalesFunnelReport(),
            'input_validators' => [
                'from_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'from_date',
                    'label' => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                'to_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'to_date',
                    'label' => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return false;
    }

    public function getHeaderFromData($reportData) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $quarter = ['I', 'II', 'III', 'IV'];
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        if ($params['displayed_by']  == 'three_latest_years') {
            $displayedLabel = 'LBL_REPORT_YEAR';
        }
        else {
            $displayedLabel = 'LBL_REPORT_' . strtoupper($params['displayed_by']);
        }

        $headerRows = [
            [
                [
                    'label' => '',
                ]
            ]
        ];

        for ($i = 1; $i < count(current($reportData)) - 1; $i++) {
            if ($params['displayed_by'] == 'quarter') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $quarter[$i - 1];
            }

            if ($params['displayed_by'] == 'month') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . $i;
            }

            if ($params['displayed_by'] == 'three_latest_years') {
                $label = vtranslate("{$displayedLabel}", 'Reports') . ' ' . (Date('Y') - 3 + $i);
            }

            $headerRows[0][] = [
                'label' => $label
            ];
        }

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports')
        ];

        return $headerRows;
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);

        $data[] = [vtranslate('LBL_REPORT_LEAD', 'Reports'), (int)$reportData['lead_number']];
        $data[] = [vtranslate('LBL_REPORT_POTENTIAL', 'Reports'), (int)$reportData['potential_number']];
        $data[] = [vtranslate('LBL_REPORT_QUOTE', 'Reports'), (int)$reportData['quote_number']];
        $data[] = [vtranslate('LBL_REPORT_SALES_ORDER', 'Reports'), (int)$reportData['sales_order_number']];

        if ($reportData['lead_number'] == 0) {
            return false;
        }

        return [
            'data' => $data
        ];
    }

    public function getReportData($params){
        global $adb;

        $displayedBy = $params['displayed_by'];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $leadWhere = '';

        if ($displayedBy == 'employee') {
            if ($params['employee'] == '0') {
                $employees = Reports_CustomReport_Helper::getUsersByDepartment($params['department'], false, false);
                $employees = array_keys($employees);
                $employees = implode("','" , $employees);
            }
            else {
                $employees = $params['employee'];
            }

            $leadWhere = "AND lead_crmentity.main_owner_id IN ('{$employees}')";
        }

        if ($displayedBy == 'campaign') {
            if ($params['campaign'] == '0') {
                $campaigns = Campaigns_Data_Model::getAllCampaigns();
                $campaigns = array_keys($campaigns);
                $campaigns = implode("','" , $campaigns);
            }
            else {
                $campaigns = $params['campaign'];
            }

            $leadWhere = "AND vtiger_leaddetails.related_campaign IN ('{$campaigns}')";
        }

        // Get lead
        $sql = "SELECT COUNT(vtiger_leaddetails.leadid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
            WHERE lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$leadWhere}";

        $leadCount = $adb->getOne($sql);

        // Get potential
        $sql = " SELECT COUNT(DISTINCT vtiger_leaddetails.leadid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id)
            INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
            WHERE lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$leadWhere}";

        $potentialCount = $adb->getOne($sql);

        // Get quote
        $sql = "SELECT COUNT(DISTINCT vtiger_leaddetails.leadid)
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity AS quote_crmentity ON (quote_crmentity.deleted = 0 AND quote_crmentity.crmid = vtiger_quotes.quoteid)
            INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_quotes.contactid)
            INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
            WHERE vtiger_quotes.quotestage NOT IN ('Created') AND lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$leadWhere}";

        $quoteCount = $adb->getOne($sql);

        // Get sales order
        $sql = "SELECT COUNT(DISTINCT vtiger_leaddetails.leadid)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_salesorder.contactid)
            INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.deleted = 0 AND contact_crmentity.crmid = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_leaddetails ON (vtiger_leaddetails.converted = 1 AND vtiger_leaddetails.contact_converted_id = vtiger_contactdetails.contactid)
            INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.deleted = 0 AND lead_crmentity.crmid = vtiger_leaddetails.leadid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' {$leadWhere}";

        $salesOrderCount = $adb->getOne($sql);

        return [
            'lead_number' => $leadCount,
            'potential_number' => $potentialCount,
            'quote_number' => $quoteCount,
            'sales_order_number' => $salesOrderCount,
        ];
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);

        $viewer->display('modules/Reports/tpls/SalesFunnelReport/SalesFunnelReport.tpl');
    }
}
