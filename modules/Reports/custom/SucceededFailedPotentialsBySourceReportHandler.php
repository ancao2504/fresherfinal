<?php

/*
    SucceededFailedPotentialsBySourceReportHandler.php
    Author: Phuc Lu
    Date: 2020.5.14
*/

use PhpOffice\PhpWord\SimpleType\NumberFormat;

require_once('modules/Reports/custom/SucceededFailedPotentialsByIndustryReportHandler.php');

class SucceededFailedPotentialsBySourceReportHandler extends SucceededFailedPotentialsByIndustryReportHandler {

    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SucceededFailedPotentialsBySourceReportWidgetFilter.tpl';
    protected $reportObject = 'SOURCE';

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        if (!isset($params['source']) || empty($params['source'])) {
            return [];
        }
        
        $data = [];      
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $source = $params['source'];
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

            if (!$forExport) {
                $potentialConditions = [[
                    ['leadsource', 'e', $source],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                if ($type == 'succeeded') {
                    $potentialConditions[0][] = ['potentialresult', 'e', 'Closed Won'];
                }

                if ($type == 'failed') {
                    $potentialConditions[0][] = ['potentialresult', 'e', 'Closed Lost'];
                }

                if ($type == 'taking_care') {
                    $potentialConditions[0][] = ['potentialresult', 'e', ''];
                }

                $data[$type]['number_link'] = getListViewLinkWithSearchParams('Potentials', $potentialConditions);
            }
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
                    WHERE leadsource = ? AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
                ) AS temp
            GROUP BY type";

        $result = $adb->pquery($sql, [$source]);

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