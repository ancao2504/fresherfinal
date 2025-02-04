<?php

/*
    TopEmployeesByFailedPotentialSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.11
*/

require_once('modules/Reports/custom/TopEmployeesByPotentialSalesReportHandler.php');

class TopEmployeesByFailedPotentialSalesReportHandler extends TopEmployeesByPotentialSalesReportHandler {

    protected function getReportData($params, $forExport = false) {
        global $adb;

        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        // Data for sale order
        $sql = "SELECT 0 as no, id, {$fullNameField} AS user_full_name, SUM(amount) AS potential_sales, COUNT(potentialid) AS potential_number
            FROM vtiger_potential 
            INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = potentialid)
            INNER JOIN vtiger_users ON (main_owner_id = id)
            WHERE potentialresult = 'Closed Lost'";

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
        ORDER BY potential_sales DESC
        LIMIT 10";

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['potential_sales'] = (float)$row['potential_sales'];
            $row['potential_number'] = (int)$row['potential_number'];

            if ($forExport) {
                $row['potential_number'] = [
                    'value' => $row['potential_number'],
                    'type' => 'currencry'
                ];
                unset($row['id']);
            }

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}