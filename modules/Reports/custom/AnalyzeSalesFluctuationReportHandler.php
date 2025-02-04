<?php

/*
    AnalyzeSalesFluctuationReportHandler.php
    Author: Phuc Lu
    Refactor: Phu Vo
    Date: 2020.08.19
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class AnalyzeSalesFluctuationReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/AnalyzeSalesFluctuationReport/AnalyzeSalesFluctuationReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/AnalyzeSalesFluctuationReport/AnalyzeSalesFluctuationReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/AnalyzeSalesFluctuationReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/AnalyzeSalesFluctuationReportDetail.js';
    protected $formatNumber = 'float';

    function getReportHeaders() {
        $displayedBy = (!isset($_REQUEST['displayed_by']) || empty($_REQUEST['displayed_by']) ? 'year' : $_REQUEST['displayed_by']);

        if ($displayedBy == 'year') {
            $j = 12;
            $label = 'MONTH';
        }
        else {
            $j = Date('t');
            $label = 'DAY';
        }

        $headers = [
            '' => '10%',
        ];

        for ($i = 1;$i <= $j;$i++) {
            $headers[vtranslate('LBL_REPORT_' . $label, 'Reports') . ' ' . $i] = (90 / $j) . '%';
        }

        return $headers;
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', $reportData[0]['name'], $reportData[1]['name']]];

        $displayedBy = (!isset($params['displayed_by']) || empty($params['displayed_by']) ? 'year' : $params['displayed_by']);

        if ($displayedBy == 'year') {
            $label = 'MONTH';
        }
        else {
            $label = 'DAY';
        }

        foreach ($reportData[0]['data'] as $key => $sales) {
            $data[] = [($key + 1), (float)$sales, $reportData[1]['data'][$key]];
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => $data,
            'xlabel' => vtranslate('LBL_REPORT_' . $label, 'Reports'),
            'ylabel' => vtranslate('LBL_REPORT_SALES', 'Reports')
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $displayedBy = (!isset($params['displayed_by']) || empty($params['displayed_by']) ? 'year' : $params['displayed_by']);
        $data = [];
        $toDate = Date('Y-m-t');

        if ($displayedBy == 'year') {
            $j = 12;
            $currentYear = Date('Y');
            $fromDate = ($currentYear - 1) . '-01-01';
            $seperateDate = Date('Y-01-01');
            $groupBy = 'MONTH';

            $data[] = [
                'name' => vtranslate('LBL_REPORT_YEAR', 'Reports') . ' ' . (int)$currentYear,
                'data' => []
            ];

            $data[] = [
                'name' => vtranslate('LBL_REPORT_YEAR', 'Reports') . ' ' . (int)($currentYear - 1),
                'data' => []
            ];

        }
        else {
            $j = Date('t');
            $currentMonth = Date('m');
            $fromDate = Date('Y-m-01', strtotime(Date('Y-m-01') . ' -1 month'));
            $seperateDate = Date('Y-m-01');
            $groupBy = 'DAY';

            $data[] = [
                'name' => vtranslate('LBL_REPORT_MONTH', 'Reports') . ' ' . (int)$currentMonth,
                'data' => []
            ];

            if ($currentMonth == 1) {
                $data[] = [
                    'name' => vtranslate('LBL_REPORT_MONTH', 'Reports') . ' 12 ' . strtolower(vtranslate('LBL_REPORT_LAST_YEAR', 'Reports')),
                    'data' => []
                ];
            }
            else {
                $data[] = [
                    'name' => vtranslate('LBL_REPORT_MONTH', 'Reports') . ' ' . (int)($currentMonth - 1),
                    'data' => []
                ];
            }
        }

        for ($i = 0; $i < $j; $i++) {
            foreach ($data as $key => $values) {
                $data[$key]['data'][] = 0;
            }
        }

        // Get sales order
        $sql = "SELECT sum_time, group_by, SUM(total) AS sales
            FROM (
                SELECT IF(createdtime < '{$seperateDate}', 1, 0) AS sum_time, total, salesorderid, {$groupBy}(createdtime) - 1 AS group_by
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = salesorderid)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND createdtime BETWEEN '{$fromDate}' AND '{$toDate}'
            ) AS temp
            GROUP BY sum_time, group_by";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $data[$row['sum_time']]['data'][$row['group_by']] = (float)$row['sales'];
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportHeaders = $this->getReportHeaders($params);
        $reportData = $this->getReportData($params);
        $chart = $this->renderChart($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('FORMAT_NUMBER', $this->formatNumber);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/AnalyzeSalesFluctuationReport/AnalyzeSalesFluctuationReport.tpl');
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