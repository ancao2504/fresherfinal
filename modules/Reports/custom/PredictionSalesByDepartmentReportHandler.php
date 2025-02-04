<?php

/*
    PredictionSalesByDepartmentReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.20
*/

require_once('modules/Reports/custom/PredictionSalesByEmployeeReportHandler.php');

class PredictionSalesByDepartmentReportHandler extends PredictionSalesByEmployeeReportHandler {
    protected $reportObject = 'DEPARTMENT';

    function getReportHeaders() {
        return false;       
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

        if (empty($params['departments'])) {
            return [];
        }

        // Get employees
        $departments = $params['departments'];
        $allDepartments = Reports_CustomReport_Helper::getAllDepartments();
        $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
        $employees = array_keys($employees);
        $departmentEmployees = Reports_CustomReport_Helper::getUsersGroupByDepartment($departments);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);      
        $employeeDepartment = [];

        foreach ($employees as $employee) {
            foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
                if (array_key_exists($employee, $departmentEmployee)) {
                    $employeeDepartment[$employee][] = $departmentId;
                }
            }
        }

        $interval = 1;
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, false);
        $ranges = Reports_CustomReport_Helper::getRangesByIntervalMonthInRange($period['from_date'], $period['to_date'], $interval);
        $employeeIds = implode("', '", $employees);
        $data = [];
        $no = 0;

        foreach ($departmentEmployees as $departmentId => $departmentEmployee) {
            // For current period
            $data[$departmentId]['name'] = $allDepartments[$departmentId];

            foreach ($ranges as $range) {
                $data[$departmentId][Date('Y_n', strtotime($range['from'])) . '_number'] = 0;
                $data[$departmentId][Date('Y_n', strtotime($range['from'])) . '_value'] = 0;
            }

            $data[$departmentId]['all'] = 0;
        }
        
        // For all data   
        $data['all'] = current($data);
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        $sql = "SELECT main_owner_id, COUNT(potentialid) AS number, SUM(amount) AS value, CONCAT(YEAR(closingdate), '_', MONTH(closingdate)) AS period
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity on (deleted = 0 AND crmid = potentialid)
            WHERE (potentialresult IS NULL OR potentialresult = '') AND probability >= ?
                AND closingdate BETWEEN '{$period['from_date']}' AND '{$period['to_date']}' AND main_owner_id IN ('{$employeeIds}')
            GROUP BY main_owner_id, period";

        $result = $adb->pquery($sql, [$minProbability]);

        while ($row = $adb->fetchByAssoc($result)) {
            foreach ($employeeDepartment[$row['main_owner_id']] as $departmentId) {
                $data[$departmentId][$row['period'] . '_number'] += $row['number'];
                $data[$departmentId][$row['period'] . '_value'] += $row['value'];
                $data[$departmentId]['all'] += $row['value'];
                
                $data['all'][$row['period'] . '_number'] += $row['number'];
                $data['all'][$row['period'] . '_value'] += $row['value'];
                $data['all']['all'] += $row['value'];
            }
        }

        $data = array_values($data);

        return $data;
    }
}