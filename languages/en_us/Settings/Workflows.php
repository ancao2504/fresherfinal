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
	//Basic Field Names
	'LBL_NEW' => 'New',
	'LBL_WORKFLOW' => 'Workflow',
	'LBL_CREATING_WORKFLOW' => 'Creating WorkFlow',
	'LBL_EDITING_WORKFLOW' => 'Editing Workflow',
	'LBL_ADD_RECORD' => 'New Workflow',

	//Edit view
	'LBL_STEP_1' => 'Step 1',
	'LBL_ENTER_BASIC_DETAILS_OF_THE_WORKFLOW' => 'Enter basic details of the Workflow',
	'LBL_SPECIFY_WHEN_TO_EXECUTE' => 'Specify when to execute this Workflow',
	'ON_FIRST_SAVE' => 'Only on the first save',
	'ONCE' => 'Until the first time the condition is true',
	'ON_EVERY_SAVE' => 'Every time the record is saved',
	'ON_MODIFY' => 'Every time a record is modified',
	'ON_SCHEDULE' => 'Schedule',
	'MANUAL' => 'System',
	'SCHEDULE_WORKFLOW' => 'Schedule Workflow',
	'ADD_CONDITIONS' => 'Add Conditions',
	'ADD_TASKS' => 'Add Actions',

	//Step2 edit view
	'LBL_EXPRESSION' => 'Expression',
	'LBL_FIELD_NAME' => 'Field',
	'LBL_SET_VALUE' => 'Set Value',
	'LBL_USE_FIELD' => 'Use Field',
	'LBL_USE_FUNCTION' => 'Use Function',
	'LBL_RAW_TEXT' => 'Raw text',
	'LBL_ENABLE_TO_CREATE_FILTERS' => 'Enable to create Filters',
	'LBL_CREATED_IN_OLD_LOOK_CANNOT_BE_EDITED' => 'This workflow was created in older look. Conditions created in older look cannot be edited. You can choose to recreate the conditions, or use the existing conditions without changing them.',
	'LBL_USE_EXISTING_CONDITIONS' => 'Use existing conditions',
	'LBL_RECREATE_CONDITIONS' => 'Recreate Conditions',
	'LBL_SAVE_AND_CONTINUE' => 'Save & Continue',

	//Step3 edit view
	'LBL_ACTIVE' => 'Active',
	'LBL_TASK_TYPE' => 'Action Type',
	'LBL_TASK_TITLE' => 'Action Title',
	'LBL_ADD_TASKS_FOR_WORKFLOW' => 'Add Action for Workflow',
	'LBL_EXECUTE_TASK' => 'Execute Action',
	'LBL_SELECT_OPTIONS' => 'Select Options',
	'LBL_ADD_FIELD' => 'Add field',
	'LBL_ADD_TIME' => 'Add time',
	'LBL_TITLE' => 'Title',
	'LBL_PRIORITY' => 'Priority',
	'LBL_ASSIGNED_TO' => 'Assigned to',
	'LBL_TIME' => 'Time',
	'LBL_DUE_DATE' => 'Due Date',
	'LBL_THE_SAME_VALUE_IS_USED_FOR_START_DATE' => 'The same value is used for the start date',
	'LBL_EVENT_NAME' => 'Event Name',
	'LBL_TYPE' => 'Type',
	'LBL_METHOD_NAME' => 'Method Name',
	'LBL_RECEPIENTS' => 'Recepients',
	'LBL_ADD_FIELDS' => 'Add Fields',
	'LBL_SMS_TEXT' => 'Sms Text',
	'LBL_SET_FIELD_VALUES' => 'Set Field Values',
	'LBL_ADD_FIELD' => 'Add Field',
	'LBL_IN_ACTIVE' => 'In Active',
	'LBL_SEND_NOTIFICATION' => 'Send Notification',
	'LBL_START_TIME' => 'Start Time',
	'LBL_START_DATE' => 'Start Date',
	'LBL_END_TIME' => 'End Time',
	'LBL_END_DATE' => 'End Date',
	'LBL_ENABLE_REPEAT' => 'Enable Repeat',
	'LBL_NO_METHOD_IS_AVAILABLE_FOR_THIS_MODULE' => 'No method is available for this module',
	
	'LBL_NO_TASKS_ADDED' => 'No Actions',
	'LBL_CANNOT_DELETE_DEFAULT_WORKFLOW' => 'You Cannot delete default Workflow',
	'LBL_MODULES_TO_CREATE_RECORD' => 'Create a record in',
	'LBL_EXAMPLE_EXPRESSION' => 'Expression',
	'LBL_EXAMPLE_RAWTEXT' => 'Rawtext',
	'LBL_VTIGER' => 'Vtiger',
	'LBL_EXAMPLE_FIELD_NAME' => 'Field',
	'LBL_NOTIFY_OWNER' => 'notify_owner',
	'LBL_ANNUAL_REVENUE' => 'annual_revenue',
	'LBL_EXPRESSION_EXAMPLE2' => "if mailingcountry == 'India' then concat(firstname,' ',lastname) else concat(lastname,' ',firstname) end",
	'LBL_FROM' => 'From',
	'LBL_RUN_WORKFLOW' => 'Run Workflow',
	'LBL_AT_TIME' => 'At Time',
	'LBL_HOURLY' => 'Hourly',
	'Optional' => 'Optional',
	'ENTER_FROM_EMAIL_ADDRESS'=> 'Enter a From email address',
	'LBL_ADD_TASK' => 'Add Action',
    'Portal Pdf Url' =>'Portal Pdf Url',

	'LBL_DAILY' => 'Daily',
	'LBL_WEEKLY' => 'Weekly',
	'LBL_ON_THESE_DAYS' => 'On these days',
	'LBL_MONTHLY_BY_DATE' => 'Monthly by Date',
	'LBL_MONTHLY_BY_WEEKDAY' => 'Monthly by Weekday',
	'LBL_YEARLY' => 'Yearly',
	'LBL_SPECIFIC_DATE' => 'On Specific Date',
	'LBL_CHOOSE_DATE' => 'Choose Date',
	'LBL_SELECT_MONTH_AND_DAY' => 'Select Month and Date',
	'LBL_SELECTED_DATES' => 'Selected Dates',
	'LBL_EXCEEDING_MAXIMUM_LIMIT' => 'Maximum limit exceeded',
	'LBL_NEXT_TRIGGER_TIME' => 'Next trigger time on',
    'LBL_ADD_TEMPLATE' => 'Add Template',
    'LBL_LINEITEM_BLOCK_GROUP' => 'LineItems Block For Group Tax',
    'LBL_LINEITEM_BLOCK_INDIVIDUAL' => 'LineItems Block For Individual Tax',
	'LBL_MESSAGE' => 'Message',
    'LBL_ADD_PDF' => 'Add PDF',
	
	//Translation for module
	'Calendar' => 'Task',
	'Send Mail' => 'Send Mail',
	'Invoke Custom Function' => 'Invoke Custom Function',
	'Create Todo' => 'Create Task',
	'Create Event' => 'Create Event',
	'Update Fields' => 'Update Fields',
	'Create Entity' => 'Create Record',
	'SMS Task' => 'SMS Task',
	'Mobile Push Notification' => 'Mobile Push Notification',
    
    // v7 translations
    'LBL_WORKFLOW_NAME' => 'Workflow Name',
    'LBL_TARGET_MODULE' => 'Target Module',
    'LBL_WORKFLOW_TRIGGER' => 'Workflow Trigger',
    'LBL_TRIGGER_WORKFLOW_ON' => 'Trigger Workflow On',
    'LBL_RECORD_CREATION' => 'Record Creation',
    'LBL_RECORD_UPDATE' => 'Record Update',
    'LBL_TIME_INTERVAL' => 'Time Interval',
    'LBL_RECURRENCE' => 'Recurrence',
    'LBL_FIRST_TIME_CONDITION_MET' => 'Only first time conditons are met',
    'LBL_EVERY_TIME_CONDITION_MET' => 'Every time conditons are met',
    'LBL_WORKFLOW_CONDITION' => 'Workflow Condition',
    'LBL_WORKFLOW_ACTIONS' => 'Workflow Actions',
    'LBL_DELAY_ACTION' => 'Delay Action',
    'LBL_FREQUENCY' => 'Frequency',
    'LBL_SELECT_FIELDS' => 'Select Fields',
    'LBL_INCLUDES_CREATION' => 'Includes Creation',
    'LBL_ACTION_FOR_WORKFLOW' => 'Action for Workflow',
    'LBL_WORKFLOW_SEARCH' => 'Search by Name',
	'LBL_ACTION_TYPE' => 'Action Type (Active Count)',
	'LBL_VTEmailTask' => 'Email',
    'LBL_VTEntityMethodTask' => 'Custom Function',
    'LBL_VTCreateTodoTask' => 'Task',
    'LBL_VTCreateEventTask' => 'Event',
    'LBL_VTUpdateFieldsTask' => 'Field Update',
    'LBL_VTSMSTask' => 'SMS', 
    'LBL_VTPushNotificationTask' => 'Mobile Notification',
    'LBL_VTCreateEntityTask' => 'Create Record',
	'LBL_MAX_SCHEDULED_WORKFLOWS_EXCEEDED' => 'Maximum number(%s) of scheduled workflows has been exceeded',

    // Added by Hieu Nguyen on 2020-07-21
    'LBL_SELECT_VALUE' => 'Select Value',
    'LBL_INSERT_VARIABLE' => 'Insert Variable',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-07-21 to support Auto Call Task
    'LBL_VTAutoCallTask' => 'Make Auto Call',
    'LBL_AUTO_CALL_TASK' => 'Make Auto Call',
    'LBL_AUTO_CALL_TASK_PHONE_FIELD' => 'Phone Field to Make Call',
    'LBL_AUTO_CALL_TASK_VARIABLE' => 'Insert Variable',
    'LBL_AUTO_CALL_TASK_TEXT_TO_CALL' => 'Content to Call',
    'LBL_AUTO_CALL_TASK_HANDLE_RESPONSE' => 'Handle Response',
    'LBL_AUTO_CALL_TASK_CONFIRM_KEY' => 'Confirm Key',
    'LBL_AUTO_CALL_TASK_CANCEL_KEY' => 'Cancel Key',
    'LBL_AUTO_CALL_TASK_TARGET_FIELD' => 'Field to Update',
    'LBL_AUTO_CALL_TASK_CONFIRMED_VALUE' => 'Update Value When Confirmed',
    'LBL_AUTO_CALL_TASK_CANCELLED_VALUE' => 'Update Value When Cancelled',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-10-26
    'LBL_ASSIGN_TO_PARENT_RECORED_OWNERS' => 'Parent record owners',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-11-05 to support saving template without purified
    'LBL_SAFE_CONTENT' => 'Confirm this template is safety without XSS',
    'LBL_SAFE_CONTENT_HINT' => 'This template will be saved without XSS filter. Please only tick this checkbox if you know exactly what you are doing!',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-11-23
    'LBL_MESSAGE_PHONE_FIELDS' => 'Phone number fields to send',
    'LBL_MESSAGE_VARIABLE' => 'Insert Variable',
    'LBL_MESSAGE_CONTENT' => 'Content to Send',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-11-23 to support Zalo ZNS Message Task
    'LBL_VTZaloOTTMessageTask' => 'Send Zalo ZNS Message',
    'LBL_ZALO_OTT_MESSAGE_TASK' => 'Send Zalo ZNS Message',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2021-10-28 to support Zalo OA Message Task
    'LBL_VTZaloOAMessageTask' => 'Send Zalo OA Message',
    'LBL_ZALO_OA_MESSAGE_TASK' => 'Send Zalo OA Message',
	'LBL_ZALO_OA_MESSAGE_TASK_SENDER_LIST' => 'Select OA to send',
    'LBL_ZALO_OA_MESSAGE_TASK_SEND_FROM_ALL_OA' => 'Send over all OAs',
    'LBL_ZALO_OA_MESSAGE_TASK_RELATED_CUSTOMER_FIELD' => 'Select field to identify receiver',
    // End Hieu Nguyen

	// Added by Hieu Nguyen on 2021-11-24 to support Add To Marketing List Task
    'LBL_VTAddToMarketingListTask' => 'Add To Marketing List',
    'LBL_ADD_TO_MARKETING_LIST_TASK' => 'Add To Marketing List',
    'LBL_ADD_TO_MARKETING_LIST_TASK_RELATED_CUSTOMER_FIELD' => 'Select field to identify customer',
    'LBL_ADD_TO_MARKETING_LIST_TASK_MARKETING_LIST' => 'Select Marketing List',
    // End Hieu Nguyen

	// Added by Hieu Nguyen on 2021-11-24 to support Assign Customer Tags Task
    'LBL_VTAssignCustomerTagsTask' => 'Assign Customer Tags',
    'LBL_ASSIGN_CUSTOMER_TAGS_TASK' => 'Assign Customer Tags',
    'LBL_ASSIGN_CUSTOMER_TAGS_TASK_RELATED_CUSTOMER_FIELD' => 'Select field to identify customer',
    'LBL_ASSIGN_CUSTOMER_TAGS_TASK_GET_TAGS_FROM_PRODUCTS_AND_SERVICES' => 'Get tags from Products and Services',
    'LBL_ASSIGN_CUSTOMER_TAGS_TASK_TAG_LIST' => 'Select tags to assign',
    'LBL_ASSIGN_CUSTOMER_TAGS_TASK_TAGS_HINT' => 'System will only use public tags for assigning to customer!',
    // End Hieu Nguyen

	// Added by Hieu Nguyen on 2021-12-07 to support Unlink Customer Tags Task
    'LBL_VTUnlinkCustomerTagsTask' => 'Unlink Customer Tags',
    'LBL_UNLINK_CUSTOMER_TAGS_TASK' => 'Unlink Customer Tags',
    'LBL_UNLINK_CUSTOMER_TAGS_TASK_RELATED_CUSTOMER_FIELD' => 'Select field to identify customer',
    'LBL_UNLINK_CUSTOMER_TAGS_TASK_GET_TAGS_FROM_PRODUCTS_AND_SERVICES' => 'Get tags from Products and Services',
    'LBL_UNLINK_CUSTOMER_TAGS_TASK_TAG_LIST' => 'Select tags to unlink',
    'LBL_UNLINK_CUSTOMER_TAGS_TASK_TAGS_HINT' => 'System will only unlink public tags from customer!',
    // End Hieu Nguyen

	// Added by Hieu Nguyen on 2021-11-24 to support Update Mautic Stage Task
    'LBL_VTUpdateMauticStageTask' => 'Update Mautic Stage',
    'LBL_UPDATE_MAUTIC_STAGE_TASK' => 'Update Mautic Stage',
    'LBL_UPDATE_MAUTIC_STAGE_TASK_RELATED_CUSTOMER_FIELD' => 'Select field to identify customer',
    'LBL_UPDATE_MAUTIC_STAGE_TASK_MAUTIC_STAGE' => 'Select Mautic Stage',
    // End Hieu Nguyen

	// Added by Phu Vo
	'LBL_WORKFLOW_UPDATE_IMPORT_BLOCK' => 'Mass Update / Import',
	'LBL_WORKFLOW_MASS_ACTION' => 'Allow workflow trigger in mass update/ import process',
	// End Phu Vo
);

$jsLanguageStrings = array(
	'JS_STATUS_CHANGED_SUCCESSFULLY' => 'Status changed Successfully',
	'JS_TASK_DELETED_SUCCESSFULLY' => 'Action deleted Successfully',
	'JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE' => 'Same fields selected more than once',
	'JS_WORKFLOW_SAVED_SUCCESSFULLY' => 'Workflow saved successfully',
    'JS_CHECK_START_AND_END_DATE'=>'End Date & Time should be greater than or equal to Start Date & Time',
    'JS_TASK_STATUS_CHANGED' => 'Task status changed successfully.',
    'JS_WORKFLOWS_STATUS_CHANGED' => 'Workflow status changed successfully.',
    'VTEmailTask' => 'Send Mail',
    'VTEntityMethodTask' => 'Invoke Custom Function',
    'VTCreateTodoTask' => 'Create Task',
    'VTCreateEventTask' => 'Create Event',
    'VTUpdateFieldsTask' => 'Update Fields',
    'VTSMSTask' => 'SMS Task', 
    'VTPushNotificationTask' => 'Mobile Push Notification',
    'VTCreateEntityTask' => 'Create Record',
    'LBL_EXPRESSION_INVALID' => 'Expression Invalid'
);

