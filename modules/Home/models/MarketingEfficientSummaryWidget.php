<?php

/**
 * Name: MarketingEfficientSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_MarketingEfficientSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

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
                'label' => vtranslate('LBL_DASHBOARD_GENERATED_LEADS', 'Home'),
            ],
            [
                'name' => 'new_potential',
                'label' => vtranslate('LBL_DASHBOARD_CREATED_POTENTIALS', 'Home'),
            ],
            [
                'name' => 'new_contact',
                'label' => vtranslate('LBL_DASHBOARD_NEW_CONTACTS', 'Home'),
            ],
            [
                'name' => 'new_sales_order',
                'label' => vtranslate('LBL_DASHBOARD_CREATED_SALES_ORDERS', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $subDay = $this->periodToAddUnitMapping($params['period']);
        $data['new_lead'] = [];
        $data['new_potential'] = [];
        $data['new_contact'] = [];
        $data['new_sales_order'] = [];

        // New Lead
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['new_lead']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_leaddetails.leadid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['new_lead']['last_period'] = $adb->getOne($lastPeriodSql);
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

        // New Contacts
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')";

        $data['new_contact']['value'] = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_contactdetails
            INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_contactdetails.contactid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})";

        $data['new_contact']['last_period'] = $adb->getOne($lastPeriodSql);
        $data['new_contact']['change'] = $this->getPeriodChange($data['new_contact']['value'], $data['new_contact']['last_period']);
        $data['new_contact']['direction'] = $this->resolveDirection($data['new_contact']['value'], $data['new_contact']['last_period']);

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

        return $data;
    }
}