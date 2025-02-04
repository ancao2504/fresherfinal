<?php

/*
    FailedPotentialsByReasonReportHandler.php
    Author: Phuc Lu
    Date: 2020.5.14
*/

use PhpOffice\PhpWord\SimpleType\NumberFormat;

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class FailedPotentialsByReasonReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/FailedPotentialsByReasonReport/FailedPotentialsByReasonReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/FailedPotentialsByReasonReport/FailedPotentialsByReasonReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/FailedPotentialsByReasonReportWidgetFilter.tpl';

    public function getFilterParams() {
        $params = parent::getFilterParams();

        if (!isset($params['displayed_by'])) {
            $params['displayed_by'] = 'month';
        }

        if (!isset($params['year'])) {
            $params['year'] = Date('Y');
        }

        return $params;
    }

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'input_validators' => [
                'from_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'from_date',
                    'label' => vtranslate('LBL_REPORT_FROM', 'Reports'),
                ],
                'to_date' => [
                    'mandatory' => false,
                    'presence' => true,
                    'quickcreate' => false,
                    'masseditable' => false,
                    'defaultvalue' => false,
                    'type' => 'date',
                    'name' => 'to_date',
                    'label' => vtranslate('LBL_REPORT_TO', 'Reports'),
                ],
            ],
        ];

        return parent::renderReportFilter($params);
    }

    public function getReportHeaders() {
        return [
            vtranslate('LBL_REPORT_NO', 'Reports') => '3%',
            vtranslate('LBL_REPORT_REASON', 'Reports') => '37%',
            vtranslate('LBL_REPORT_FAILED_OPPORTUNITY_NUMBER', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_NUMBER_RATE', 'Reports') => '15%',
            vtranslate('LBL_REPORT_OPPORTUNITY_VALUE', 'Reports') =>  '15%',
            vtranslate('LBL_REPORT_VALUE_RATE', 'Reports') =>  '15%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, true);
        $data['number_rates'] = [['Element', vtranslate('LBL_REPORT_NUMBER_RATE', 'Reports')]];
        $data['value_rates'] = [['Element', vtranslate('LBL_REPORT_VALUE_RATE', 'Reports')]];
        $haveData = false;

        foreach ($reportData as $key => $row) {
            if ($row['id'] === 'all') break;

            if ((float)$row['number'] > 0 || (float)$row['value'] > 0) {
                $haveData = true;
            }

            $data['number_rates'][] = [$row['reason'], (float)$row['number']];
            $data['value_rates'][] = [$row['reason'], (float)$row['value']];
        }

        if (count($data['number_rates']) == 1 || !$haveData)
            return false;

        return [
            'data' => $data
        ];
    }

    protected function getReportData($params, $forChart = false, $forExport = false) {
        global $adb;

        $data = [];
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $allReasons = Reports_CustomReport_Helper::getPotentialFailedReasonValues(false, false);
        $no = 0;

        foreach ($allReasons as $reasonValue => $reasonLabel) {
            $data[$reasonValue] = [
                'id' => (!$forExport ? $reasonValue : ++$no),
                'reason' => $reasonLabel,
                'number' => 0,
                'number_rate' => 0,
                'value' => 0,
                'value_rate' => 0,
            ];

            if (!$forExport) {
                $potentialConditions = [[
                    ['potentiallostreason', 'e', $reasonValue],
                    ['potentialresult', 'e', 'Closed Lost'],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $data[$reasonValue]['number_link'] = getListViewLinkWithSearchParams('Potentials', $potentialConditions);
            }
        }

        $reasonValues = array_keys($allReasons);
        $reasonValues = implode("','", $reasonValues);

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['reason'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Get data
        $sql = "SELECT potentiallostreason, COUNT(potentialid) AS number, SUM(amount) AS value
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON  (deleted = 0 AND potentialid = crmid)
            WHERE potentialresult = 'Closed Lost' AND potentiallostreason IN ('{$reasonValues}')
                AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY potentiallostreason";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['potentiallostreason']]['number'] = (int)$row['number'];
            $data[$row['potentiallostreason']]['value'] = (int)$row['value'];

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

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $chart = $this->renderChart($params);
        $reportData = $this->getReportData($params);
        $reportHeaders = $this->getReportHeaders();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/FailedPotentialsByReasonReport/FailedPotentialsByReasonReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, false, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}