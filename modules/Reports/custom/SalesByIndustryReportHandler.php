<?php

/*
    SalesByIndustryReportHandler.php
    Author: Phuc Lu
    Date: 2020.06.08
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SalesByIndustryReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/SalesByIndustryReport/SalesByIndustryReportChart.tpl';
    protected $reportFilterTemplate = 'modules/Reports/tpls/SalesByIndustryReport/SalesByIndustryReportFilter.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/SalesByIndustryReportWidgetFilter.tpl';
    protected $detailJsFile = 'modules/Reports/resources/SalesByIndustryReportDetail.js';
    protected $reportObject = 'INDUSTRY';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'report_object' => $this->reportObject,
            'industries' => Reports_CustomReport_Helper::getIndustryValues(false, true),
            'provinces' => Reports_CustomReport_Helper::getAllProvinceValues(false, true, true, ['Accounts', 'Contacts']),
            'sources' => Reports_CustomReport_Helper::getSourceValues(true, false, false),
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
            vtranslate('LBL_REPORT_' . $this->reportObject, 'Reports') => '50%',
            vtranslate('LBL_REPORT_SALES', 'Reports') =>  '23%',
            vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') => '23%',
        ];
    }

    protected function getChartData(array $params) {
        $reportData = $this->getReportData($params, false);
        $data = [
            'categories' => [],
            'series' => [
                [
                    'name' =>  vtranslate('LBL_REPORT_SALES', 'Reports') ,
                    'color'=> 'rgba(32,96,182,1)',
                    'data'=> [],
                    'pointPadding'=> 0.3,
                ],
                [
                    'name' =>  vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports') ,
                    'color'=> 'rgba(139,229,238,0.8)',
                    'data'=> [],
                    'pointPadding'=> 0.2,
                ]
            ],
        ];

        foreach ($reportData as $row) {
            if ($row['id'] === 'all') break;

            $data['categories'][] = $row['name'];
            $data['series'][0]['data'][] = $row['sales'];
            $data['series'][1]['data'][] = $row['potential_sales'];
        }

        if (count($reportData) == 0) {
            return false;
        }

        return [
            'data' => $data
        ];
    }

    public function getReportData($params, $forExport = false){
        global $adb;

        if (empty($params['industries'])) {
            return [];
        }

        // Get industries
        $industries = $params['industries'];
        $industriesPlusCondition = '';
        $allIndustries = Reports_CustomReport_Helper::getIndustryValues(false, false, true);

        if ($industries == '0' || (is_array($industries) && in_array('0', $industries))) {
            $industries = array_keys($allIndustries);
        }

        // Update label for no industry
        $allIndustries[''] = vtranslate('LBL_REPORT_UNDEFINED', 'Reports');

        // Replace no industry with empty value
        if (in_array('1', $industries)) {
            $industries[array_search('1', $industries)] = '';
            $industriesPlusCondition = " OR vtiger_account.industry = '' OR vtiger_account.industry IS NULL";
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $industryIds = implode("','", $industries);

        $data = [];
        $no = 0;

        foreach ($industries as $industryId) {
            // For current period
            $data[$industryId] = [
                'id' => (!$forExport ? $industryId : ++$no),
                'name' => $allIndustries[$industryId],
                'sales' => 0,
                'potential_sales' => 0
            ];
        }

        // For all data
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Get sales order
        $sql = "SELECT IF(vtiger_account.industry IS NULL OR vtiger_account.industry = '', '', vtiger_account.industry) AS industry, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorder_crmentity.deleted = 0 AND salesorder_crmentity.crmid = vtiger_salesorder.salesorderid)
            INNER JOIN vtiger_account ON (vtiger_salesorder.accountid = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE vtiger_salesorder.sostatus NOT IN ('Created', 'Cancelled') AND (vtiger_account.industry IN ('$industryIds') {$industriesPlusCondition})
                AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['sales'] = (float)$row['sales'];
            $data['all']['sales'] += (float)$row['sales'];
        }

        // Get potential
        $sql = "SELECT IF(vtiger_account.industry IS NULL OR vtiger_account.industry = '', '', vtiger_account.industry) AS industry, SUM(vtiger_potential.amount) AS potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity AS potential_crmentity ON (potential_crmentity.deleted = 0 AND potential_crmentity.crmid = vtiger_potential.potentialid)
            INNER JOIN vtiger_account ON (vtiger_potential.related_to = vtiger_account.accountid)
            INNER JOIN vtiger_crmentity AS account_crmentity ON (account_crmentity.deleted = 0 AND account_crmentity.crmid = vtiger_account.accountid)
            WHERE (vtiger_account.industry IN ('$industryIds') {$industriesPlusCondition}) AND potential_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY industry";

        $result = $adb->pquery($sql);

        while ($row = $adb->fetchByAssoc($result)) {
            $row = decodeUTF8($row);
            $data[$row['industry']]['potential_sales'] = (float)$row['potential_sales'];
            $data['all']['potential_sales'] += (float)$row['potential_sales'];
        }

        if ($forExport) {
            foreach ($data as $key => $value) {
                $data[$key]['sales'] = [
                    'value' => $value['sales'],
                    'type' => 'currency'
                ];

                $data[$key]['potential_sales'] = [
                    'value' => $value['potential_sales'],
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
        $viewer->assign('REPORT_OBJECT', $this->reportObject);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SalesByIndustryReport/SalesByIndustryReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);
        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}
