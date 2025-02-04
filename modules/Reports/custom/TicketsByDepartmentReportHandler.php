<?php

/*
    TicketsByDepartmentReportHandler.php
    Author: Phuc Lu
    Date: 2020.05.12
*/

require_once('modules/Reports/custom/TicketsByEmployeeReportHandler.php');

class TicketsByDepartmentReportHandler extends TicketsByEmployeeReportHandler {
    protected $reportObject = 'DEPARTMENT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('Phòng ban', 'Users') =>  '45%',
            vtranslate('Ticket', 'Reports') =>  '40%',
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $departments = $params['departments'];
        
        if (empty($departments)) return [];
        
        // Get employees
        $departments = $params['departments'];
        $allDepartments = Reports_CustomReport_Helper::getAllDepartments();
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        if ($departments == '0' || in_array('0', $departments)) {
            $departments = array_keys($allDepartments);
        }
        
        $departmentIds = implode("', '", $departments);
      
        $data = [];
        $no = 0;

        // Init data structure
        foreach ($allDepartments as $departmentId => $departmentName) {
            if ($departmentId == '0') continue;
            
            $data[$departmentId] = [
                'id' => (!$forExport ? $departmentId['id'] : ++$no),
                'full_name' => $departmentName,
                'tickets' => 0,
            ];
        } 

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Data for Ticket
        $sql = "SELECT 0 AS no, vtiger_troubletickets.users_department, COUNT(ticketid) AS tickets
            FROM vtiger_troubletickets
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = ticketid)
            WHERE 
                vtiger_troubletickets.users_department IN ('{$departmentIds}')
                AND vtiger_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_troubletickets.users_department";

        $result = $adb->pquery($sql);
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            
            $data[$row['users_department']]['full_name'] = $allDepartments[$row['users_department']];
            $data[$row['users_department']]['no'] = $no++;
            $data[$row['users_department']]['tickets'] = (int)$row['tickets'];
            $data['all']['tickets'] = $data['all']['tickets'] + (int)$row['tickets'];
        }

        return array_values($data);
    }
}