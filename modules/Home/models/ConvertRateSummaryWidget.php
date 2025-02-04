<?php

/**
 * Name: ConvertRateSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_ConvertRateSummaryWidget_Model extends Home_BaseSummaryCustomDashboard_Model {

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
                'name' => 'converted_lead',
                'label' => vtranslate('LBL_DASHBOARD_CONVERTED_ON_LEAD', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_CONVERTED_ON_LEAD_DESCRIPTION', 'Home'),
            ],
            [
                'name' => 'potential_lead',
                'label' => vtranslate('LBL_DASHBOARD_POTENTIAL_ON_LEAD', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_POTENTIAL_ON_LEAD_DESCRIPTION', 'Home'),
            ],
            [
                'name' => 'close_won_potential',
                'label' => vtranslate('LBL_DASHBOARD_CLOSE_WON_ON_POTENTIALS', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_CLOSE_WON_ON_POTENTIALS_DESCRIPTION', 'Home'),
            ],
            [
                'name' => 'converted_rate',
                'label' => vtranslate('LBL_DASHBOARD_CONVERTED_RATE', 'Home'),
                'tooltip' => vtranslate('LBL_DASHBOARD_CONVERTED_RATE_DESCRIPTION', 'Home'),
            ],
        ];

        return $widgetHeaders;
    }

    public function getWidgetData($params) {
        global $adb;

        $data = [];
        $periodFilterInfo = Reports_CustomReport_Helper::getPeriodFromFilter($params);
        $subDay = $this->periodToAddUnitMapping($params['period']);
        $data['converted_lead'] = [];
        $data['potential_lead'] = [];
        $data['close_won_potential'] = [];
        $data['converted_rate'] = [];

        // New Lead
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_leaddetails.leadid
                AND vtiger_crmentity.setype = 'Leads'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
        ";
        $thisPeriodNewLeads = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_leaddetails.leadid
                AND vtiger_crmentity.setype = 'Leads'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
        ";
        $lastPeriodNewLeads = $adb->getOne($lastPeriodSql);

        // New Potentials
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
        ";
        $thisPeriodNewPotentials = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
        ";
        $lastPeriodNewPotentials = $adb->getOne($lastPeriodSql);

        // Get data and calculate convert rate
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_leaddetails.leadid
                AND vtiger_crmentity.setype = 'Leads'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_leaddetails.leadstatus = 'Converted'
        ";
        $thisPeriodConvertedLeads = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_leaddetails
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_leaddetails.leadid
                AND vtiger_crmentity.setype = 'Leads'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_leaddetails.leadstatus = 'Converted'
        ";
        $lastPeriodConvertedLeads = $adb->getOne($lastPeriodSql);
        
        $data['converted_lead']['value'] = $thisPeriodNewLeads > 0 ? $thisPeriodConvertedLeads / $thisPeriodNewLeads : 0;
        $data['converted_lead']['last_period'] = $lastPeriodNewLeads > 0 ? $lastPeriodConvertedLeads / $lastPeriodNewLeads : 0;
        $data['converted_lead']['change'] = $this->getPeriodChange($data['converted_lead']['value'], $data['converted_lead']['last_period']);
        $data['converted_lead']['direction'] = $this->resolveDirection($data['converted_lead']['value'], $data['converted_lead']['last_period']);

        // Potentials Leads

        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.isconvertedfromlead = 1
        ";
        $thisPeriodPotentials = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.isconvertedfromlead = 1
        ";
        $lastPeriodPotentials = $adb->getOne($lastPeriodSql);
        
        $data['potential_lead']['value'] = $thisPeriodNewLeads > 0 ? $thisPeriodPotentials / $thisPeriodNewLeads : 0;
        $data['potential_lead']['last_period'] = $lastPeriodNewLeads > 0 ? $lastPeriodPotentials / $lastPeriodNewLeads : 0;
        $data['potential_lead']['change'] = $this->getPeriodChange($data['potential_lead']['value'], $data['potential_lead']['last_period']);
        $data['potential_lead']['direction'] = $this->resolveDirection($data['potential_lead']['value'], $data['potential_lead']['last_period']);

        // Close Won Potentials
        $thisPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE('{$periodFilterInfo['from_date']}')
                AND DATE(vtiger_crmentity.createdtime) <= DATE('{$periodFilterInfo['to_date']}')
                AND vtiger_potential.potentialresult = 'Closed Won'
        ";
        $thisPeriodCloseWonPotentials = $adb->getOne($thisPeriodSql);

        $lastPeriodSql = "SELECT COUNT(vtiger_crmentity.crmid)
            FROM vtiger_potential
            INNER JOIN vtiger_crmentity ON (
                vtiger_crmentity.crmid = vtiger_potential.potentialid
                AND vtiger_crmentity.setype = 'Potentials'
                AND vtiger_crmentity.deleted = 0
            )
            WHERE
                DATE(vtiger_crmentity.createdtime) >= DATE_SUB(DATE('{$periodFilterInfo['from_date']}'), INTERVAL 1 {$subDay})
                AND DATE(vtiger_crmentity.createdtime) <= DATE_SUB(DATE('{$periodFilterInfo['to_date']}'), INTERVAL 1 {$subDay})
                AND vtiger_potential.potentialresult = 'Closed Won'
        ";
        $lastPeriodCloseWonPotentials = $adb->getOne($lastPeriodSql);
        
        $data['close_won_potential']['value'] = $thisPeriodNewPotentials > 0 ? $thisPeriodCloseWonPotentials / $thisPeriodNewPotentials : 0;
        $data['close_won_potential']['last_period'] = $lastPeriodNewPotentials > 0 ? $lastPeriodCloseWonPotentials / $lastPeriodNewPotentials : 0;
        $data['close_won_potential']['change'] = $this->getPeriodChange($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);
        $data['close_won_potential']['direction'] = $this->resolveDirection($data['close_won_potential']['value'], $data['close_won_potential']['last_period']);
        
        // Summary convert rate
        $data['converted_rate']['value'] = $thisPeriodNewLeads > 0 ? $thisPeriodCloseWonPotentials / $thisPeriodNewLeads : 0;
        $data['converted_rate']['last_period'] = $lastPeriodNewLeads > 0 ? $lastPeriodCloseWonPotentials / $lastPeriodNewLeads : 0;
        $data['converted_rate']['change'] = $this->getPeriodChange($data['converted_rate']['value'], $data['converted_rate']['last_period']);
        $data['converted_rate']['direction'] = $this->resolveDirection($data['converted_rate']['value'], $data['converted_rate']['last_period']);

        // Format data
        $data['converted_lead']['value'] = round($data['converted_lead']['value'], 2) * 100 . '%';
        $data['converted_lead']['last_period'] = round($data['converted_lead']['last_period'], 2) * 100 . '%';
        $data['potential_lead']['value'] = round($data['potential_lead']['value'], 2) * 100 . '%';
        $data['potential_lead']['last_period'] = round($data['potential_lead']['last_period'], 2) * 100 . '%';
        $data['close_won_potential']['value'] = round($data['close_won_potential']['value'], 2) * 100 . '%';
        $data['close_won_potential']['last_period'] = round($data['close_won_potential']['last_period'], 2) * 100 . '%';
        $data['converted_rate']['value'] = round($data['converted_rate']['value'], 2) * 100 . '%';
        $data['converted_rate']['last_period'] = round($data['converted_rate']['last_period'], 2) * 100 . '%';

        return $data;
    }
}