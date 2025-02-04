<?php

/*
    PotentialConversionRateReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.04
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class PotentialConversionRateReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/PotentialConversionRateReport/PotentialConversionRateReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/PotentialConversionRateReport/PotentialConversionRateReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/PotentialConversionRateReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'all';
        }

        return $params;
    }

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
            vtranslate('Sales Stage', 'Potentials') => '49%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '50%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);
        $data = [];

        foreach ($reportData as $values) {
            if ($values['potential_number'] > 0)  {
                $data[] = [$values['sales_stage'], (int)$values['potential_number']];
            }
        }

        if (!count($data)) {
            return false;
        }

        return [
            'data' => $data
        ];
    }

    public function getReportData($params){
        global $adb;

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        $sql = "SELECT 0 AS no, vtiger_sales_stage.sales_stage, COUNT(potentialid) AS potential_number
            FROM vtiger_sales_stage
            LEFT JOIN (
                vtiger_potential INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = potentialid)
            ) ON (vtiger_potential.sales_stage = vtiger_sales_stage.sales_stage AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}')
            WHERE vtiger_sales_stage.sales_stage != 'Closed Lost'
            GROUP BY vtiger_sales_stage.sales_stage
            ORDER BY vtiger_sales_stage.sortorderid DESC";

        $result = $adb->pquery($sql);
        $data = [];
        $numberOfPrevRecord = 0;
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $numberOfPrevRecord += (int)$row['potential_number'];

            if ($numberOfPrevRecord == 0) continue;

            $row['potential_number'] = $numberOfPrevRecord;
            $row['sales_stage'] = vtranslate($row['sales_stage'], 'Potentials');

            $data[] = $row;
        }

        $data = array_values($data);
        krsort($data);
        $data = array_values($data);

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);
        $chart = $this->renderChart($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/PotentialConversionRateReport/PotentialConversionRateReport.tpl');
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
