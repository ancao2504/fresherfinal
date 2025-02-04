<?php

/*
    PredictionSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class PredictionSalesReportHandler extends CustomReportHandler {
    protected $chartTemplate = 'modules/Reports/tpls/PredictionSalesReport/PredictionSalesReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/PredictionSalesReport/PredictionSalesReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/PredictionSalesReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'month';
        }

        if (!isset($params['year'])) {
            $params['year'] = Date('Y');
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], false, true),
            'prediction_time_options' => Reports_CustomReport_Helper::getPredictionTimeOptions(),
        ];

        return parent::renderReportFilter($params);
    }

    function getReportHeaders() {
        return false;
    }

    public function getHeaderFromData($reportData) {
        $headerRows = [
            [
                [
                    'label' => '',
                ]
            ],
        ];

        $quarter = ['I', 'II', 'III', 'IV'];
        $firstRow = current($reportData);
        unset($firstRow['name']);
        unset($firstRow['all']);
        end($firstRow);
        $lastKey = key($firstRow);

        if ($lastKey == 12) {
            $displayedBy = vtranslate('LBL_REPORT_MONTH', 'Reports');
        }
        else {
            $displayedBy = vtranslate('LBL_REPORT_QUARTER', 'Reports');
        }

        foreach ($firstRow as $key => $value) {
            $headerRows[0][] = [
                'label' => $displayedBy .' ' . ($lastKey == 12 ? $key : $quarter[$key - 1])
            ];
        }

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports')
        ];

        return $headerRows;
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_SALES', 'Reports'), vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports')]];
        $label = vtranslate('LBL_REPORT_' . (strtoupper($params['displayed_by'])), 'Reports');
        $quarter = ['I', 'II', 'III', 'IV'];

        foreach ($reportData[1] as $columnKey => $value) {
            if ($columnKey == 'name' || $columnKey == 'all') continue;

            $tempLabel = $label . ' ' . ($params['displayed_by'] == 'quarter' ? $quarter[$columnKey - 1] : $columnKey);
            $data[$columnKey][0] = $tempLabel;
            $data[$columnKey][1] = (float)$value;
        }

        foreach ($reportData[0] as $columnKey => $value) {
            if ($columnKey == 'name' || $columnKey == 'all') continue;

            $data[$columnKey][2] = (float)$value;
        }

        if (count($data) == 1)
            return false;

        return [
            'data' => array_values($data),
        ];
    }

    function getReportData($params, $forExport = false) {
        global $adb;
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        if (!isset($currentConfig['sales_forecast']) || !isset($currentConfig['sales_forecast']['min_successful_percentage']) || empty($currentConfig['sales_forecast']['min_successful_percentage'])) {
            $minProbability = 0;
        }
        else {
            $minProbability = $currentConfig['sales_forecast']['min_successful_percentage'];
        }

        $displayedBy = strtoupper($params['displayed_by']);
        $interval = ($displayedBy == 'MONTH' ? 1 : 3);
        $year = $params['year'];
        $fromDate = MAX(Date('Y-m-d'), "{$year}-01-01");
        $toDate = "{$year}-12-31";
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($fromDate, $toDate, $interval);
        $data = [
            'number' => [
                'name' => vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports')
            ],
            'value' => [
                'name' => vtranslate('LBL_REPORT_VALUE', 'Reports')
            ]
        ];

        foreach ($ranges as $range) {
            $tempTime = Date('n', strtotime($range['from']));
            $tempTime = (int)($tempTime / $interval) + (bool)($tempTime % $interval);

            $data['number'][$tempTime] = 0;
            $data['value'][$tempTime] = 0;
        }

        $data['number']['all'] = 0;
        $data['value']['all'] = 0;

        $sql = "SELECT COUNT(potentialid) AS number, SUM(amount) AS value, {$displayedBy}(closingdate) AS time
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity on (deleted = 0 AND crmid = potentialid)
            WHERE (potentialresult IS NULL OR potentialresult = '') AND probability >= ?
                AND closingdate > '{$fromDate} 00:00:00' AND closingdate <= '{$toDate} 23:59:59'
            GROUP BY time";

        $result = $adb->pquery($sql, [$minProbability]);

        while ($row = $adb->fetchByAssoc($result)) {
            $data['number'][$row['time']] = $row['number'];
            $data['value'][$row['time']] = $row['value'];

            $data['number']['all'] += $row['number'];
            $data['value']['all'] += $row['value'];
        }

        if ($forExport) {
            foreach ($data['value'] as $key => $value) {
                if ($key == 'name') continue;

                $data['value'][$key] = [
                    'value' => $value,
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
        $reportData = $this->getReportData($params);
        $chart = $this->renderChart($params);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, false);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], 1);

        foreach ($ranges as $key => $range) {
            $ranges[$key] = [
                'label' => Date('m-Y', strtotime($range['from'])),
                'key' => Date('Y_n', strtotime($range['from'])),
            ];
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('CHART', $chart);
        $viewer->assign('RANGES', $ranges);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/PredictionSalesReport/PredictionSalesReport.tpl');
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