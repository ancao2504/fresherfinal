<?php

/*
    TicketsByEmployeeReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.18
*/

require_once('modules/Reports/custom/CustomReportHandler.php');

class TicketsByEmployeeReportHandler extends CustomReportHandler {
    protected $chartTemplate = 'modules/Reports/tpls/TicketsByEmployeeReport/TicketsByEmployeeReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/CustomerConversionRateByEmployeeReport/CustomerConversionRateByEmployeeReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/CustomerConversionRateByEmployeeReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/CustomerConversionRateByEmployeeReportDetail.js';
    protected $reportObject = 'EMPLOYEE';

    // Return filter params. Override this function to handle your own logic if needed
    function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['departments'])) $params['departments'] = ['0'];
        if (!isset($params['employees'])) $params['employees'] = ['0'];

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], false, true),
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
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

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('Nhân viên', 'Users') =>  '45%',
            vtranslate('Ticket', 'Reports') =>  '40%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('Ticket', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            if ($row['id'] == 'all') continue;
            
            $data[] = [html_entity_decode($row['full_name']), (float)$row['tickets']];
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

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['id']] = [
                'id' => (!$forExport ? $row['id'] : ++$no),
                'full_name' => trim($row['user_full_name']),
                'tickets' => 0,
            ];
        }

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');
        $data['all']['tickets'] = 0;

        // Data for Ticket
        $sql = "SELECT 0 AS no, vtiger_crmentity.main_owner_id, COUNT(ticketid) AS tickets
            FROM vtiger_troubletickets
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = ticketid)
            WHERE 
                vtiger_crmentity.main_owner_id IN ('{$employeeIds}')
                AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_crmentity.main_owner_id";
        // echo $sql;die;
        $result = $adb->pquery($sql);
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $data[$row['main_owner_id']]['no'] = $no++;
            $data[$row['main_owner_id']]['tickets'] = (int)$row['tickets'];
            $data['all']['tickets'] = $data['all']['tickets'] + (int)$row['tickets'];
        }

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
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);   

        $viewer->display('modules/Reports/tpls/TicketsByEmployeeReport/TicketsByEmployeeReport.tpl');
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