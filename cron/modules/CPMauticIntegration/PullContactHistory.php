<?php

// Added by Phuc Lu on 2020.03.04
// Run this query to register the scheduler: INSERT INTO `vtiger_cron_task` (`id`, `name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES ('11', 'PullMauticContactHistory', 'cron/modules/CPMauticIntegration/PullContactHistory.php', '900', '0', '0', '1', 'CPMauticIntegration', '11', 'Recommended frequency for task PullMauticContactHistory is 15 mins');

vimport('includes.runtime.Globals');
include_once('modules/CPMauticIntegration/models/Service.php');
CPMauticIntegration_Service_Model::pullContactHistory();