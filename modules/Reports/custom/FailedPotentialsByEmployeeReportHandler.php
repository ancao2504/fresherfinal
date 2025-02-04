<?php

/*
    FailedPotentialsByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.5.14
*/

use PhpOffice\PhpWord\SimpleType\NumberFormat;

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class FailedPotentialsByEmployeeReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/FailedPotentialsByEmployeeReport/FailedPotentialsByEmployeeReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/FailedPotentialsByEmployeeReport/FailedPotentialsByEmployeeReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/FailedPotentialsByEmployeeReportWidgetFilter.tpl';

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
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], true, false),
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
            vtranslate('LBL_REPORT_NO', 'Reports') => '5%',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') => '50%',
            vtranslate('LBL_REPORT_FAILED_OPPORTUNITY_NUMBER', 'Reports') =>  '22.5%',
            vtranslate('LBL_REPORT_OPPORTUNITY_VALUE', 'Reports') =>  '22.5%',
        ];
    }


    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);
        $data = [['Element', vtranslate('LBL_REPORT_FAILED_OPPORTUNITY_NUMBER', 'Reports'), vtranslate('LBL_REPORT_OPPORTUNITY_VALUE', 'Reports')]];
        $haveData = false;

        foreach ($reportData as $row) {
            if ($row['id'] == 'all') {
                break;
            }

            if ((float)$row['number'] > 0 || (float)$row['value'] > 0) {
                $haveData = true;
            }

            $data[] = [html_entity_decode($row['user_full_name']), (float)($row['number']), (float)$row['value']];
            $links[] = '';
        }

        if (count($data) == 1 || !$haveData)
            return false;

        return [
            'data' => $data,
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
            $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
            $employees = array_keys($employees);
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['id']] = [
                'id' => (!$forExport ? $row['id'] : ++$no),
                'user_full_name' => trim($row['user_full_name']),
                'number' => 0,
                'value' => 0,
            ];

            if (!$forExport) {
                $potentialConditions = [[
                    ['main_owner_id', 'e', $row['id']],
                    ['potentialresult', 'e', 'Closed Lost'],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $data[$row['id']]['number_link'] = getListViewLinkWithSearchParams('Potentials', $potentialConditions);
            }
        }

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['user_full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Get data
        $sql = "SELECT main_owner_id, COUNT(potentialid) AS number, SUM(amount) AS value
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON  (deleted = 0 AND potentialid = crmid)
            WHERE potentialresult = 'Closed Lost' AND main_owner_id IN ('{$employeeIds}')
                AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['main_owner_id']]['number'] = (int)$row['number'];
            $data[$row['main_owner_id']]['value'] = (int)$row['value'];

            $data['all']['number'] += (int)$row['number'];
            $data['all']['value'] += (int)$row['value'];
        }

        if ($forExport) {
            foreach ($data as $key => $values) {
                $data[$key]['value'] = [
                    'value' => $data[$key]['value'],
                    'type' => 'currency'
                ];
            }
        }

        $total = $data['all'];
        unset($data['all']);

        usort($data, function ($a, $b) {
            if ($a['number'] == $b['number']) {
                return 0;
            }
            return ($a['number'] < $b['number']) ? 1 : -1;
        });

        $data['all'] = $total;

        return array_values($data);
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
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/FailedPotentialsByEmployeeReport/FailedPotentialsByEmployeeReport.tpl');
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