<?php

/*
    TopSourcesByLeadReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.10
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class TopSourcesByLeadReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/TopSourcesByLeadReport/TopSourcesByLeadReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/TopSourcesByLeadReport/TopSourcesByLeadReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/TopSourcesByLeadReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/TopSourcesByLeadReportDetail.js';
    protected $targetModule = 'SOURCE_LEAD';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
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
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_LEAD_SOURCE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_LEAD_NUMBER', 'Reports') =>  '49%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['leadsource']), (float)$row['lead_number']];
            $links[] = '';
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'links' => $links,
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for sales
        $sql = "SELECT 0 AS no, leadsource, COUNT(leadid) AS lead_number
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE leadsource IS NOT NULL AND leadsource != '' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY leadsource
            ORDER BY lead_number DESC
            LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['lead_number'] = (int)$row['lead_number'];
            $row['leadsource'] = vtranslate($row['leadsource']);

            $data[] = $row;
        }

        $data = array_values($data);

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $_REQUEST;

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('TARGET_MODULE', $this->targetModule);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/TopSourcesByLeadReport/TopSourcesByLeadReport.tpl');
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