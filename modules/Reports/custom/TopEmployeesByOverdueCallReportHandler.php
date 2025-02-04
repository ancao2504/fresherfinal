<?php

/*
    TopEmployeesByOverdueCallReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.10
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopEmployeesByOverdueCallReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CALL';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_NUMBER', 'Reports') =>  '49%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['user_full_name']), (float)$row['number']];
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

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for leads
        $sql = "SELECT 0 as no, id, {$fullNameField} AS user_full_name, COUNT(activityid) AS number
            FROM vtiger_activity
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = activityid AND activitytype = 'Call')
            INNER JOIN vtiger_users ON (main_owner_id = id)
            WHERE events_call_direction = 'Outbound' AND CONCAT(due_date, ' ', time_end) < NOW() AND eventstatus not IN ('Held', 'Not Held')";

        $sqlParams = [];
        
        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Update params for where
        $extWhere = '';

        if (!empty($period['from_date'])) {
            $extWhere .= " AND createdtime >= ?";
            $sqlParams[] = $period['from_date'];
        }

        if (!empty($period['to_date'])) {
            $extWhere .= " AND createdtime <= ?";
            $sqlParams[] = $period['to_date'];
        }

        $sql .= " {$extWhere}
            GROUP BY id
            ORDER BY number DESC
            LIMIT 5";

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['number'] = (int)$row['number'];

            if ($forExport) {
                unset($row['id']);
            }

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}