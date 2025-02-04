<?php

/**
 * BaseSummaryCustomDashboard.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

abstract class Home_BaseSummaryCustomDashboard_Model extends Home_BaseCustomDashboard_Model {
    abstract public function getWidgetHeaders($params);

    public $lastPeriod = false;

    public function periodToAddUnitMapping($period) {
        $mapping = [
            'date' => 'DAY',
            'week' => 'WEEK',
            'month' => 'MONTH',
            'quarter' => 'QUARTER',
            'year' => 'YEAR',
        ];

        return $mapping[$period] ?? '';
    }

    public function getPeriodChange($thisPeriod, $lastPeriod) {
        if (empty($thisPeriod)) $thisPeriod = 0;
        if (empty($lastPeriod)) $lastPeriod = 0;

        $thisPeriod = floatval($thisPeriod);
        $lastPeriod = floatval($lastPeriod);

        if (empty($lastPeriod)) return 'N/A';
        $change = ($thisPeriod / $lastPeriod - 1) * 100;
        $change = round($change, 2);
        if ($change < 0) $change = - $change;

        return $change;
    }

    public function resolveDirection($thisPeriod, $lastPeriod) {
        if (empty($thisPeriod)) $thisPeriod = 0;
        if (empty($lastPeriod)) $lastPeriod = 0;

        $thisPeriod = floatval($thisPeriod);
        $lastPeriod = floatval($lastPeriod);

        if ($thisPeriod > $lastPeriod) return "+";
        if ($thisPeriod < $lastPeriod) return "-";

        return '0';
    }

    public function getReportConfig() {
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        // Set default values
        if (empty($currentConfig)) {
            $currentConfig = array(
                'sales_forecast' => [
                    'min_successful_percentage' => 80
                ],
                'customer_groups' =>  [
                    'customer_group_calculate_by' => 'cummulation',
                    'groups' => []
                ]
            );
        }

        return $currentConfig;
    }
}