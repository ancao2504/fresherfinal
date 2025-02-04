<?php

/*
    TopSourcesByConvertedLeadReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.10
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopSourcesByConvertedLeadReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'SOURCE_CONVERTED_LEAD';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_LEAD_SOURCE', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_CONVERTED', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['leadsource']), (float)$row['converted_lead_number']];
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

        // Data for conversionrate
        $sql = "SELECT 0 as no, leadsource, COUNT(leadid) AS lead_number, SUM(IF(converted = 1, 1, 0)) AS converted_lead_number
        FROM
        (
            SELECT vtiger_leaddetails.leadsource, leadid, COUNT(DISTINCT potentialid) AS potential_number, SUM(vtiger_potential.amount) AS amount, vtiger_leaddetails.converted
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity AS lead_crmentity ON (lead_crmentity.crmid = leadid AND lead_crmentity.deleted = 0)
            INNER JOIN vtiger_users ON (lead_crmentity.main_owner_id = id)
            LEFT JOIN (
                vtiger_contactdetails
                INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = contactid AND contact_crmentity.deleted = 0)
                INNER JOIN vtiger_potential ON (vtiger_potential.contact_id = contactid)
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.crmid = potentialid AND potential_crmentity.deleted = 0 AND potentialresult = 'Closed Won')
            )
            ON (vtiger_leaddetails.converted = 1 AND contactid = contact_converted_id AND lead_crmentity.main_owner_id = potential_crmentity.main_owner_id)
            WHERE lead_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY id, leadid
        ) AS temp
        GROUP BY leadsource
        HAVING converted_lead_number > 0";
        
        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['lead_number'] = (int)$row['lead_number'];
            $row['converted_lead_number'] = (int)$row['converted_lead_number'];

            if (empty($row['leadsource'])) {
                $row['leadsource'] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            }
            else {
                $row['leadsource'] = vtranslate($row['leadsource']);
            }

            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}