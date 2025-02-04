<?php

/*
    SucceededFailedPotentialsByProvinceReportHandler.php
    Author: Phuc Lu
    Date: 2020.5.14
*/

use PhpOffice\PhpWord\SimpleType\NumberFormat;

require_once('modules/Reports/custom/SucceededFailedPotentialsByIndustryReportHandler.php');

class SucceededFailedPotentialsByProvinceReportHandler extends SucceededFailedPotentialsByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SucceededFailedPotentialsByProvinceReportWidgetFilter.tpl';
    protected $reportObject = 'PROVINCE';

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        if (!isset($params['province']) || empty($params['province'])) {
            return [];
        }
        
        $data = [];      
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $personalAccountId = Accounts_Data_Helper::getPersonalAccountId();
        $province = $params['province'];
        $allTypes = [
            'succeeded' => vtranslate('LBL_REPORT_WON', 'Reports'),
            'failed' => vtranslate('LBL_REPORT_LOST', 'Reports'),
            'taking_care' => vtranslate('LBL_REPORT_TAKING_CARE', 'Reports'),
        ];

        $no = 0;

        foreach ($allTypes as $type => $label) {
            $data[$type] = [
                'id' => (!$forExport ? $type : ++$no),
                'label' => $label,
                'number' => 0,
                'number_rate' => 0,
                'value' => 0,
                'value_rate' => 0,
            ];
        }

        // For all data   
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['label'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Get data
        $sql = "SELECT type, SUM(amount) AS value, COUNT(potentialid) AS number
        FROM (
                SELECT (CASE WHEN potentialresult = 'Closed Won' THEN 'succeeded' WHEN potentialresult = 'Closed Lost' THEN 'failed' ELSE 'taking_care' END) AS type, amount, potentialid
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
                LEFT JOIN (
                        vtiger_account
                        INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
                        INNER JOIN vtiger_accountbillads ON (vtiger_accountbillads.accountaddressid = vtiger_account.accountid)
                ) ON (vtiger_potential.related_to = vtiger_account.accountid) 
                LEFT JOIN (
                    vtiger_contactdetails INNER JOIN vtiger_crmentity AS contact_crmentity ON (contact_crmentity.crmid = vtiger_contactdetails.contactid AND contact_crmentity.deleted = 0)
                    INNER JOIN vtiger_contactsubdetails ON (vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid)
                    INNER JOIN vtiger_contactaddress ON (vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid)
                ) ON (vtiger_contactdetails.contactid = vtiger_potential.contact_id AND vtiger_potential.related_to = '{$personalAccountId}')
                WHERE (bill_city = ? AND vtiger_potential.related_to != '{$personalAccountId}' OR vtiger_potential.related_to = '{$personalAccountId}' AND mailingcity = ?) AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ) AS temp
        GROUP BY type";

        $result = $adb->pquery($sql, [$province, $province]);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['type']]['number'] = (int)$row['number'];
            $data[$row['type']]['value'] = (int)$row['value'];
            
            $data['all']['number'] += (int)$row['number'];
            $data['all']['value'] += (int)$row['value'];
        }

        foreach ($data as $key => $values) {
            if ($data['all']['number'] != 0) {
                $data[$key]['number_rate'] = CurrencyField::convertToUserFormat($data[$key]['number'] / $data['all']['number'] * 100);

                if (!$forChart) {
                    $data[$key]['number_rate'] .= '%';
                }
            }
            else {
                $data[$key]['number_rate'] = '-';
                
                if ($forChart) {
                    $data[$key]['number_rate'] = '0%';
                }
            }

            if ($data['all']['value'] != 0) {
                $data[$key]['value_rate'] = CurrencyField::convertToUserFormat($data[$key]['value'] / $data['all']['value'] * 100);

                if (!$forChart) {
                    $data[$key]['value_rate'] .= '%';
                }
            }
            else {
                $data[$key]['value_rate'] = '-';

                if ($forChart) {
                    $data[$key]['number_rate'] = 0;
                }
            }

            if ($forExport) {
                $data[$key]['value'] = [
                    'value' => $data[$key]['value'],
                    'type' => 'currency'
                ];
            }
        }

        return array_values($data);
    }
}