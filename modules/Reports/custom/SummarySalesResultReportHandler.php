<?php

/*
    SummarySalesResultReportHandler.php
    Author: Phuc Lu
    Date: 2020.04.28
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class SummarySalesResultReportHandler extends CustomReportHandler {

    protected $reportFilterTemplate = 'modules/Reports/tpls/SummarySalesResultReport/SummarySalesResultReportFilter.tpl';

    public function renderReportFilter(array $params) {
        $this->reportFilterMeta = [
            'departments' => Reports_CustomReport_Helper::getAllDepartments(),
            'filter_users' => Reports_CustomReport_Helper::getUsersByDepartment($params['department'], false, true),
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
                    'label' => vtranslate('LBL_REPORT_EMPLOYEE', 'Reports'),
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
                        'row'=> 2,
                        'column' => 1
                    ]
                ],
                7 => [
                    'label' => vtranslate('LBL_REPORT_REVENUE', 'Reports'),
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
            ]
        ];

        return $headerRows;
    }

    function getReportData($params, $forExport = false) {
        global $adb;

        if (empty($params['employees'])) {
            return [];
        }

        // Get employees
        $employees = $params['employees'];
        $departments = $params['departments'];

        if (in_array('0', $employees)) {
            $employees = Reports_CustomReport_Helper::getUsersByDepartment($departments, false, false);
            $employees = array_keys($employees);
        }

        $period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);

        $employeeIds = implode("', '", $employees);
        $fullNameField = getSqlForNameInDisplayFormat(['first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'], 'Users');

        $sql = "SELECT id, {$fullNameField} AS user_full_name FROM vtiger_users WHERE id IN ('{$employeeIds}')";
        $result = $adb->pquery($sql, []);
        $data = [];
        $no = 0;

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['id']] = [
                'id' => (!$forExport ? $row['id'] : ++$no),
                'user_full_name' => trim($row['user_full_name']),
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
                'sales' => 0,
                'revenue' => 0,
            ];

            if (!$forExport) {
                $commonConditions = [[
                    ['main_owner_id', 'e', $row['id']],
                    ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
                ]];

                $data[$row['id']] = array_merge($data[$row['id']], [
                    'lead_total_link' => getListViewLinkWithSearchParams('Leads', $commonConditions),
                    'lead_converted_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Converted']])]),
                    'lead_taking_care_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'n', 'Converted,Lost Lead']])]),
                    'lead_pending_link' => getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Lost Lead']])]),
                    'potential_total_link' => getListViewLinkWithSearchParams('Potentials', $commonConditions),
                    'potential_won_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', 'Closed Won']])]),
                    'potential_taking_care_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', '']])]),
                    'potential_lost_link' => getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', 'Closed Lost']])]),
                    'quote_total_link' => getListViewLinkWithSearchParams('Quotes', $commonConditions),
                    'quote_confirmed_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Approved,Accepted']])]),
                    'quote_not_confirmed_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'n', 'Approved,Accepted,Rejected']])]),
                    'quote_cancelled_link' => getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Rejected']])]),
                    'salesorder_total_link' => getListViewLinkWithSearchParams('SalesOrder', $commonConditions),
                    'salesorder_confirmed_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'c', 'Approved,Delivered,Partial payment,Full payment']])]),
                    'salesorder_not_confirmed_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'c', 'Created,Waiting for approve']])]),
                    'salesorder_cancelled_link' => getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'e', 'Cancelled']])]),
                ]);
            }
        }

        // For all
        $data['all'] = current($data);
        $data['all']['id'] = (!$forExport ? 'all' : '');
        $data['all']['user_full_name'] = vtranslate('LBL_REPORT_TOTAL', 'Reports');

        // Generate link for all
        if (!$forExport) {
            $commonConditions = [[
                ['createdtime', 'bw', $period['from_date_for_filter'] . ',' . $period['to_date_for_filter']]
            ]];

            $data['all']['lead_total_link'] = getListViewLinkWithSearchParams('Leads', $commonConditions);
            $data['all']['lead_converted_link'] = getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Converted']])]);
            $data['all']['lead_taking_care_link'] = getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'n', 'Converted,Lost Lead']])]);
            $data['all']['lead_pending_link'] = getListViewLinkWithSearchParams('Leads', [array_merge($commonConditions[0], [['leadstatus', 'e', 'Lost Lead']])]);
            $data['all']['potential_total_link'] = getListViewLinkWithSearchParams('Potentials', $commonConditions);
            $data['all']['potential_won_link'] = getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', 'Closed Won']])]);
            $data['all']['potential_taking_care_link'] = getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', '']])]);
            $data['all']['potential_lost_link'] = getListViewLinkWithSearchParams('Potentials', [array_merge($commonConditions[0], [['potentialresult', 'e', 'Closed Lost']])]);
            $data['all']['quote_total_link'] = getListViewLinkWithSearchParams('Quotes', $commonConditions);
            $data['all']['quote_confirmed_link'] = getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Approved,Accepted']])]);
            $data['all']['quote_not_confirmed_link'] = getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'n', 'Approved,Accepted,Rejected']])]);
            $data['all']['quote_cancelled_link'] = getListViewLinkWithSearchParams('Quotes', [array_merge($commonConditions[0], [['quotestage', 'e', 'Rejected']])]);
            $data['all']['salesorder_total_link'] = getListViewLinkWithSearchParams('SalesOrder', $commonConditions);
            $data['all']['salesorder_confirmed_link'] = getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'c', 'Approved,Delivered,Partial payment,Full payment']])]);
            $data['all']['salesorder_not_confirmed_link'] = getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'c', 'Created,Waiting for approve']])]);
            $data['all']['salesorder_cancelled_link'] = getListViewLinkWithSearchParams('SalesOrder', [array_merge($commonConditions[0], [['sostatus', 'e', 'Waiting for approve']])]);
        }

        // Get leads data
        $sql = "SELECT main_owner_id, count(leadid) AS lead_total,
            SUM(CASE WHEN leadstatus = 'Converted' THEN 1 ELSE 0 END) AS lead_converted, SUM(CASE WHEN leadstatus = 'Lost Lead' THEN 1 ELSE 0 END) AS lead_pending
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (crmid = leadid AND deleted = 0)
            WHERE main_owner_id IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['lead_total'] = (int)$row['lead_total'];
            $data[$row['main_owner_id']]['lead_pending'] = (int)$row['lead_pending'];
            $data[$row['main_owner_id']]['lead_converted'] = (int)$row['lead_converted'];
            $data[$row['main_owner_id']]['lead_taking_care'] = $row['lead_total'] - $row['lead_converted'] - $row['lead_pending'];

            $data['all']['lead_total'] += (int)$row['lead_total'];
            $data['all']['lead_pending'] += (int)$row['lead_pending'];
            $data['all']['lead_converted'] += (int)$row['lead_converted'];
            $data['all']['lead_taking_care'] += $row['lead_total'] - $row['lead_converted'] - $row['lead_pending'];
        }

        // Get potentials data
        $sql = "SELECT main_owner_id, count(potentialid) AS potential_total,
            SUM(CASE WHEN potentialresult = 'Closed Won' THEN 1 ELSE 0 END) AS potential_won, SUM(CASE WHEN potentialresult = 'Closed Lost' THEN 1 ELSE 0 END) AS potential_lost
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (crmid = potentialid AND deleted = 0)
            WHERE main_owner_id IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['potential_total'] = (int)$row['potential_total'];
            $data[$row['main_owner_id']]['potential_lost'] = (int)$row['potential_lost'];
            $data[$row['main_owner_id']]['potential_won'] = (int)$row['potential_won'];
            $data[$row['main_owner_id']]['potential_taking_care'] = $row['potential_total'] - $row['potential_won'] - $row['potential_lost'];

            $data['all']['potential_total'] += (int)$row['potential_total'];
            $data['all']['potential_lost'] += (int)$row['potential_lost'];
            $data['all']['potential_won'] += (int)$row['potential_won'];
            $data['all']['potential_taking_care'] += $row['potential_total'] - $row['potential_won'] - $row['potential_lost'];
        }

        // Get quote data
        $sql = "SELECT main_owner_id, count(quoteid) AS quote_total,
            SUM(CASE WHEN quotestage IN ('Approved', 'Accepted') THEN 1 ELSE 0 END) AS quote_confirmed, SUM(CASE WHEN quotestage IN ('Rejected') THEN 1 ELSE 0 END) AS quote_cancelled
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON (crmid = quoteid AND deleted = 0)
            WHERE main_owner_id IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['quote_total'] = (int)$row['quote_total'];
            $data[$row['main_owner_id']]['quote_cancelled'] = (int)$row['quote_cancelled'];
            $data[$row['main_owner_id']]['quote_confirmed'] = (int)$row['quote_confirmed'];
            $data[$row['main_owner_id']]['quote_not_confirmed'] = $row['quote_total'] - $row['quote_confirmed'] - $row['quote_cancelled'];

            $data['all']['quote_total'] += (int)$row['quote_total'];
            $data['all']['quote_cancelled'] += (int)$row['quote_cancelled'];
            $data['all']['quote_confirmed'] += (int)$row['quote_confirmed'];
            $data['all']['quote_not_confirmed'] += $row['quote_total'] - $row['quote_confirmed'] - $row['quote_cancelled'];
        }

        // Get sales order data
        $sql = "SELECT main_owner_id, count(salesorderid) AS salesorder_total,
            SUM(CASE WHEN sostatus IN ('Approved', 'Delivered', 'Partial payment', 'Full payment') THEN 1 ELSE 0 END) AS salesorder_confirmed, SUM(CASE WHEN sostatus IN ('Cancelled') THEN 1 ELSE 0 END) AS salesorder_cancelled
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (crmid = salesorderid AND deleted = 0)
            WHERE main_owner_id IN ('{$employeeIds}') AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['salesorder_total'] = (int)$row['salesorder_total'];
            $data[$row['main_owner_id']]['salesorder_cancelled'] = (int)$row['salesorder_cancelled'];
            $data[$row['main_owner_id']]['salesorder_confirmed'] = (int)$row['salesorder_confirmed'];
            $data[$row['main_owner_id']]['salesorder_not_confirmed'] = $row['salesorder_total'] - $row['salesorder_confirmed'] - $row['salesorder_cancelled'];

            $data['all']['salesorder_total'] += (int)$row['salesorder_total'];
            $data['all']['salesorder_cancelled'] += (int)$row['salesorder_cancelled'];
            $data['all']['salesorder_confirmed'] += (int)$row['salesorder_confirmed'];
            $data['all']['salesorder_not_confirmed'] += $row['salesorder_total'] - $row['salesorder_confirmed'] - $row['salesorder_cancelled'];
        }

        // Get sales
        $sql = "SELECT main_owner_id, SUM(vtiger_salesorder.total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = 0)
            INNER JOIN vtiger_users ON (vtiger_crmentity.main_owner_id = vtiger_users.id)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND main_owner_id IN ('{$employeeIds}')
                AND createdtime BETWEEN '{$period['from_date']}' AND '{$period['to_date']}'
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['sales'] = $row['sales'];
            $data['all']['sales'] += $row['sales'];
        }

        // Get revenue
        $sql = "SELECT main_owner_id, SUM(amount_vnd) AS revenue
            FROM (
                SELECT DISTINCT salesorderid, cpreceiptid, amount_vnd, main_owner_id
                FROM (
                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, salesorder_crmentity.main_owner_id
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.related_salesorder = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND salesorder_crmentity.main_owner_id IN ('{$employeeIds}')
                        AND vtiger_cpreceipt.cpreceipt_category = 'sales'
                        AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')

                    UNION ALL

                    SELECT vtiger_salesorder.salesorderid, vtiger_cpreceipt.cpreceiptid, vtiger_cpreceipt.amount_vnd, salesorder_crmentity.main_owner_id
                    FROM vtiger_salesorder
                    INNER JOIN vtiger_crmentity AS salesorder_crmentity ON (salesorderid = salesorder_crmentity.crmid AND salesorder_crmentity.deleted = 0)
                    INNER JOIN vtiger_users ON (salesorder_crmentity.main_owner_id = vtiger_users.id)
                    INNER JOIN vtiger_invoice ON (vtiger_invoice.salesorderid = vtiger_salesorder.salesorderid)
                    INNER JOIN vtiger_crmentity AS invoice_crmentity ON (invoice_crmentity.crmid = vtiger_invoice.invoiceid AND invoice_crmentity.deleted = 0)
                    INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relmodule = 'Invoice' AND vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid)
                    INNER JOIN vtiger_cpreceipt ON (vtiger_cpreceipt.cpreceiptid = vtiger_crmentityrel.crmid AND vtiger_crmentityrel.module = 'CPReceipt')
                    INNER JOIN vtiger_crmentity AS receipt_crmentity ON (receipt_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND receipt_crmentity.deleted = 0)
                    WHERE sostatus NOT IN ('Created', 'Cancelled') AND vtiger_cpreceipt.cpreceipt_status = 'completed' AND salesorder_crmentity.main_owner_id IN ('{$employeeIds}')
                        AND vtiger_cpreceipt.cpreceipt_category = 'sales'
                        AND vtiger_cpreceipt.paid_date BETWEEN DATE('{$period['from_date']}') AND DATE('{$period['to_date']}')
                ) AS temp1
            ) AS temp2
            GROUP BY main_owner_id";

        $result = $adb->pquery($sql, []);

        while ($row = $adb->fetchByAssoc($result)) {
            $data[$row['main_owner_id']]['revenue'] = $row['revenue'];
            $data['all']['revenue'] += $row['revenue'];
        }

        return array_values($data);
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $params = $this->getFilterParams();

        $reportFilter = $this->renderReportFilter($params);
        $reportData = $this->getReportData($params);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('REPORT_FILTER', $reportFilter);
        $viewer->assign('REPORT_DATA', $reportData);
        $viewer->assign('PARAMS', $params);
        $viewer->assign('REPORT_ID', $this->reportid);

        $viewer->display('modules/Reports/tpls/SummarySalesResultReport/SummarySalesResultReport.tpl');
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $filters = $request->get('advanced_filter');
        $params = [];

        foreach ($filters as $filter) {
            $params[$filter['name']] = $filter['value'];
        }

        $reportData = $this->getReportData($params, true);

        // Format
        for ($i = 0; $i < count($reportData); $i++) {
            $reportData[$i]['sales'] = [
                'value' => $reportData[$i]['sales'],
                'type' => 'currency'
            ];

            $reportData[$i]['revenue'] = [
                'value' => $reportData[$i]['revenue'],
                'type' => 'currency'
            ];
        }

        CustomReportUtils::writeReportToExcelFile($this, $reportData, $tempFileName, $advanceFilterSql);
    }
}