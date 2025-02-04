<?php

/*
    TopCampaignsByPotentialSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCampaignsByPotentialSalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CAMPAIGN_POTENTIAL_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['campaignname']), (float)$row['potential_sales']];
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

        // Data for sales
        $sql = "SELECT 0 AS no, vtiger_campaign.campaignid, vtiger_campaign.campaignname, SUM(vtiger_potential.amount) AS potential_sales, COUNT(potentialid) AS potential_number
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity AS campaign_crmentity ON (campaign_crmentity.deleted = 0 AND vtiger_campaign.campaignid = campaign_crmentity.crmid)
            INNER JOIN vtiger_potential ON (vtiger_potential.campaignid = vtiger_campaign.campaignid)
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid) 
            WHERE campaign_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_campaign.campaignid
            ORDER BY potential_sales DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['potential_number'] = (int)$row['potential_number'];
            $row['potential_sales'] = (int)$row['potential_sales'];
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}