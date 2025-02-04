<?php

/*
    AverageDaysWonPotentialByDepartmentReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/AverageDaysWonPotentialByEmployeeReportHandler.php');

class AverageDaysWonPotentialByDepartmentReportHandler extends AverageDaysWonPotentialByEmployeeReportHandler {

    protected $reportObject = 'DEPARTMENT';

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
       
        if (empty($params['departments'])) {
            return [];
        }

        // Get employees
        $departments = $params['departments'];
        $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
        $employees = array_keys($employees);
        $departmentEmployees = Reports_CustomReport_Helper::getUsersGroupByDepartment($departments);
        $allDepartments = Reports_CustomReport_Helper::getAllDepartments();
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);    
        $employeeIds = implode("', '", $employees);      
        $employeeDepartments = [];

        foreach ($employees as $employee) {
            foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
                if (array_key_exists($employee, $departmentEmployee)) {
                    $employeeDepartments[$employee][] = $departmentId;
                }
            }
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, false);
        $data = [];
        $no = 0;

        foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
            // For current period
            $data[$departmentId] = [
                'id' => (!$forExport ? $departmentId : ++$no),
                'name' => $allDepartments[$departmentId],
                'number' => 0,
                'days' => 0,
                'avg_days' => 0,
            ];
        }

        // Get data
        $sql = "SELECT DISTINCT potentialid, main_owner_id, MIN(DATEDIFF(changedon, createdtime)) AS won_days
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND vtiger_crmentity.crmid = potentialid)
            INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_basic.crmid = potentialid)
            INNER JOIN vtiger_modtracker_detail ON (vtiger_modtracker_basic.id = vtiger_modtracker_detail.id AND fieldname = 'potentialresult' AND postvalue = 'Closed Won')
            WHERE potentialresult = 'Closed Won' AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND main_owner_id IN ('{$employeeIds}')
            GROUP BY potentialid";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            foreach ($employeeDepartments[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId]['number'] ++;
                $data[$departmentId]['days'] += (float)$row['won_days'];
            }
        }

        foreach ($data as $key => $values) {
            $data[$key]['avg_days'] = (float)($data[$key]['days'] / $data[$key]['number']);

            if ($forExport) {
                $data[$key]['avg_days'] = [
                    'value' => $data[$key]['avg_days'],
                    'type' => 'double'
                ];

                unset($data[$key]['days']);
            }
        }

        return array_values($data);
    }
}