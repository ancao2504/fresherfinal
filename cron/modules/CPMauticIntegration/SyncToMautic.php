<?php

// Added by Phuc Lu on 2019.06.27 
// Run this query to register the scheduler: INSERT INTO `vtiger_cron_task` (`id`, `name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES ('9', 'SyncToMauticTask', 'cron/modules/CPMauticIntegration/SyncToMautic.php', '300', NULL, NULL, '1', 'CPMauticIntegration', '9', 'Recommended frequency for task SyncToMautic is 5 mins');

vimport('includes.runtime.Globals');
include_once('modules/CPMauticIntegration/models/Service.php');
CPMauticIntegration_Service_Model::syncToMautic();