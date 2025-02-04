<?php

/*
    TopCampaignsByConversionRatioReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCampaignsByConversionRatioReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CAMPAIGN_CONVERSION_RATE';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '39%',
            vtranslate('LBL_REPORT_LEAD_NUMBER', 'Reports') =>  '20%',
            vtranslate('LBL_REPORT_CONVERTED', 'Reports') =>  '20%',
            vtranslate('LBL_REPORT_CONVERTED_LEAD_RATIO', 'Reports') =>  '20%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_CONVERTED_LEAD_RATIO', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['campaignname']), (float)$row['conversion_rate']];
            $links[] = '';
        }        

        if (count($data) == 1)
            return false;
            
        return [
            'data' => $data,
            'links' => $links,
            'is_percentage' => true
        ];
    }

    protected function getReportData($params, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        $sql = "SELECT 0 AS no, campaignid, campaignname,
            COUNT(DISTINCT total_lead.leadid) AS lead_number, 
            COUNT(DISTINCT converted_lead.leadid) AS converted_lead_number,
            COUNT(DISTINCT converted_lead.leadid) / COUNT(DISTINCT total_lead.leadid) AS conversion_rate
            FROM
                vtiger_campaign
                INNER JOIN vtiger_crmentity AS campaign_entity ON (vtiger_campaign.campaignid = campaign_entity.crmid AND campaign_entity.deleted = 0)
                LEFT JOIN (
                    vtiger_leaddetails AS total_lead
                    INNER JOIN vtiger_crmentity AS total_lead_entity ON (total_lead.leadid = total_lead_entity.crmid AND total_lead_entity.deleted = 0)
                ) ON (total_lead.related_campaign = vtiger_campaign.campaignid)
                LEFT JOIN (
                    vtiger_leaddetails AS converted_lead
                    INNER JOIN vtiger_crmentity AS converted_lead_entity ON (converted_lead.leadid = converted_lead_entity.crmid AND converted_lead_entity.deleted = 0)
                ) ON (converted_lead.related_campaign = vtiger_campaign.campaignid AND converted_lead.converted = 1)
            WHERE campaign_entity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY campaignid
            HAVING converted_lead_number > 0
            ORDER BY conversion_rate DESC
            LIMIT 10";
        
        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['lead_number'] = (int)$row['lead_number'];
            $row['converted_lead_number'] = (int)$row['converted_lead_number'];
            $row['conversion_rate'] = round((float)$row['conversion_rate'] * 100, 2);
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}