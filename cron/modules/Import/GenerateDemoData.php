<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/


$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
$VTIGER_BULK_SAVE_MODE = true;

require_once  'includes/Loader.php';
require_once 'includes/runtime/Controller.php';
require_once 'includes/runtime/BaseModel.php';
require_once 'includes/runtime/Globals.php';
require_once 'include/fields/DateTimeField.php';


global $generateDemoData, $log;

$db = PearDatabase::getInstance();

foreach($generateDemoData['modules'] as $moduleId){
    $queueFile = 'cron/modules/Import/DemoData/insert_queue_'.$moduleId.'.sql';
    $tableFile = 'cron/modules/Import/DemoData/create_table_'.$moduleId.'.sql';
    $dataFile = 'cron/modules/Import/DemoData/insert_data_'.$moduleId.'.sql';

    //Insert queue
    $sql1 = file_get_contents($queueFile);
    $params = array(
        $db->getUniqueID('vtiger_import_queue'),
        $generateDemoData['userId']
    );
    $result = $db->pquery('DELETE FROM vtiger_import_queue WHERE userid = "'.$generateDemoData['userId'].'"');
    $result = $db->pquery($sql1, $params);


    //create table
    $result = $db->pquery("DROP TABLE IF EXISTS ".'vtiger_import_'.$generateDemoData['userId']);

    $sql2 = file_get_contents($tableFile);
    $sql2 = str_replace('table_name', 'vtiger_import_'.$generateDemoData['userId'], $sql2);
    $result = $db->pquery($sql2);


    //Import data to generate
    $dateTime = new DateTimeField();
    $displayDate = $dateTime->getDisplayDate();
    $dateParts = explode("-", $displayDate);
    $monthYearStr = $dateParts[1]."/".$dateParts[2];
    $monthYearStr = "10/2018";

    $sql3 = file_get_contents($dataFile);
    $sql3 = str_replace('09/2018', $monthYearStr, $sql3);
    $sql3 = str_replace('table_name', 'vtiger_import_'.$generateDemoData['userId'], $sql3);

    $randomUserList = $generateDemoData['userRandom'];

    $pattern = "/user_name/";
    while(preg_match($pattern, $sql3)) {
        $sql3 = preg_replace($pattern, $randomUserList[array_rand($randomUserList)], $sql3, 1);
    }

    $result = $db->pquery($sql3);

    Import_Data_Action::generateDemoData();
} 

$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
