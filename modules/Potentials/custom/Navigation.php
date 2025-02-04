<?php
    /**
     * Created by PhpStorm.
     * Author: Kelvin Thang
     * Date: 2018-12-10
     * Time: 10:20 AM
     */

    // Added by Phuc on 2020.03.18
    global $adb;
    $record = $this->record->getRecord();
    $sql = "SELECT vtiger_modtracker_detail.postvalue
        FROM vtiger_modtracker_detail INNER JOIN vtiger_modtracker_basic ON (vtiger_modtracker_detail.id = vtiger_modtracker_basic.id)
        WHERE vtiger_modtracker_basic.crmid = ? AND vtiger_modtracker_detail.fieldname = 'sales_stage'
        ORDER BY vtiger_modtracker_basic.changedon";

    $visitedStages = [];
    $result = $adb->pquery($sql, [$record->getId()]);
    
    while ($row = $adb->fetchByAssoc($result)) {
        $visitedStages[] = $row['postvalue'];
    }

    $excludes = [
        'Closed Won',
        'Closed Lost'
    ];

    $salesStagePickList = Vtiger_Util_Helper::getPickListValues('sales_stage');
    $salesStagePickList = array_diff($salesStagePickList, $excludes);
    $visitedStages = array_diff($visitedStages, $excludes);

    $navigationData = array(
        'field_name' => 'sales_stage',
        /*'picklist' => array(
            '0' => 'Prospecting',
            '1' => 'Qualification',
            '2' => 'Needs Analysis',
            '3' => 'Value Proposition',            
            '4' => 'Perception Analysis',
            '5' => 'Proposal or Price Quote',
            '6' => 'Negotiation or Review',
            '7' => 'Closed Won',
        ),*/
        'picklist' => $salesStagePickList, //-- Automatically retrieve data according to Picklist
        'visited' => $visitedStages,
    );
    // Ended by Phuc
