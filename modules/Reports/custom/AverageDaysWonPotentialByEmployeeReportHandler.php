<?php

/*
    AverageDaysWonPotentialByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class AverageDaysWonPotentialByEmployeeReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/AverageDaysWonPotentialByEmployeeReport/AverageDaysWonPotentialByEmployeeReportFilter.tpl';

    protected $reportObject = 'EMPLOYEE';

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
        // Assign filter meta data
        $this->reportFilterMeta = [
            'record_id' => $this->reportid,
            'report_object' => $this->reportObject,
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], true, false),
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '5%',
            vtranslate('LBL_REPORT_' . $this->reportObject, 'Reports') => '50%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '22.5%',
            vtranslate('LBL_REPORT_AVERAGE_DAYS_FOR_WON_POTENTIAL', 'Reports') =>  '22.5%',
        ];
    }

    protected function getReportData($params, $forExport = false) {
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
                'avg_days' => 0,
            ];

            if (!$forExport) {
                $potentialConditions = [[
                    ['main_owner_id', 'e', $row['id']],
                    ['potentialresult', 'e', 'Closed Won'],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $data[$row['id']]['number_link'] = getListViewLinkWithSearchParams('Potentials', $potentialConditions);
            }
        }

        // Get data
        $sql = "SELECT main_owner_id, count(potentialid) AS number, AVG(won_days) AS avg_days
            FROM (
                SELECT DISTINCT potentialid, main_owner_id, MIN(DATEDIFF(changedon, createdtime)) AS won_days
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity ON (deleted = 0 AND vtiger_crmentity.crmid = potentialid)
                INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_basic.crmid = potentialid)
                INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_basic.id = vtiger_modtracker_detail.id AND fieldname = 'potentialresult' AND postvalue = 'Closed Won')
                WHERE potentialresult = 'Closed Won' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND main_owner_id IN ('{$employeeIds}')
                GROUP BY potentialid
            ) AS temp
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['main_owner_id']]['number'] = (int)$row['number'];
            $data[$row['main_owner_id']]['avg_days'] = (float)$row['avg_days'];
        }

        if ($forExport) {
            foreach ($data as $key => $values) {
                $data[$key]['avg_days'] = [
                    'value' => $data[$key]['avg_days'],
                    'type' => 'double'
                ];
            }
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/AverageDaysWonPotentialByEmployeeReport/AverageDaysWonPotentialByEmployeeReport.tpl');
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