<?php

/*
    TopEmployeesByCompletedTicketReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopEmployeesByCompletedTicketReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'TICKET';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_EMPLOYEE', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_COMPLETED', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_COMPLETED', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [html_entity_decode($row['user_full_name']), (float)$row['closed_ticket_number']];
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

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for ticket
        $sql = "SELECT 0 AS no, id, {$fullNameField} AS user_full_name, COUNT(ticketid) AS ticket_number, SUM(IF(vtiger_troubletickets.status = 'Closed', 1, 0)) AS closed_ticket_number
        FROM vtiger_troubletickets
        INNER JOIN vtiger_crmentity ON (crmid = ticketid AND deleted = 0)
        INNER JOIN vtiger_users ON (main_owner_id = id)
        WHERE createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
        GROUP BY id
        HAVING closed_ticket_number > 0
        LIMIT 10";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $row['no'] = $no++;
            $row['ticket_number'] = (int)$row['ticket_number'];
            $row['closed_ticket_number'] = (int)$row['closed_ticket_number'];

            if ($forExport) {
                unset($row['id']);
            }
            
            $data[] = $row;
        }

        return $data;
    }
}