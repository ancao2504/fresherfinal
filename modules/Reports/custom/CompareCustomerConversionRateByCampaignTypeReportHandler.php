<?php

/**
 * Name: CompareCustomerConversionRateByCampaignTypeReportHandler.php
 * Author: Phu Vo
 * Date: 2020.10.12
 */

require_once('modules/Reports/custom/CompareCustomerConversionRateByCampaignReportHandler.php');


class CompareCustomerConversionRateByCampaignTypeReportHandler extends CompareCustomerConversionRateByCampaignReportHandler {

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('Loại chiến dịch', 'Reports') => '30%',
            vtranslate('Tỷ lệ Cơ hội / Đầu mối', 'Reports') =>  '7%',
            vtranslate('Tỷ lệ Thành công / Cơ hội', 'Reports') =>  '7%',
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        // Handle from date and to date
        $campaigns = [];
        $campaignIdToType = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params);

        // Data for campaigns
        $sql = "SELECT campaignid, campaignname, campaigntype
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (crmid = campaignid AND deleted = 0)
            WHERE createdtime BETWEEN  '{$period['from_date']}' AND '{$period['to_date']}'";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaignType = !empty($row['campaigntype']) ? $row['campaigntype'] : 'Undefined';
            $row['campaignname'] = !empty($row['campaigntype']) ? vtranslate($row['campaigntype'], 'Campaigns') : vtranslate('LBL_REPORT_UNDEFINED', 'Reports');
            $row['all_potentials'] = 0;
            $row['won_potentials'] = 0;
            $row['all_leads'] = 0;
            $row['potential_lead_ratio'] = 0;
            $row['won_potential_ratio'] = 0;
            $campaigns[$row['campaigntype']] = $row;
            $campaignIdToType[$row['campaignid']] = $campaignType;
        }

        $campaignIds = join("', '", array_keys($campaignIdToType));

        // Data for all potentials
        $sql = "SELECT campaignid, COUNT(potentialid) AS number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE isconvertedfromlead = 1 AND  campaignid IN ('{$campaignIds}')
            GROUP BY campaignid";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaignType = $campaignIdToType[$row['campaignid']];
            $campaigns[$campaignType]['all_potentials'] = $row['number'];
        }

        // Data for won potentials
        $sql = "SELECT campaignid, COUNT(potentialid) AS number
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE isconvertedfromlead = 1 AND  sales_stage = 'Closed Won' AND campaignid IN ('{$campaignIds}')
            GROUP BY campaignid";

        $result = $adb->pquery($sql);
            
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaignType = $campaignIdToType[$row['campaignid']];
            $campaigns[$campaignType]['won_potentials'] = $row['number'];
            $campaigns[$campaignType]['won_potential_ratio'] = round(($row['number'] / $campaigns[$campaignType]['all_potentials']) * 100);
        }

        // Data for all leads
        $sql = "SELECT related_campaign AS campaignid, COUNT(leadid) AS number
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE related_campaign IN ('{$campaignIds}')
            GROUP BY related_campaign";
    
        $result = $adb->pquery($sql);
    
        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $campaignType = $campaignIdToType[$row['campaignid']];
            $campaigns[$campaignType]['all_leads'] = $row['number'];
            $campaigns[$campaignType]['potential_lead_ratio'] = round(($campaigns[$campaignType]['all_potentials'] / $row['number']) * 100);
        }

        return array_values($campaigns);
    }
}