<?php

// Added by Phuc Lu on 2020.03.05
// Run this query to register the scheduler: INSERT INTO vtiger_cron_task (name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ('SyncMauticHistoryForConvertedCustomers', 'cron/modules/CPMauticIntegration/SyncMauticHistoryForConvertedCustomers.php', '300', '0', '0', '1', 'CPMauticIntegration', '11', 'Recommended frequency for task SyncMauticHistoryForConvertedCustomers is 5 mins');

vimport('includes.runtime.Globals');
include_once('modules/CPMauticIntegration/models/Service.php');
CPMauticIntegration_Service_Model::syncMauticHistoryForConvertedCustomers();