<?php

/*
    PredictionSalesByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class PredictionSalesByEmployeeReportHandler extends CustomReportHandler {

    protected $reportObject = 'EMPLOYEE';
    protected $reportFilterTemplate = 'modules/Reports/tpls/PredictionSalesByEmployeeReport/PredictionSalesByEmployeeReportFilter.tpl';

    function getFilterParams()
    {
        $params = parent::getFilterParams();

        if (!isset($params['period'])) {
            $params['period'] = '3_next_months';
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
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
                    'label' => vtranslate('LBL_REPORT_' . $this->reportObject, 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ]
            ],
            [
                [
                    'label' => ''
                ]
            ]
        ];

        $firstRow = current($reportData);

        foreach ($firstRow as $key => $value) {
            if ($key == 'user_full_name' || $key == 'name' || $key == 'all' || strpos($key, '_value') > 0) continue;

            $month = explode('_', $key);
            $headerRows[0][] = [
                'label' => vtranslate('LBL_REPORT_MONTH', 'Reports') .' ' . $month[1] . '-' . $month[0],
                'merge' => [
                    'row'=> 1,
                    'column' => 2
                ]
            ];

            $headerRows[1][] = ['label' => vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports')];
            $headerRows[1][] = ['label' => vtranslate('LBL_REPORT_VALUE', 'Reports')];
        }

        $headerRows[0][] = [
            'label' => vtranslate('LBL_REPORT_TOTAL_PREDICTED_POTENTIAL_SALES', 'Reports'),
            'merge' => [
                'row'=> 2,
                'column' => 1
            ]
        ];

        return $headerRows;
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

        $interval = 1;
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, false);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], $interval);

        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (!$forExport) {
                $data[$row['id']]['id'] = $row['id'];
            }

            $data[$row['id']]['user_full_name'] = $row['user_full_name'];

            foreach ($ranges as $range) {
                $data[$row['id']][Date('Y_n', strtotime($range['from'])) . '_number'] = 0;
                $data[$row['id']][Date('Y_n', strtotime($range['from'])) . '_value'] = 0;

                if (!$forExport) {
                    $potentialConditions = [[
                        ['main_owner_id', 'e', $row['id']],
                        ['closingdate', 'bw', $range['from'] . ',' . $range['to']]
                    ]];

                    $data[$row['id']][Date('Y_n', strtotime($range['from'])) . '_link'] = getListViewLinkWithSearchParams('Potentials', $potentialConditions);
                }
            }

            $data[$row['id']]['all'] = 0;
        }

        $data['all'] = current($data);
        $data['all']['user_full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        if (!$forExport) {
            $data['all']['id'] = 'all';
        }

        $sql = "SELECT main_owner_id, COUNT(potentialid) AS number, SUM(amount) AS value, CONCAT(YEAR(closingdate), '_', MONTH(closingdate)) AS period
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity on (deleted = 0 AND crmid = potentialid)
            WHERE (potentialresult IS NULL OR potentialresult = '') AND probability >= ?
                AND closingdate BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND main_owner_id IN ('{$employeeIds}')
            GROUP BY main_owner_id, period";

        $result = $adb->pquery($sql, [$minProbability]);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']][$row['period'] . '_number'] = $row['number'];
            $data[$row['main_owner_id']][$row['period'] . '_value'] = $row['value'];
            $data[$row['main_owner_id']]['all'] += $row['value'];

            $data['all'][$row['period'] . '_number'] += $row['number'];
            $data['all'][$row['period'] . '_value'] += $row['value'];
            $data['all']['all'] += $row['value'];
        }

        $data = array_values($data);

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);
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
        $viewer->assign('RANGES', $ranges);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/PredictionSalesByEmployeeReport/PredictionSalesByEmployeeReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);

        foreach ($reportData as $dataKey => $data) {
            foreach ($data as $key => $value) {
                if ($key == 'all' || strpos($key, '_value') > 0) {
                    $reportData[$dataKey][$key] = [
                        'value' => $value,
                        'type' => 'currency'
                    ];
                };
            }
        }

        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}