<?php

/*
    SummaryCampaignResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.28
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SummaryCampaignDetailsResultReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/SummaryCampaignDetailsResultReport/SummaryCampaignDetailsResultReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'all_campaigns' => Campaigns_Data_Model::getAllCampaigns(),
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


    function getReportHeaders() {
        return false;
    }

    public function getHeaderFromData($reportData) {
        $headerRows = [
            0 => [
                0 => [
                    'label' => vtranslate('LBL_REPORT_NO', 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ],
                1 => [
                    'label' => vtranslate('LBL_REPORT_CAMPAIGN', 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ],
                2 => [
                    'label' => vtranslate('LBL_REPORT_LEAD', 'Reports'),
                    'merge' => [
                        'row'=> 1,
                        'column' => 4
                    ]
                ],
                3 => [
                    'label' => vtranslate('LBL_REPORT_POTENTIAL', 'Reports'),
                    'merge' => [
                        'row'=> 1,
                        'column' => 4
                    ]
                ],
                4 => [
                    'label' => vtranslate('LBL_REPORT_QUOTE', 'Reports'),
                    'merge' => [
                        'row'=> 1,
                        'column' => 4
                    ]
                ],
                5 => [
                    'label' => vtranslate('LBL_REPORT_SALES_ORDER', 'Reports'),
                    'merge' => [
                        'row'=> 1,
                        'column' => 4
                    ]
                ],
                6 => [
                    'label' => vtranslate('LBL_REPORT_SALES', 'Reports'),
                    'merge' => [
                        'row'=> 1,
                        'column' => 4
                    ]
                ],
                7 => [
                    'label' => vtranslate('LBL_REPORT_REVENUE', 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ],
                8 => [
                    'label' => vtranslate('LBL_REPORT_COST', 'Reports'),
                    'merge' => [
                        'row'=> 2,
                        'column' => 1
                    ]
                ],
            ],
            1 => [
                0 =>  [
                    'label' => '',
                ],
                1 => [
                    'label' => '',
                ],
                2 => [
                    'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
                ],
                3 => [
                    'label' => vtranslate('LBL_REPORT_CONVERTED', 'Reports'),
                ],
                4 => [
                    'label' => vtranslate('LBL_REPORT_TAKING_CARE', 'Reports'),
                ],
                5 => [
                    'label' => vtranslate('LBL_REPORT_PENDING', 'Reports'),
                ],
                6 => [
                    'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
                ],
                7 => [
                    'label' => vtranslate('LBL_REPORT_WON', 'Reports'),
                ],
                8 => [
                    'label' => vtranslate('LBL_REPORT_TAKING_CARE', 'Reports'),
                ],
                9 => [
                    'label' => vtranslate('LBL_REPORT_LOST', 'Reports'),
                ],
                10 => [
                    'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
                ],
                11 => [
                    'label' => vtranslate('LBL_REPORT_CONFIRMED', 'Reports'),
                ],
                12 => [
                    'label' => vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports'),
                ],
                13 => [
                    'label' => vtranslate('LBL_REPORT_CANCELLED', 'Reports'),
                ],
                14 => [
                    'label' => vtranslate('LBL_REPORT_TOTAL', 'Reports'),
                ],
                15 => [
                    'label' => vtranslate('LBL_REPORT_CONFIRMED', 'Reports'),
                ],
                16 => [
                    'label' => vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports'),
                ],
                17 => [
                    'label' => vtranslate('LBL_REPORT_CANCELLED', 'Reports'),
                ],
                18 => [
                    'label' => vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports'),
                ],
                19 => [
                    'label' => vtranslate('LBL_REPORT_PREDICTED_POTENTIAL_SALES', 'Reports'),
                ],
                20 => [
                    'label' => vtranslate('LBL_REPORT_QUOTE_SALES', 'Reports'),
                ],
                21 => [
                    'label' => vtranslate('LBL_REPORT_SALES_ORDER_SALES', 'Reports'),
                ],

            ]
        ];

        return $headerRows;
    }

    function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['campaigns'])) {
            return [];
        }

        $currentReportConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        if (isset($currentReportConfig['sales_forecast']) && !empty($currentReportConfig['sales_forecast']['min_successful_percentage'])) {
            $predictedPercentage = $currentReportConfig['sales_forecast']['min_successful_percentage'];
        }
        else {
            $predictedPercentage = 80;
        }

        $campaigns = $params['campaigns'];
        $campaignIds = implode("', '", $campaigns);
        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
        $data = [];
        $no = 0;

        // Get campaign data
        $sql = "SELECT campaignid, campaignname, actualcost FROM vtiger_campaign WHERE campaignid IN ('$campaignIds')";
        $result = $adb->pquery($sql, []);
        $sumCost = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']] = [
                'campaign_id' => (!$forExport ? $row['campaignid'] : ++$no),
                'campaign_name' => trim($row['campaignname']),
                'lead_total' => 0,
                'lead_converted' => 0,
                'lead_taking_care' => 0,
                'lead_pending' => 0,
                'potential_total' => 0,
                'potential_won' => 0,
                'potential_taking_care' => 0,
                'potential_lost' => 0,
                'quote_total' => 0,
                'quote_confirmed' => 0,
                'quote_not_confirmed' => 0,
                'quote_cancelled' => 0,
                'salesorder_total' => 0,
                'salesorder_confirmed' => 0,
                'salesorder_not_confirmed' => 0,
                'salesorder_cancelled' => 0,
                'potential_sales' => 0,
                'predicted_potential_sales' => 0,
                'quote_sales' => 0,
                'salesorder_sales' => 0,
                'revenue' => 0,
                'cost' => $row['actualcost']
            ];

            $sumCost += (int)$row['actualcost'];

            if (!$forExport) {
                $commonConditions = [[
                    ['related_campaign', 'e', $row['campaignname']],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $potentialConditions = [[
                    ['campaignid', 'e', $row['campaignname']],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $data[$row['campaignid']] = array_merge($data[$row['campaignid']], [
                    'lead_total_link' => getListViewLinkWithSearchParams('Leads', $commonConditions),
                    'lead_converted_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Converted']])]),
                    'lead_taking_care_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'n', 'Converted,Lost Lead']])]),
                    'lead_pending_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Lost Lead']])]),
                    'potential_total_link' => getListViewLinkWithSearchParams('Potentials', $potentialConditions),
                    'potential_won_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($potentialConditions[0], [['potentialresult', 'e', 'Closed Won']])]),
                    'potential_taking_care_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($potentialConditions[0], [['potentialresult', 'e', '']])]),
                    'potential_lost_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($potentialConditions[0], [['potentialresult', 'e', 'Closed Lost']])]),
                    'quote_total_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'n', 'Created']])]),
                    'quote_confirmed_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Approved,Accepted']])]),
                    'quote_not_confirmed_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'n', 'Created,Approved,Rejected']])]),
                    'quote_cancelled_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Rejected']])]),
                    'salesorder_total_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'n', 'Created']])]),
                    'salesorder_confirmed_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'e', 'Approved,Delivered,Partial payment,Full payment']])]),
                    'salesorder_not_confirmed_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'n', 'Created,Approved,Delivered,Partial payment,Full payment,Cancelled']])]),
                    'salesorder_cancelled_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'e', 'Cancelled']])]),
                ]);
            }
        }

        // For all
        $data['all'] = current($data);
        $data['all']['campaign_id'] = (!$forExport ? 'all' : '');
        $data['all']['campaign_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');
        $data['all']['cost'] = $sumCost;

		// Get leads data
        $sql = "SELECT related_campaign, count(leadid) AS lead_total,
            SUM(CASE WHEN leadstatus = 'Converted' THEN 1 ELSE 0 END) AS lead_converted, SUM(CASE WHEN leadstatus = 'Lost Lead' THEN 1 ELSE 0 END) AS lead_pending
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE related_campaign IN ('$campaignIds') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY related_campaign";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['related_campaign']]['lead_total'] = (int)$row['lead_total'];
            $data[$row['related_campaign']]['lead_pending'] = (int)$row['lead_pending'];
            $data[$row['related_campaign']]['lead_converted'] = (int)$row['lead_converted'];
            $data[$row['related_campaign']]['lead_taking_care'] = $row['lead_total'] - $row['lead_converted'] - $row['lead_pending'];

            $data['all']['lead_total'] += (int)$row['lead_total'];
            $data['all']['lead_pending'] += (int)$row['lead_pending'];
            $data['all']['lead_converted'] += (int)$row['lead_converted'];
            $data['all']['lead_taking_care'] += $row['lead_total'] - $row['lead_converted'] - $row['lead_pending'];
        }

        // Get potentials data
        $sql = "SELECT campaignid, count(potentialid) AS potential_total,
            SUM(CASE WHEN potentialresult = 'Closed Won' THEN 1 ELSE 0 END) AS potential_won, SUM(CASE WHEN potentialresult = 'Closed Lost' THEN 1 ELSE 0 END) AS potential_lost,
            SUM(amount) AS potential_sales, SUM(CASE WHEN potentialresult = 'Closed Won' OR ((potentialresult IS NULL OR potentialresult = '') AND probability >= {$predictedPercentage}) THEN amount ELSE 0 END) AS predicted_potential_sales
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE campaignid IN ('$campaignIds') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY campaignid";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']]['potential_total'] = (int)$row['potential_total'];
            $data[$row['campaignid']]['potential_lost'] = (int)$row['potential_lost'];
            $data[$row['campaignid']]['potential_won'] = (int)$row['potential_won'];
            $data[$row['campaignid']]['potential_taking_care'] = $row['potential_total'] - $row['potential_won'] - $row['potential_lost'];
            $data[$row['campaignid']]['potential_sales'] = (int)$row['potential_sales'];
            $data[$row['campaignid']]['predicted_potential_sales'] = (int)$row['predicted_potential_sales'];

            $data['all']['potential_total'] += (int)$row['potential_total'];
            $data['all']['potential_lost'] += (int)$row['potential_lost'];
            $data['all']['potential_won'] += (int)$row['potential_won'];
            $data['all']['potential_taking_care'] += $row['potential_total'] - $row['potential_won'] - $row['potential_lost'];
            $data['all']['potential_sales'] += (int)$row['potential_sales'];
            $data['all']['predicted_potential_sales'] += (int)$row['predicted_potential_sales'];
        }

        // Get quote data
        $sql = "SELECT related_campaign, count(quoteid) AS quote_total, SUM(total) AS quote_sales,
            SUM(CASE WHEN quotestage IN ('Approved', 'Accepted') THEN 1 ELSE 0 END) AS quote_confirmed, SUM(CASE WHEN quotestage IN ('Rejected') THEN 1 ELSE 0 END) AS quote_cancelled
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON (crmid = quoteid AND deleted = 0)
            WHERE quotestage NOT IN ('Created') AND related_campaign IN ('{$campaignIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY related_campaign";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['related_campaign']]['quote_total'] = (int)$row['quote_total'];
            $data[$row['related_campaign']]['quote_cancelled'] = (int)$row['quote_cancelled'];
            $data[$row['related_campaign']]['quote_confirmed'] = (int)$row['quote_confirmed'];
            $data[$row['related_campaign']]['quote_not_confirmed'] = $row['quote_total'] - $row['quote_confirmed'] - $row['quote_cancelled'];
            $data[$row['related_campaign']]['quote_sales'] = (int)$row['quote_sales'];

            $data['all']['quote_total'] += (int)$row['quote_total'];
            $data['all']['quote_cancelled'] += (int)$row['quote_cancelled'];
            $data['all']['quote_confirmed'] += (int)$row['quote_confirmed'];
            $data['all']['quote_not_confirmed'] += $row['quote_total'] - $row['quote_confirmed'] - $row['quote_cancelled'];
            $data['all']['quote_sales'] += (int)$row['quote_sales'];
        }

        // Get sales order data
        $sql = "SELECT related_campaign, count(salesorderid) AS salesorder_total,
            SUM(CASE WHEN sostatus NOT IN ('Created', 'Cancelled') THEN total ELSE 0 END) AS salesorder_sales,
            SUM(CASE WHEN sostatus IN ('Approved', 'Delivered', 'Partial payment', 'Full payment') THEN 1 ELSE 0 END) AS salesorder_confirmed, SUM(CASE WHEN sostatus IN ('Cancelled') THEN 1 ELSE 0 END) AS salesorder_cancelled
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (crmid = salesorderid AND deleted = 0)
            WHERE sostatus NOT IN ('Created') AND related_campaign IN ('{$campaignIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY related_campaign";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['related_campaign']]['salesorder_total'] = (int)$row['salesorder_total'];
            $data[$row['related_campaign']]['salesorder_cancelled'] = (int)$row['salesorder_cancelled'];
            $data[$row['related_campaign']]['salesorder_confirmed'] = (int)$row['salesorder_confirmed'];
            $data[$row['related_campaign']]['salesorder_not_confirmed'] = $row['salesorder_total'] - $row['salesorder_confirmed'] - $row['salesorder_cancelled'];
            $data[$row['related_campaign']]['salesorder_sales'] = (int)$row['salesorder_sales'];

            $data['all']['salesorder_total'] += (int)$row['salesorder_total'];
            $data['all']['salesorder_cancelled'] += (int)$row['salesorder_cancelled'];
            $data['all']['salesorder_confirmed'] += (int)$row['salesorder_confirmed'];
            $data['all']['salesorder_not_confirmed'] += $row['salesorder_total'] - $row['salesorder_confirmed'] - $row['salesorder_cancelled'];
            $data['all']['salesorder_sales'] += (int)$row['salesorder_sales'];
        }

        // Get revenue
        $sql = "SELECT campaignid, SUM(amount_vnd) AS revenue
        FROM (
            SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, campaignid
            FROM (
                SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_salesorder.related_campaign AS campaignid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.related_campaign IN ('$campaignIds')
                    AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'

                UNION ALL

                SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, vtiger_salesorder.related_campaign AS campaignid
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND vtiger_salesorder.related_campaign IN ('$campaignIds')
                    AND salesorder_crmentity.createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            ) AS temp1
        ) AS temp2
        GROUP BY campaignid";
        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['campaignid']]['revenue'] = $row['revenue'];
            $data['all']['revenue'] += $row['revenue'];
        }

        $data = array_values($data);

        if ($forExport) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['campaign_id'] = (empty($data[$i]['campaign_id']) ? '' : $i + 1);

                $data[$i]['potential_sales'] = [
                    'value' => $data[$i]['potential_sales'],
                    'type' => 'currency'
                ];


                $data[$i]['potential_sales'] = [
                    'value' => $data[$i]['predicted_potential_sales'],
                    'type' => 'currency'
                ];

                $data[$i]['potential_sales'] = [
                    'value' => $data[$i]['quote_sales'],
                    'type' => 'currency'
                ];


                $data[$i]['potential_sales'] = [
                    'value' => $data[$i]['salesorder_sales'],
                    'type' => 'currency'
                ];

                $data[$i]['revenue'] = [
                    'value' => $data[$i]['revenue'],
                    'type' => 'currency'
                ];

                $data[$i]['cost'] = [
                    'value' => $data[$i]['cost'],
                    'type' => 'currency'
                ];
            }
        }

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportHeaders = $this->getReportHeaders();
        $reportData = $this->getReportData($params);

        $currentReportConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        if (isset($currentReportConfig['sales_forecast']) && !empty($currentReportConfig['sales_forecast']['min_successful_percentage'])) {
            $predictedPercentage = $currentReportConfig['sales_forecast']['min_successful_percentage'];
        }
        else {
            $predictedPercentage = 80;
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('REPORT_HEADERS', $reportHeaders);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('PREDICTED_PERCENTAGE', $predictedPercentage);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SummaryCampaignDetailsResultReport/SummaryCampaignDetailsResultReport.tpl');
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