<?php

/**
 * Name: SalesSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_SalesSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

    public $lastPeriod = true;

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'new_lead',
                'label' => vtranslate('LBL_DASHBOARD_NEW_LEADS', 'Home'),
            ],
            [
                'name' => 'new_potential',
                'label' => vtranslate('LBL_DASHBOARD_NEW_POTENTIALS', 'Home'),
            ],
            [
                'name' => 'new_quote',
                'label' => vtranslate('LBL_DASHBOARD_NEW_QUOTES', 'Home'),
            ],
            [
                'name' => 'new_sales_order',
                'label' => vtranslate('LBL_DASHBOARD_NEW_SALES_ORDERS', 'Home'),
            ],
            [
                'name' => 'potential_sales',
                'label' => vtranslate('LBL_DASHBOARD_POTENTIAL_SALES', 'Home'),
            ],
            [
                'name' => 'expected_sales',
                'label' => vtranslate('LBL_DASHBOARD_EXPECTED_SALES', 'Home'),
                'tooltip' => $this->getExpectedSalesTooltip(),
            ],
            [
                'name' => 'close_won_potential',
                'label' => vtranslate('LBL_DASHBOARD_CLOSE_WON_POTENTIAL', 'Home'),
            ],
            [
                'name' => 'close_lost_potential',
                'label' => vtranslate('LBL_DASHBOARD_CLOSE_LOST_POTENTIAL', 'Home'),
            ],
            [
                'name' => 'sales',
                'label' => vtranslate('LBL_DASHBOARD_SALES', 'Home'),
            ],
            [
                'name' => 'revenue',
                'label' => vtranslate('LBL_DASHBOARD_REVENUE', 'Home'),
            ],
            [
                'name' => 'convert_rate',
                'label' => vtranslate('LBL_DASHBOARD_CONVERTED_RATE', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_SALES_CONVERTED_RATE_DESCRIPTION', 'Home'),
            ],
            [
                'name' => 'close_time_average',
                'label' => vtranslate('LBL_DASHBOARD_CLOSE_TIME_AVERAGE', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_CLOSE_TIME_AVERAGE_DESCRIPTION', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $subDay = $this->periodToAddUnitMapping($params['period']);
        $reportConfig = $this->getReportConfig();
        $minSuccessfulPercentage = $reportConfig['sales_forecast']['min_successful_percentage'];

        $data['new_lead'] = [];
        $data['new_potential'] = [];
        $data['new_quote'] = [];
        $data['new_sales_order'] = [];
        $data['potential_sales'] = [];
        $data['expected_sales'] = [];
        $data['close_won_potential'] = [];
        $data['close_lost_potential'] = [];
        $data['sales'] = [];
        $data['revenue'] = [];
        $data['convert_rate'] = [];
        $data['close_time_average'] = [];

        // New Lead
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $thisPeriodNewLeads = $adb->getOne($thisPeriodSql);
        $data['new_lead']['value'] = $thisPeriodNewLeads;

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $lastPeriodNewLeads = $adb->getOne($lastPeriodSql);
        $data['new_lead']['last_period'] = $lastPeriodNewLeads;
        $data['new_lead']['change'] = $this->getPeriodChange($data['new_lead']['value'], $data['new_lead']['last_period']);
        $data['new_lead']['direction'] = $this->resolveDirection($data['new_lead']['value'], $data['new_lead']['last_period']);

        // New Potentials
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['new_potential']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['new_potential']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['new_potential']['change'] = $this->getPeriodChange($data['new_potential']['value'], $data['new_potential']['last_period']);
        $data['new_potential']['direction'] = $this->resolveDirection($data['new_potential']['value'], $data['new_potential']['last_period']);

        // New Quotes
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_quotes.quoteid AND vtiger_crmentity.setype = 'Quotes' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['new_quote']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_quotes
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_quotes.quoteid AND vtiger_crmentity.setype = 'Quotes' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['new_quote']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['new_quote']['change'] = $this->getPeriodChange($data['new_quote']['value'], $data['new_quote']['last_period']);
        $data['new_quote']['direction'] = $this->resolveDirection($data['new_quote']['value'], $data['new_quote']['last_period']);

        // New Sales Order
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['new_sales_order']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['new_sales_order']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['new_sales_order']['change'] = $this->getPeriodChange($data['new_sales_order']['value'], $data['new_sales_order']['last_period']);
        $data['new_sales_order']['direction'] = $this->resolveDirection($data['new_sales_order']['value'], $data['new_sales_order']['last_period']);

        // Potential Sales
        $thisPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['potential_sales']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['potential_sales']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['potential_sales']['change'] = $this->getPeriodChange($data['potential_sales']['value'], $data['potential_sales']['last_period']);
        $data['potential_sales']['direction'] = $this->resolveDirection($data['potential_sales']['value'], $data['potential_sales']['last_period']);

        // Expected Sales
        $thisPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.probability >= {$minSuccessfulPercentage}";

        $data['expected_sales']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.probability >= {$minSuccessfulPercentage}";

        $data['expected_sales']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['expected_sales']['change'] = $this->getPeriodChange($data['expected_sales']['value'], $data['expected_sales']['last_period']);
        $data['expected_sales']['direction'] = $this->resolveDirection($data['expected_sales']['value'], $data['expected_sales']['last_period']);

        // Close Won Sales
        $thisPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Won'";

        $data['close_won_potential']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Won'";

        $data['close_won_potential']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['close_won_potential']['change'] = $this->getPeriodChange($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);
        $data['close_won_potential']['direction'] = $this->resolveDirection($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);

        // Close Lost Sales
        $thisPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Lost'";

        $data['close_lost_potential']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_potential.amount)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Lost'";

        $data['close_lost_potential']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['close_lost_potential']['change'] = $this->getPeriodChange($data['close_lost_potential']['value'], $data['close_lost_potential']['last_period']);
        $data['close_lost_potential']['direction'] = $this->resolveDirection($data['close_lost_potential']['value'], $data['close_lost_potential']['last_period']);

        // Sales
        $thisPeriodSql = "SELECT SUM(vtiger_salesorder.total)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['sales']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_salesorder.total)
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_salesorder.salesorderid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['sales']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['sales']['change'] = $this->getPeriodChange($data['sales']['value'], $data['sales']['last_period']);
        $data['sales']['direction'] = $this->resolveDirection($data['sales']['value'], $data['sales']['last_period']);

        // Revenue
        $thisPeriodSql = "SELECT SUM(vtiger_cpreceipt.amount_vnd)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_cpreceipt.paid_date) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_cpreceipt.paid_date) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_cpreceipt.cpreceipt_status = 'completed'";

        $data['revenue']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT SUM(vtiger_cpreceipt.amount_vnd)
            FROM vtiger_cpreceipt
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid AND vtiger_crmentity.setype = 'CPReceipt' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_cpreceipt.cpreceipt_status = 'completed'";

        $data['revenue']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['revenue']['change'] = $this->getPeriodChange($data['revenue']['value'], $data['revenue']['last_period']);
        $data['revenue']['direction'] = $this->resolveDirection($data['revenue']['value'], $data['revenue']['last_period']);

        // Convert Rate
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_leaddetails.leadstatus = 'Converted'";

        $thisPeriodConvertedLeads = $adb->getOne($thisPeriodSql);
        $data['convert_rate']['value'] = $thisPeriodNewLeads > 0 ? ($thisPeriodConvertedLeads / $thisPeriodNewLeads) * 100 : 0;

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_leaddetails.leadstatus = 'Converted'";

        $lastPeriodConvertedLeads = $adb->getOne($lastPeriodSql);
        $data['convert_rate']['last_period'] = $lastPeriodNewLeads > 0 ? ($lastPeriodConvertedLeads / $lastPeriodNewLeads) * 100 : 0;
        $data['convert_rate']['change'] = $this->getPeriodChange($data['convert_rate']['value'], $data['convert_rate']['last_period']);
        $data['convert_rate']['direction'] = $this->resolveDirection($data['convert_rate']['value'], $data['convert_rate']['last_period']);

        // Average close time
        $thisPeriodSql = "SELECT AVG(DATEDIFF(DATE(vtiger_potential.closingdate), DATE(vtiger_crmentity.createdtime)))
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Won'
        ";
        $data['close_time_average']['value'] = $adb->getOne($thisPeriodSql) ?? 0;
        $data['close_time_average']['value'] = round($data['close_time_average']['value']);

        $lastPeriodSql = "SELECT AVG(DATEDIFF(DATE(vtiger_potential.closingdate), DATE(vtiger_crmentity.createdtime)))
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_potential.potentialid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Won'";

        $data['close_time_average']['last_period'] = $adb->getOne($lastPeriodSql) ?? 0;
        $data['close_time_average']['last_period'] = round($data['close_time_average']['last_period']);
        $data['close_time_average']['change'] = $this->getPeriodChange($data['close_time_average']['value'], $data['close_time_average']['last_period']);
        $data['close_time_average']['direction'] = $this->resolveDirection($data['close_time_average']['value'], $data['close_time_average']['last_period']);

        // Format data
        $data['potential_sales']['value'] = $this->formatNumberToUser($data['potential_sales']['value']);
        $data['potential_sales']['last_period'] = $this->formatNumberToUser($data['potential_sales']['last_period']);
        $data['expected_sales']['value'] = $this->formatNumberToUser($data['expected_sales']['value']);
        $data['expected_sales']['last_period'] = $this->formatNumberToUser($data['expected_sales']['last_period']);
        $data['close_won_potential']['value'] = $this->formatNumberToUser($data['close_won_potential']['value']);
        $data['close_won_potential']['last_period'] = $this->formatNumberToUser($data['close_won_potential']['last_period']);
        $data['close_lost_potential']['value'] = $this->formatNumberToUser($data['close_lost_potential']['value']);
        $data['close_lost_potential']['last_period'] = $this->formatNumberToUser($data['close_lost_potential']['last_period']);
        $data['sales']['value'] = $this->formatNumberToUser($data['sales']['value']);
        $data['sales']['last_period'] = $this->formatNumberToUser($data['sales']['last_period']);
        $data['revenue']['value'] = $this->formatNumberToUser($data['revenue']['value']);
        $data['revenue']['last_period'] = $this->formatNumberToUser($data['revenue']['last_period']);
        $data['convert_rate']['value'] = round($data['convert_rate']['value'], 2) . '%';
        $data['convert_rate']['last_period'] = round($data['convert_rate']['last_period'], 2) . '%';
        $data['close_time_average']['value'] = $data['close_time_average']['value'] . ' ngày';
        $data['close_time_average']['last_period'] = $data['close_time_average']['last_period'];
        $data['close_time_average']['change'] = round($data['close_time_average']['change']);

        return $data;
    }

    private function getExpectedSalesTooltip() {
        $reportConfig = $this->getReportConfig();
        $minSuccessfulPercentage = $reportConfig['sales_forecast']['min_successful_percentage'];
        $replaceParams = ['%success_rate' => $minSuccessfulPercentage];
        $text = vtranslate('LBL_DASHBOARD_EXPECTED_SALES_DESCRIPTION', 'Home', $replaceParams);

        return $text;
    }
}