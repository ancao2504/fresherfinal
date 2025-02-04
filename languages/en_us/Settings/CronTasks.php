<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
$languageStrings = array(
	'CronTasks' => 'Scheduler',

	//Basic Field Names
	'Id' => 'Id',
	'Cron Job' => 'Service Name',
	'Frequency' => 'Frequency',
	'Status' => 'Status',
	'Last Start' => 'Last scan started',
	'Last End' => 'Last scan ended',
	'Sequence' => 'Sequence',

	//Actions
	'LBL_COMPLETED' => 'Completed',
	'LBL_RUNNING' => 'Running',
	'LBL_ACTIVE' => 'Active',
	'LBL_INACTIVE' => 'In Active',

	// Added by Hieu Nguyen on 2018-08-18
    'Frequency(H:M)' => 'Frequency(H:M)',
	'Recommended frequency for RecurringInvoice is 12 hours' => 'Recommended frequency for RecurringInvoice is 12 hours',
	'Recommended frequency for Workflow is 15 mins' => 'Recommended frequency for Workflow is 15 mins',
	'Recommended frequency for SendReminder is 15 mins' => 'Recommended frequency for SendReminder is 15 mins',
	'Recommended frequency for MailScanner is 15 mins' => 'Recommended frequency for MailScanner is 15 mins',
	'Recommended frequency for ScheduleImport is 15 mins' => 'Recommended frequency for ScheduleImport is 15 mins',
	'Recommended frequency for ScheduleReports is 15 mins' => 'Recommended frequency for ScheduleReports is 15 mins',
	'LBL_BTN_RESET_SERVICE_TITLE' => 'Reset Service',
	'LBL_BTN_TEST_SERVICE_TITLE' => 'Test Service',
	// End Hieu Nguyen
);

$jsLanguageStrings = array(
    // Added by Hieu Nguyen on 2018-08-18
	'JS_RESET_SERVICE_CONFIRM_MSG' => "This action will reset this Service so that it can run again from begining.\nCaution: use this action only when the Service is stuck, or you will face with the issue of re-running logic!",
	'JS_RESET_SERVICE_SUCCESS_MSG' => 'Reset Service successfully!',
	'JS_RESET_SERVICE_ERROR_MSG' => 'Can not reset this Service. Please try again!',
    'JS_TEST_SERVICE_CONFIRM_MSG' => 'Are you sure to run test this Service right now?',
	'JS_TEST_SERVICE_SUCCESS_MSG' => 'Run test Service succesfully!',
	'JS_TEST_SERVICE_ERROR_MSG' => 'Can not run test this Service. Please try again!',
    // End Hieu Nguyen
);
