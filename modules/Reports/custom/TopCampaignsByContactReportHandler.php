<?php

/*
    TopCampaignsByContactReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCampaignsByContactReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CAMPAIGN_CONTACT';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '50%',
            vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports') =>  '49%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['campaignname']), (float)$row['contact_number']];
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
        $sql = "SELECT 0 AS no,  vtiger_campaign.campaignid, vtiger_campaign.campaignname, COUNT(DISTINCT contactid) AS contact_number
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = contactid AND contact_crmentity.deleted = 0)
            INNER JOIN vtiger_campaign ON (vtiger_contactdetails.related_campaign = vtiger_campaign.campaignid)
            INNER JOIN vtiger_crmentity AS campaign_crmentity ON (campaign_crmentity.crmid = vtiger_campaign.campaignid AND campaign_crmentity.deleted = 0)
            WHERE campaign_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_campaign.campaignid
            ORDER BY contact_number DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['contact_number'] = (int)$row['contact_number'];
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}