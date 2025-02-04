<?php

/*
    Class Vtiger_Service_Model
    Author: Hieu Nguyen
    Date: 2019-10-10
    Purpose: handle background tasks for Vtiger core
*/

class Vtiger_Service_Model extends Vtiger_Base_Model {

    static function processEmailQueue() {
        require_once('vtlib/Vtiger/Mailer.php');
        $log = LoggerManager::getLogger('PLATFORM'); 
        $log->info('[CRON] Started processEmailQueue');

        try {
            Vtiger_Mailer::dispatchQueue();
            $log->info('[CRON] Finished processEmailQueue');
        }
        catch (Exception $ex) {
            $log->info('[CRON] processEmailQueue error: ' . $ex->getMessage());
        }
    }
}