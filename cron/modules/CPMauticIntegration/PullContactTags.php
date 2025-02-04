<?php

// Added by Phuc Lu on 2020.05.06
// Run this query to register the scheduler: INSERT INTO vtiger_cron_task (name, handler_file, frequency, laststart, lastend, status, module, sequence, description) VALUES ('PullMauticContactTags', 'cron/modules/CPMauticIntegration/PullContactTags.php', '900', '0', '0', '1', 'CPMauticIntegration', '12', 'Recommended frequency for task PullMauticContactTags is 15 mins');

vimport('includes.runtime.Globals');
include_once('modules/CPMauticIntegration/models/Service.php');
CPMauticIntegration_Service_Model::pullContactTags();