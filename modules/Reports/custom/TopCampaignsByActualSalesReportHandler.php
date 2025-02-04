<?php

/*
    TopCampaignsByActualSalesReportHandler.php
    Author: Phuc Lu
    Date: 2020.08.12
*/

require_once('modules/Reports/custom/TopSourcesByLeadReportHandler.php');

class TopCampaignsByActualSalesReportHandler extends TopSourcesByLeadReportHandler {
    protected $targetModule = 'CAMPAIGN_ACTUAL_SALES';

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '15px',
            vtranslate('LBL_REPORT_CAMPAIGN', 'Reports') =>  '49%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '25%',
            vtranslate('LBL_REPORT_SALES_ORDER_NUMBER', 'Reports') =>  '25%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params);
        $data = [['Element', vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')]];
        $links = [];

        foreach ($reportData as $row) {
            $data[] = [vtranslate($row['campaignname']), (float)$row['sales']];
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
        $sql = "SELECT 0 AS no, vtiger_campaign.campaignid, vtiger_campaign.campaignname, SUM(vtiger_salesorder.total) AS sales, COUNT(vtiger_salesorder.salesorderid) AS sales_count
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_campaign ON (vtiger_salesorder.related_campaign = vtiger_campaign.campaignid)
            INNER JOIN vtiger_crmentity AS campaign_crmentity ON (campaign_crmentity.crmid = vtiger_campaign.campaignid AND campaign_crmentity.deleted = 0)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND campaign_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY vtiger_campaign.campaignid
            ORDER BY sales DESC
            LIMIT 5";

        $result = $adb->pquery($sql);
        $data = [];
        $no = 1;

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);

            $row['no'] = $no++;
            $row['sales'] = (float)$row['sales'];
            $row['sales_count'] = (int)$row['sales_count'];
        
            if ($forExport) {
                unset($row['campaignid']);
            }
            
            $data[] = $row;            
        }

        $data = array_values($data);

        return $data;
    }
}