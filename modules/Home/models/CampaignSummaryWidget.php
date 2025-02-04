<?php

/**
 * Name: CampaignSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_CampaignSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

    var $column = 6;

    function getDefaultParams() {
        $defaultParams = [
            'period' => 'month',
        ];

        return $defaultParams;
    }

    public function getWidgetHeaders($params) {
        $widgetHeaders = [
            [
                'name' => 'budgetcost',
                'label' => vtranslate('LBL_DASHBOARD_BUDGET_COST', 'Home'),
            ],
            [
                'name' => 'actualcost',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_COST', 'Home'),
            ],
            [
                'name' => 'sales',
                'label' => vtranslate('LBL_DASHBOARD_SALES', 'Home'),
            ],
            [
                'name' => 'actual_revenue',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_REVENUE', 'Home'),
            ],
            [
                'name' => 'expectedroi',
                'label' => vtranslate('LBL_DASHBOARD_EXPECTED_ROI', 'Home'),
            ],
            [
                'name' => 'actualroi',
                'label' => vtranslate('LBL_DASHBOARD_ACTUAL_ROI', 'Home'),
            ],
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
                'name' => 'new_account',
                'label' => vtranslate('LBL_DASHBOARD_NEW_ACCOUNTS', 'Home'),
            ],
            [
                'name' => 'new_sales_order',
                'label' => vtranslate('LBL_DASHBOARD_CREATED_SALES_ORDERS', 'Home'),
            ],
            [
                'name' => 'new_contract',
                'label' => vtranslate('LBL_DASHBOARD_CREATED_CONTRACTS', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $data['budgetcost'] = [];
        $data['actualcost'] = [];
        $data['sales'] = [];
        $data['actual_revenue'] = [];
        $data['expectedroi'] = [];
        $data['actualroi'] = [];
        $data['new_lead'] = [];
        $data['new_potential'] = [];
        $data['new_contact'] = [];
        $data['new_account'] = [];
        $data['new_sales_order'] = [];
        $data['new_contract'] = [];

        $sql = "SELECT
                SUM(vtiger_campaign.budgetcost) AS budgetcost,
                SUM(vtiger_campaign.expectedrevenue) AS expectedrevenue,
                SUM(vtiger_campaign.actualcost) AS actualcost,
                SUM(sales_table.sales) AS sales,
                SUM(vtiger_campaign.actual_revenue) AS actual_revenue,
                SUM(lead_table.count) AS new_lead,
                SUM(potential_table.count) AS new_potential,
                SUM(contact_table.count) AS new_contact,
                SUM(account_table.count) AS new_account,
                SUM(sales_table.count) AS new_sales_order,
                SUM(contract_table.count) AS new_contract
            FROM vtiger_campaign
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_campaign.campaignid
                AND vtiger_crmentity.setype = 'Campaigns'
                AND vtiger_crmentity.deleted = 0
            )
            LEFT JOIN (
                SELECT vtiger_salesorder.related_campaign, SUM(vtiger_salesorder.total) AS sales, COUNT(vtiger_salesorder.salesorderid) AS count
                FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity ON (vtiger_salesorder.salesorderid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'SalesOrder' AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_salesorder.related_campaign <> '' AND vtiger_salesorder.related_campaign IS NOT NULL
                GROUP BY vtiger_salesorder.related_campaign
            ) AS sales_table ON (sales_table.related_campaign = vtiger_campaign.campaignid)
            LEFT JOIN (
                SELECT vtiger_leaddetails.related_campaign, COUNT(vtiger_leaddetails.leadid) as count
                FROM vtiger_leaddetails
                INNER JOIN vtiger_crmentity ON (vtiger_leaddetails.leadid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Leads' AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_leaddetails.related_campaign <> '' AND vtiger_leaddetails.related_campaign IS NOT NULL
                GROUP BY vtiger_leaddetails.related_campaign
            ) AS lead_table ON (lead_table.related_campaign = vtiger_campaign.campaignid)
            LEFT JOIN (
                SELECT vtiger_potential.campaignid, COUNT(vtiger_potential.potentialid) AS count
                FROM vtiger_potential
                INNER JOIN vtiger_crmentity ON (vtiger_potential.potentialid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Potentials' AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_potential.campaignid <> '' AND vtiger_potential.campaignid IS NOT NULL
                GROUP BY vtiger_potential.campaignid
            ) AS potential_table ON (potential_table.campaignid = vtiger_campaign.campaignid)
            LEFT JOIN (
                SELECT vtiger_contactdetails.related_campaign, COUNT(vtiger_contactdetails.contactid) AS count
                FROM vtiger_contactdetails
                INNER JOIN vtiger_crmentity ON (vtiger_contactdetails.contactid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Contacts' AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_contactdetails.related_campaign <> '' AND vtiger_contactdetails.related_campaign IS NOT NULL
                GROUP BY vtiger_contactdetails.related_campaign
            ) AS contact_table ON (contact_table.related_campaign = vtiger_campaign.campaignid)
            LEFT JOIN (
                SELECT vtiger_account.related_campaign, COUNT(vtiger_account.accountid) AS count
                FROM vtiger_account
                INNER JOIN vtiger_crmentity ON (vtiger_account.accountid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'Accounts' AND vtiger_crmentity.deleted = 0)
                WHERE vtiger_account.related_campaign <> '' AND vtiger_account.related_campaign IS NOT NULL
                GROUP BY vtiger_account.related_campaign
            ) AS account_table ON (account_table.related_campaign = vtiger_campaign.campaignid)
            LEFT JOIN (
                SELECT
                    CASE
                        WHEN vtiger_contactdetails.contactid <> '' THEN vtiger_contactdetails.related_campaign
                        WHEN vtiger_account.accountid <> '' THEN vtiger_account.related_campaign
                    END AS parent,
                    COUNT(vtiger_servicecontracts.servicecontractsid) AS count
                FROM vtiger_servicecontracts
                INNER JOIN vtiger_crmentity ON (vtiger_crmentity.crmid = vtiger_servicecontracts.servicecontractsid AND vtiger_crmentity.setype = 'ServiceContracts' AND vtiger_crmentity.deleted = 0)
                LEFT JOIN vtiger_contactdetails ON (vtiger_contactdetails.contactid = vtiger_servicecontracts.sc_related_to)
                LEFT JOIN vtiger_account ON (vtiger_account.accountid = vtiger_servicecontracts.sc_related_to)
                GROUP BY CASE
                    WHEN vtiger_contactdetails.contactid <> '' THEN vtiger_contactdetails.related_campaign
                    WHEN vtiger_account.accountid <> '' THEN vtiger_account.related_campaign
                END
            ) AS contract_table ON (contract_table.parent = vtiger_campaign.campaignid)
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_campaign.campaignstatus <> 'Planning'";

        $result = $adb->pquery($sql);
        $result = $adb->fetchByAssoc($result);

        $data['budgetcost']['value'] = $this->formatNumberToUser($result['budgetcost']);
        $data['actualcost']['value'] = $this->formatNumberToUser($result['actualcost']);
        $data['sales']['value'] = $this->formatNumberToUser($result['sales']);
        $data['actual_revenue']['value'] = $this->formatNumberToUser($result['actual_revenue']);
        $data['expectedroi']['value'] = $this->calcRoi($result['budgetcost'], $result['expectedrevenue']);
        $data['actualroi']['value'] = $this->calcRoi($result['actualcost'], $result['actual_revenue']);
        $data['new_lead']['value'] = $result['new_lead'] ?? 0;
        $data['new_potential']['value'] = $result['new_potential'] ?? 0;
        $data['new_contact']['value'] = $result['new_contact'] ?? 0;
        $data['new_account']['value'] = $result['new_account'] ?? 0;
        $data['new_sales_order']['value'] = $result['new_sales_order'] ?? 0;
        $data['new_contract']['value'] = $result['new_contract'] ?? 0;

        return $data;
    }
}