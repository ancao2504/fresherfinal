<?php
/* +**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* ***********************************************************************************/

// Define where OnlineCRM's PMS system is to get full license info
$pmsUrl = 'https://pms.onlinecrm.vn/';

// Secret key for public urls like webhooks or secure entrypoints
$secretKey = '<SECRET_KEY>';    // Use https://www.md5online.org/ to generate different key for each project!

// [Security] List username of users that are for API access only (using for 3rd integration)
$usersForApiOnly = array(
    'api.erp',
    'api.hr',
    'api.accounting',
    'api.warehouse',
    'api.retail',
    'api.mautic',
    'api.website',
    'api.landingpage',
);
$happyBirthDayConfig = array(
    'contacts_sms_template_id' => 2155,
    'contactsemail_template_id' => 44,
);
$loggerConfig = array(	
    'ERROR' => false,
    'FATAL' => false,
    'INFO'  => false,
    'WARN'  => false,
    'DEBUG' => false,
);

//Maximum number of Mailboxes in mail converter
$max_mailboxes = 3;

// Business Managers who are allowed to access public configs
$businessManagersConfig = array(
    'facebook_integration' => array(),  // ['Users:1', 'Users:2']
    'zalo_integration' => array(),      // ['Users:1', 'Users:2']
    'telesales_campaign' => array(),    // ['Users:1', 'Users:2']
    'leads_distribution' => array(),    // ['Users:1', 'Users:2']
);

// Google Integration
$googleConfig = array(
    'debug' => array(
        'calendar' => array(
            'pull' => false,
            'push' => false
        ),
        'contacts' => array(
            'pull' => false,
            'push' => false
        ),
    ),
    'oauth' => array(
        'client_id' => '',
	    'client_secret' => '',
    ),
    'maps' => array(
        'maps_and_places_api_key' => '',
        'geocoding_api_key' => '',
    ),
    'recaptcha' => array(
        'site_key' => '',
        'secret_key' => '',
        'endpoint' => 'https://www.google.com/recaptcha/api/siteverify',
    ),
    'firebase' => array(
        'fcm_sender_id' => '',
        'fcm_server_key' => '',
    ),
);

$oauthCallbackAllowedModules = array(
    'Google',
    'CPChatBotIntegration',
    'Vtiger',
);

$inventoryModules = array(
    'Invoice',
    'Quotes',
    'PurchaseOrder',
    'SalesOrder',
    //'ReturnOrder'
	'CPComboProducts', //-- Added by Kelvin Thang on 2020-02-29 set module CPComboProducts is inventory
);

$layoutEditorConfig = array(
    'allowed_system_modules' => array('Users', 'PBXManager', 'SMSNotifier'), // Allow system modules to show in LayoutEditor selection
    'modules_allow_developer_only' => array(    // Modules that should only show for developer in Layout Editor
        'Users', 'PBXManager', 'SMSNotifier', 'Calendar', 'Events', 'CPZaloAdsForm', 'CPChatMessageLog', 'CPEmployeeCheckinLog', 'CPMauticContactHistory',
        'CPEventRegistration', 'CPSMSOTTMessageLog', 'CPSocialMessageLog', 'CPSocialArticleLog', 'CPSocialFeedback', 'CPTicketCommunicationLog'
    ),
    'prevent_custom_field' => array(
        'Calendar'		=> array('LBL_EVENT_INFORMATION', 'LBL_TASK_INFORMATION', 'LBL_DESCRIPTION_INFORMATION', 'LBL_CHECKIN', 'LBL_INVITEES'),
        'Events'		=> array('LBL_INVITEES'),
        'HelpDesk'		=> array('LBL_TICKET_RESOLUTION', 'LBL_COMMENTS'),
        'Faq'			=> array('LBL_COMMENT_INFORMATION'),
        'Invoice'		=> array('LBL_ITEM_DETAILS'),
        'Quotes'		=> array('LBL_ITEM_DETAILS'),
        'SalesOrder'	=> array('LBL_ITEM_DETAILS'),
        'PurchaseOrder'	=> array('LBL_ITEM_DETAILS'),
        //'ReturnOrder'	=> array('LBL_ITEM_DETAILS'),
	    'CPComboProducts'	=> array('LBL_ITEM_DETAILS'), //-- Added by Kelvin Thang on 2020-02-29 set module CPComboProducts to show in LayoutEditor selection
    )
);

//-- Added By Kelvin Thang on 2020-02-29 set Combo Products Ignore Modules
$comboProductsIgnoreModules = array(
	'PurchaseOrder',
	'CPComboProducts',
);

// Hide these modules from Menu, Menu Editor, Layout Editor, Record Numbering, Picklist Editor, Picklist Dependency, Workflows and Create Report
$hiddenModules = array(
    'CPLocalization',
    'CPAICameraIntegration',
    'CPTicketCommunicationLog',
    'CPOTTIntegration',
);

// Hide these modules in Module Manager only
$hiddenModulesForModuleManager = array(
    'Reports',
    'CPKanban',
    'CPNotification',
    'CPLocalization',
    'CPMauticIntegration',
    'CPChatBotIntegration',
    'CPSocialIntegration',
);

// Hide these modules in Quick Create menu
$hiddenModulesForQuickCreate = array(
    'Emails', 'ModComments', 'Integration', 'PBXManager', 'Dashboard', 'Home', 'CPTarget'
);

// Advanced Quick Create menu config to display quick create menu with dropdown options
$advancedQuickCreateMenus = array(
    // 'HelpDesk' => array(
    //     'LBL_REPORT_BUG' => array('icon' => 'fa-bug', 'link' => 'index.php?module=HelpDesk&view=Edit&helpdesk_customer_type=Internal&ticketcategories=Bug&helpdesk_issue_category=Base'),
    //     'LBL_REQUEST_FEATURE' => array('icon' => 'fa-puzzle-piece', 'link' => 'index.php?module=HelpDesk&view=Edit&helpdesk_customer_type=Internal&ticketcategories=Request&helpdesk_issue_category=Base'),
    //     'LBL_CREATE_NEW' => array('icon' => 'fa-file-text', 'link' => 'javascript:vtUtils.openQuickCreateModal(\'HelpDesk\', {data: {ticketpriorities: \'Urgent\'}});'),
    // ),
);

// Prevent user to edit these fields
$nonEditableFields = array(
    'isconvertedfrompotential', 
    'isconvertedfromlead', 
    'createdby', 
    'main_owner_id', 
    'mautic_id',
    'last_synced_mautic_history_time',
    'zalo_id_synced',
    'accounts_customer_group',
    'chat_channel',
    'chat_app',
);

// Ignore role based check on picklist fields
$nonRoleBasedPicklists = array(
    'chat_app',
);

// Picklist fields not allowed to display in Picklist Editor
$pickListEditorUnsupportedFields = [
    'campaignrelstatus', 'duration_minutes','email_flag','hdnTaxType', 'payment_duration', 'recurringtype', 'recurring_frequency',
    'visibility', 'chat_app', 'paymentterms', 'modeofpayment', 'paymentgateway', 'currency', 'cpreceipt_currency', 'cpassetaccount_currency',
    'cppayment_currency', 'cppayment_step', 'cppayment_manager_status', 'cppayment_leader_status', 'cppayment_accountant_status', 'callstatus',
    'sms_ott_message_type', 'cpsocialmessagetemplate_type', 'cpsocialmessagetemplate_content_type', 'cpsocialmessagetemplate_status',
    'cpsocialmessagetemplate_module', 'cpsocialmessagelog_social_channel', 'cpsocialarticle_type', 'cpsocialarticle_status', 'cpsocialmessagelog_status',
    'cpsocialarticlelog_social_channel', 'cpsocialarticlelog_status', 'cpsocialfeedback_type', 'cpsocialfeedback_channel', 'cpmauticcontacthistory_type',
    'cpchatmessagelog_channel', 'queue_status', 'cpeventregistration_status', 'cpeventregistration_confirm_status', 'cpzaloadsform_advertise_oa',
    'cpzaloadsform_purpose', 'cpzaloadsform_matching_criteria', 'cpzaloadsform_default_module',
];

// Config full name order (need to Repair after change)
$fullNameConfig = array(
    'required_field' => 'firstname',                        // Only firstname or lastname can be required at the same time
    'full_name_order' => array('lastname', 'firstname'),    // Change the field order to get the expected full name display order
);

$globalSearchConfig = [
    'min_keyword_length' => 2,
    'page_limit' => 5,
    'default_display_fields' => ['id', 'assigned_user_id', 'main_owner_id', 'createdby', 'modifiedby'],
];

// Validation rules
$validationConfig = array(
    'prevent_redundant_spaces' => true,
    'autocomplete_min_length' => 2,
    'password' => array(
        'length' => 8,
        'lower_case_characters' => true,
        'upper_case_characters' => false,
        'digit_characters' => false,
        'special_characters' => false,
    ),
    'allowed_upload_file_exts' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'ics', 'odt', 'ods', 'odp', 'png', 'jpg', 'gif', 'tiff', 'bmp', 'avi', 'flv', 'wmv', 'mp4', 'mov', 'mp3', 'wav', 'flac', 'dwg', 'zip', 'rar', 'tar'], // Moved by Phuc on 2020.01.20 to add upload file ext
	'minilist_widget_max_columns' => $minilist_widget_max_columns, //-- Added By Kelvin Thang on 2020-01-07 config set minilist widget max columns in dashboard
    'global_search_keyword_min_length' => $globalSearchConfig['min_keyword_length'],
);

// Default preferences for new users
$defaultUserPreferences = array(
    'language' => 'vn_vn',
    'timezone' => 'Asia/Bangkok',
    'date_format' => 'dd-mm-yyyy',
    'currency_id' => '1',
    'currency_symbol_placement' => '1.0$',
);

$emailQueueConfig = array(
    'batch_limit' => 500,
    'max_attempts' => 5,
);

$smsQueueConfig = array(
    'batch_limit' => 500,
    'max_attempts' => 5,
);

$voiceCommandConfig = array(
    'enable' => false,
    'proxy_server_name' => 'demo.cloudpro.vn',
    'proxy_server_port' => '2096',
    'proxy_server_ssl' => true,
    'socket_token' => 'c505eb1d7a28c72632465526453aa1a0',
);

$callCenterConfig = array(
    'enable' => false,                          // Set this to true to enable. Set to false if no need to use to prevent error in console
    'debug' => true,                            // Set this to true to write debug logs
    'bridge' => array(
        'server_name' => '<IP_OR_DOMAIN>',      // Must be a domain in SSL connection
        'server_port' => '1234',                // This port will be used for frontend connection, prefer SSL port if available
        'server_ssl' => false,                  // Set to true if the port above is SSL
        'server_backend_port' => '1234',        // This port will be used for backend connection, prefer non-SSL port when SSL error
        'server_backend_ssl' => false,          // Set to true if the port above is SSL
        'access_domain' => '<CRM_DOMAIN>',      // Domain of CRM site which is registered in Call Center Bridge credentials
        'private_key' => '<SHA256_SECRET_KEY>', // SHA256 private key along with Domain of CRM site which is used to create access token
    ),
    'inbound_routing' => array(     // Routing for inbound call (Not all provider support this feature!!!)
        '02862858702' => 'H6',      // Hotline => Role ID
        '02836222701' => 'H7',      // Hotline => Role ID
    ),
    'outbound_routing' => array(    // Routing for outbound call (Curently for Stringee, Tel4VN and OmiCall only!!!)
        '02862858702' => ['H6'],      // Hotline => [Role ID1, Role ID2]
        '02836222701' => ['H7'],      // Hotline => [Role ID3, Role ID4]
    ),
    'click2call_users_can_use_all_hotlines' => array('Users:1', 'Users:2'),
    'click2call_enabled_modules' => array('Contacts', 'Leads', 'CPTarget'), // Allow click2call functionality in these modules
    'ami_version' => '',       // Specify the AMI version of GrandStream Device (use telnet to get the version info)
);

$socialConfig = array(
    'social_message_send_immediately_limit' => 20,
    'queue_process_batch_limit' => 500,
    'facebook' => array(
        'debug' => true,
    ),
    'zalo' => array(
        'debug' => true,
        'service_url' => 'https://openapi.zalo.me/v2.0/',
        'max_requests_per_min' => 4000,
        'max_articles_limit' => 10,
        'request_share_contact_info' => array(  // Clone the sub array for each OA
            '<OA_ID1>' => array(
                'image_url' => '<IMAGE_URL1>',
                'title' => '<TITLE1>',
                'message' => '<MESSAGE1>',
            ),
        ),
    )
);

$centralizedChatboxConfig = array(
    'enabled' => false,
    'chat_bridge' => array(
        'debug' => true,
        'server_name' => '<IP_OR_DOMAIN>',       // Must be a domain in SSL connection
        'server_port' => '2086',                 // This port will be used for frontend connection, prefer SSL port if available
        'server_ssl' => false,                   // Set to true if the port above is SSL
        'server_backend_port' => '2086',         // This port will be used for backend connection, prefer non-SSL port when SSL error
        'server_backend_ssl' => false,           // Set to true if the port above is SSL
        'access_domain' => '<CRM_DOMAIN>',       // Domain of CRM site which is registered in Social Chat Bridge credentials
        'private_key' => '<SHA256_SECRET_KEY>',  // SHA256 private key along with Domain of CRM site which is used to create access token
    ),
    'chat_storage' => array(
        'debug' => true,
        'service_url' => '<STORAGE_SERVICE_URL>',   // Ex: http://dev.cloudpro.vn:2052/
        'access_token' => '<STORAGE_SERVICE_ACCESS_TOKEN>',
    ),
    'chat_admins' => array(),   // ['Users:1', 'Users:2']
);

$chatBotConfig = array(
    'enable' => false,
    'debug' => false,
    'queue_process_batch_limit' => 500,
    'hana' => array(
        'service_url' => 'https://apiv2.hana.ai/external-api/v1.0/',
        'chat_bot_iframe_url' => 'https://bot.hana.ai/apps/',
        'chat_detail_iframe_url' => 'https://bot.hana.ai/chat-iframe/',
    ),
    'bbh' => array(
        'oauth_redirect_url' => 'https://botbanhang.vn/chat/#/redirect',
        'chatbox_url' => 'https://botbanhang.vn/chat/#/chat-box',
        'bot_service_url' => 'https://api.botbanhang.vn/v1.3/public/json',          // For sync customer and send message APIs
        'widget_service_url' => 'https://chatbox-app.botbanhang.vn/v1/service/',    // For Iframe Widget auth
        'store_service_url' => 'https://api-cms.botup.io/v1/selling-page/',         // For sync category and product APIs
    ),
);

$aiCameraConfig = array(
    'debug' => true,
    'job_sync_checkin_log_start_hour' => 18,        // Cronjob will start syncing checkin logs to CRM from this start hour
    'checkin_notification_interval_minutes' => 1,   // Only 1 notification will be sent in this interval, duplicate checkins will stores but skips notification
    'hanet' => array(
        'auth_url' => 'https://oauth.hanet.com/',
        'service_url' => 'https://partner.hanet.ai/',
    ),
    'cmc' => array(
        'auth_url' => '',   // Not avaiable for now
        'service_url' => 'https://camera-api.api-connect.io:8089/face-recognitions/',
    ),
);

$iotConfig = array(
    'auto_rollter_shutter' => array(
        'enable' => false,
        'ai_camera_device_id' => '<DEVICE_ID>',  // This camera device will only be used for auto roller shutter
        'do_not_save_office_checkin_log' => true,   // Indicate if this camera device is for auto roller shutter, not for office checkin
        'service_url' => 'http://<SEVER_IP>:<PORT>/sendCommand',    // Service url of the IoT-Hub
        'access_token' => '<ACCESS_TOKEN>',     // Get this token from IoT-Hub's config.json
        'allow_open_time' => array(     // Only to allow open roller shutter using camera checkin within this time range
            'begin' => '07:30:00',
            'end' => '19:00:00',
        ),
    )
);

$mauticConfig = array(
    'min_points_to_sync_data' => 100,
);

$kanbanConfig = array(
    'enabled_modules' => array('Accounts', 'Contacts', 'Leads', 'Potentials', 'Project', 'HelpDesk', 'Calendar'),
    'totaled_modules' => array('Potentials' => 'amount')
);

$notificationConfig = array(
    'assign_ignore_modules' => array(
        'SMSNotifier', 
        'PBXManager', 
        'CPSocialFeedback', 
        'CPSocialArticle', 
        'CPSocialArticleLog', 
        'CPSocialMessageLog',
        'Emails', // Added by Phu Vo on 2020.09.22
        'CPSMSOTTMessageLog', // Added by Phu Vo on 2020.11.18
        'CPEmployeeCheckinLog', // Added by Phu Vo on 2021.05.17
    ),
);

$mobileConfig = array(
    'api_key' => 'c505eb1d7a28c72632465526453aa1a0',
    'supported_modules' => [
        'Leads',
        'Contacts',
        'Accounts',
        'Potentials',
        'Quotes',
        'ServiceContracts',
        'SalesOrder',
        'Products',
        'Services',
        'Faq',
        'HelpDesk',
        'Calendar',
        'Events',
        'ModComments',
        'Documents'
    ]
);

$batchSaveConfig = array(
    'trigger_entity_events' => true,    // Allow entity events (beforesave/aftersave/...) to run while running batch save (import/massupdate)
);

$dashboardWidgetConfig = array(
    'GroupedBySalesPerson' => array('rows' => 1, 'cols' => 2),
    'PipelinedAmountPerSalesPerson' => array('rows' => 1, 'cols' => 2),
    'GroupedBySalesStage' => array('rows' => 1, 'cols' => 2),
    'Funnel Amount' => array('rows' => 1, 'cols' => 2),
    'History' => array('rows' => 2, 'cols' => 1),
    'CompareCallsThisYear' => array('rows' => 1, 'cols' => 2),
    'CompareCallsThisMonth' => array('rows' => 1, 'cols' => 3),
    'PlannedCalls' => array('rows' => 1, 'cols' => 2),
    'MissedCalls' => array('rows' => 1, 'cols' => 2),
    'BiggestAmountPotentialsWidget' => array('rows' => 1, 'cols' => 2),
    'CloseExpectedPotentialsWidget' => array('rows' => 1, 'cols' => 2),
    'OverdueCallsWidget' => array('rows' => 1, 'cols' => 1),
    'OverdueMeetingsWidget' => array('rows' => 1, 'cols' => 1),
    'OverdueTasksWidget' => array('rows' => 1, 'cols' => 1),
    'ActiveCampaignsWidget' => array('rows' => 1, 'cols' => 3),
    'CancelledCampaignWidget' => array('rows' => 1, 'cols' => 3),
    'CustomerUnfollowedInPeriodWidget' => array('rows' => 1, 'cols' => 2),
    'CustomerHaveNoSOInPeriodWidget' => array('rows' => 1, 'cols' => 2),
    'CustomerHaveContractWillBeExpiredWidget' => array('rows' => 1, 'cols' => 2),
    'CustomerHaveGuaranteeContractWillBeExpiredWidget' => array('rows' => 1, 'cols' => 2),
    'CustomerHaveBirthdayThisMonthWidget' => array('rows' => 1, 'cols' => 2),
    'CustomerWillReachHigherMemberLevelWidget' => array('rows' => 1, 'cols' => 2),
    'NewLeadsWidget' => array('rows' => 1, 'cols' => 1),
    'NewSalesOrdersWidget' => array('rows' => 1, 'cols' => 2),
    'NewQuotesWidget' => array('rows' => 1, 'cols' => 2),
    'NewContactsWidget' => array('rows' => 1, 'cols' => 2),
    'InactiveCampaignWidget' => array('rows' => 1, 'cols' => 2),
    'PlannedCampaignWidget' => array('rows' => 1, 'cols' => 2),
    'CompletedCampaignWidget' => array('rows' => 1, 'cols' => 2),
    'MissedCallsWidget' => array('rows' => 1, 'cols' => 1),
    'InProgressTasksWidget' => array('rows' => 1, 'cols' => 2),
    'PlannedTasksWidget' => array('rows' => 1, 'cols' => 2),
    'PlannedMeetingsWidget' => array('rows' => 1, 'cols' => 2),
    'PlannedCallsWidget' => array('rows' => 1, 'cols' => 2),
    'NewPotentialsWidget' => array('rows' => 1, 'cols' => 2),
    'NegotiationReviewPotentialsWidget' => array('rows' => 1, 'cols' => 1),
    'PreQualifiedLeadsWidget' => array('rows' => 1, 'cols' => 1),
    'TakeCaringLeadsWidget' => array('rows' => 1, 'cols' => 1),
    'QualifiedLeadsWidget' => array('rows' => 1, 'cols' => 1),
    'HotLeadsWidget' => array('rows' => 1, 'cols' => 1),
    'ClosedTicketsWidget' => array('rows' => 1, 'cols' => 1),
    'WaitForResponseTicketsWidget' => array('rows' => 1, 'cols' => 1),
    'InProgressTicketsWidget' => array('rows' => 1, 'cols' => 1),
    'OpenTicketsWidget' => array('rows' => 1, 'cols' => 2),
    'NewSaleContractsWidget' => array('rows' => 1, 'cols' => 2),
    'PlannedReceiptsWidget' => array('rows' => 1, 'cols' => 3),
    'PlannedPaymentsWidget' => array('rows' => 1, 'cols' => 3),
    'PaymentsWidget' => array('rows' => 1, 'cols' => 3),
    'ReceiptsWidget' => array('rows' => 1, 'cols' => 3),
    'FeedbackSummaryWidget' => array('rows' => 1, 'cols' => 2),
    'DebitSummaryWidget' => array('rows' => 1, 'cols' => 2),
    'SalesSummaryWidget' => array('rows' => 1, 'cols' => 3),
    'ConvertRateSummaryWidget' => array('rows' => 1, 'cols' => 2),
    'MarketingEfficientSummaryWidget' => array('rows' => 1, 'cols' => 2),
    'MarketingSummaryWidget' => array('rows' => 1, 'cols' => 3),
    'CampaignSummaryWidget' => array('rows' => 1, 'cols' => 3),
);

$ImportConfig = array(
    'importTypes' => array(
        'csv' => array('reader' => 'Import_CSVReader_Reader', 'classpath' => 'modules/Import/readers/CSVReader.php'),
        'vcf' => array('reader' => 'Import_VCardReader_Reader', 'classpath' => 'modules/Import/readers/VCardReader.php'),
        'ics' => array('reader' => 'Import_ICSReader_Reader', 'classpath' => 'modules/Import/readers/ICSReader.php'),
        'default' => array('reader' => 'Import_FileReader_Reader', 'classpath' => 'modules/Import/readers/FileReader.php')
    ),

    'userImportTablePrefix' => 'vtiger_import_',
    // Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported
    'importBatchLimit' => '250',
    // Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job
    //'immediateImportLimit' => '1000',
    'immediateImportLimit' => '1000',
    //'importPagingLimit' => '10000',
    'importPagingLimit' => '1000000',
    //'importPagingLimit' => '100',
);

$generateDemoData = array(
    'userId' => '21', //user handle task generate data, don't use this user to import other data via normal way
    'userName' => 'demodata', //user handle task generate data, don't use this user to import other data via normal way
    'modules' => array( // which module need to import?
        '2', //Potentials
        '20', //Quotes
        '22', //SalesOrder
    ),
    'userRandom' => array(
        'demodata',
        'tung.bui',
        'hoc.bui',
        'admin',
    )
);

//-- Added By Kelvin Thang -- date: 2019-01-14 -add config override set module Non visible
//$nonVisibleModulesList = array();

// Added by Hoang Duc 22-03-2019 to enable email access tracking
$email_tracking = "Yes";
// End Hoang Duc

// Added by Phu Vo on 2020.02.06 to config sms feature
$smsConfig = [
    'debug' => false,
    'max_characters' => 600,
];
// End Phu Vo

// Added by Phu Vo on 2020.04.23
$countryCodes = array(
    '1' => array('name' => 'UNITED STATES', 'code' => '1', 'short_name' => 'US'),
    '7' => array('name' => 'RUSSIAN FEDERATION', 'code' => '7', 'short_name' => 'RU'),
    '20' => array('name' => 'EGYPT', 'code' => '20', 'short_name' => 'EG'),
    '27' => array('name' => 'SOUTH AFRICA', 'code' => '27', 'short_name' => 'ZA'),
    '30' => array('name' => 'GREECE', 'code' => '30', 'short_name' => 'GR'),
    '31' => array('name' => 'NETHERLANDS', 'code' => '31', 'short_name' => 'NL'),
    '32' => array('name' => 'BELGIUM', 'code' => '32', 'short_name' => 'BE'),
    '33' => array('name' => 'FRANCE', 'code' => '33', 'short_name' => 'FR'),
    '34' => array('name' => 'SPAIN', 'code' => '34', 'short_name' => 'ES'),
    '36' => array('name' => 'HUNGARY', 'code' => '36', 'short_name' => 'HU'),
    '39' => array('name' => 'HOLY SEE (VATICAN CITY STATE)', 'code' => '39', 'short_name' => 'VA'),
    '40' => array('name' => 'ROMANIA', 'code' => '40', 'short_name' => 'RO'),
    '41' => array('name' => 'SWITZERLAND', 'code' => '41', 'short_name' => 'CH'),
    '43' => array('name' => 'AUSTRIA', 'code' => '43', 'short_name' => 'AT'),
    '44' => array('name' => 'ISLE OF MAN', 'code' => '44', 'short_name' => 'IM'),
    '45' => array('name' => 'DENMARK', 'code' => '45', 'short_name' => 'DK'),
    '46' => array('name' => 'SWEDEN', 'code' => '46', 'short_name' => 'SE'),
    '47' => array('name' => 'NORWAY', 'code' => '47', 'short_name' => 'NO'),
    '48' => array('name' => 'POLAND', 'code' => '48', 'short_name' => 'PL'),
    '49' => array('name' => 'GERMANY', 'code' => '49', 'short_name' => 'DE'),
    '51' => array('name' => 'PERU', 'code' => '51', 'short_name' => 'PE'),
    '52' => array('name' => 'MEXICO', 'code' => '52', 'short_name' => 'MX'),
    '53' => array('name' => 'CUBA', 'code' => '53', 'short_name' => 'CU'),
    '54' => array('name' => 'ARGENTINA', 'code' => '54', 'short_name' => 'AR'),
    '55' => array('name' => 'BRAZIL', 'code' => '55', 'short_name' => 'BR'),
    '56' => array('name' => 'CHILE', 'code' => '56', 'short_name' => 'CL'),
    '57' => array('name' => 'COLOMBIA', 'code' => '57', 'short_name' => 'CO'),
    '58' => array('name' => 'VENEZUELA', 'code' => '58', 'short_name' => 'VE'),
    '60' => array('name' => 'MALAYSIA', 'code' => '60', 'short_name' => 'MY'),
    '61' => array('name' => 'CHRISTMAS ISLAND', 'code' => '61', 'short_name' => 'CX'),
    '62' => array('name' => 'INDONESIA', 'code' => '62', 'short_name' => 'ID'),
    '63' => array('name' => 'PHILIPPINES', 'code' => '63', 'short_name' => 'PH'),
    '64' => array('name' => 'NEW ZEALAND', 'code' => '64', 'short_name' => 'NZ'),
    '65' => array('name' => 'SINGAPORE', 'code' => '65', 'short_name' => 'SG'),
    '66' => array('name' => 'THAILAND', 'code' => '66', 'short_name' => 'TH'),
    '81' => array('name' => 'JAPAN', 'code' => '81', 'short_name' => 'JP'),
    '82' => array('name' => 'KOREA REPUBLIC OF', 'code' => '82', 'short_name' => 'KR'),
    '84' => array('name' => 'VIET NAM', 'code' => '84', 'short_name' => 'VN'),
    '86' => array('name' => 'CHINA', 'code' => '86', 'short_name' => 'CN'),
    '90' => array('name' => 'TURKEY', 'code' => '90', 'short_name' => 'TR'),
    '91' => array('name' => 'INDIA', 'code' => '91', 'short_name' => 'IN'),
    '92' => array('name' => 'PAKISTAN', 'code' => '92', 'short_name' => 'PK'),
    '93' => array('name' => 'AFGHANISTAN', 'code' => '93', 'short_name' => 'AF'),
    '94' => array('name' => 'SRI LANKA', 'code' => '94', 'short_name' => 'LK'),
    '95' => array('name' => 'MYANMAR', 'code' => '95', 'short_name' => 'MM'),
    '98' => array('name' => 'IRAN, ISLAMIC REPUBLIC OF', 'code' => '98', 'short_name' => 'IR'),
    '212' => array('name' => 'MOROCCO', 'code' => '212', 'short_name' => 'MA'),
    '213' => array('name' => 'ALGERIA', 'code' => '213', 'short_name' => 'DZ'),
    '216' => array('name' => 'TUNISIA', 'code' => '216', 'short_name' => 'TN'),
    '218' => array('name' => 'LIBYAN ARAB JAMAHIRIYA', 'code' => '218', 'short_name' => 'LY'),
    '220' => array('name' => 'GAMBIA', 'code' => '220', 'short_name' => 'GM'),
    '221' => array('name' => 'SENEGAL', 'code' => '221', 'short_name' => 'SN'),
    '222' => array('name' => 'MAURITANIA', 'code' => '222', 'short_name' => 'MR'),
    '223' => array('name' => 'MALI', 'code' => '223', 'short_name' => 'ML'),
    '224' => array('name' => 'GUINEA', 'code' => '224', 'short_name' => 'GN'),
    '225' => array('name' => 'COTE D IVOIRE', 'code' => '225', 'short_name' => 'CI'),
    '226' => array('name' => 'BURKINA FASO', 'code' => '226', 'short_name' => 'BF'),
    '227' => array('name' => 'NIGER', 'code' => '227', 'short_name' => 'NE'),
    '228' => array('name' => 'TOGO', 'code' => '228', 'short_name' => 'TG'),
    '229' => array('name' => 'BENIN', 'code' => '229', 'short_name' => 'BJ'),
    '230' => array('name' => 'MAURITIUS', 'code' => '230', 'short_name' => 'MU'),
    '231' => array('name' => 'LIBERIA', 'code' => '231', 'short_name' => 'LR'),
    '232' => array('name' => 'SIERRA LEONE', 'code' => '232', 'short_name' => 'SL'),
    '233' => array('name' => 'GHANA', 'code' => '233', 'short_name' => 'GH'),
    '234' => array('name' => 'NIGERIA', 'code' => '234', 'short_name' => 'NG'),
    '235' => array('name' => 'CHAD', 'code' => '235', 'short_name' => 'TD'),
    '236' => array('name' => 'CENTRAL AFRICAN REPUBLIC', 'code' => '236', 'short_name' => 'CF'),
    '237' => array('name' => 'CAMEROON', 'code' => '237', 'short_name' => 'CM'),
    '238' => array('name' => 'CAPE VERDE', 'code' => '238', 'short_name' => 'CV'),
    '239' => array('name' => 'SAO TOME AND PRINCIPE', 'code' => '239', 'short_name' => 'ST'),
    '240' => array('name' => 'EQUATORIAL GUINEA', 'code' => '240', 'short_name' => 'GQ'),
    '241' => array('name' => 'GABON', 'code' => '241', 'short_name' => 'GA'),
    '242' => array('name' => 'CONGO', 'code' => '242', 'short_name' => 'CG'),
    '243' => array('name' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'code' => '243', 'short_name' => 'CD'),
    '244' => array('name' => 'ANGOLA', 'code' => '244', 'short_name' => 'AO'),
    '245' => array('name' => 'GUINEA-BISSAU', 'code' => '245', 'short_name' => 'GW'),
    '248' => array('name' => 'SEYCHELLES', 'code' => '248', 'short_name' => 'SC'),
    '249' => array('name' => 'SUDAN', 'code' => '249', 'short_name' => 'SD'),
    '250' => array('name' => 'RWANDA', 'code' => '250', 'short_name' => 'RW'),
    '251' => array('name' => 'ETHIOPIA', 'code' => '251', 'short_name' => 'ET'),
    '252' => array('name' => 'SOMALIA', 'code' => '252', 'short_name' => 'SO'),
    '253' => array('name' => 'DJIBOUTI', 'code' => '253', 'short_name' => 'DJ'),
    '254' => array('name' => 'KENYA', 'code' => '254', 'short_name' => 'KE'),
    '255' => array('name' => 'TANZANIA, UNITED REPUBLIC OF', 'code' => '255', 'short_name' => 'TZ'),
    '256' => array('name' => 'UGANDA', 'code' => '256', 'short_name' => 'UG'),
    '257' => array('name' => 'BURUNDI', 'code' => '257', 'short_name' => 'BI'),
    '258' => array('name' => 'MOZAMBIQUE', 'code' => '258', 'short_name' => 'MZ'),
    '260' => array('name' => 'ZAMBIA', 'code' => '260', 'short_name' => 'ZM'),
    '261' => array('name' => 'MADAGASCAR', 'code' => '261', 'short_name' => 'MG'),
    '262' => array('name' => 'MAYOTTE', 'code' => '262', 'short_name' => 'YT'),
    '263' => array('name' => 'ZIMBABWE', 'code' => '263', 'short_name' => 'ZW'),
    '264' => array('name' => 'NAMIBIA', 'code' => '264', 'short_name' => 'NA'),
    '265' => array('name' => 'MALAWI', 'code' => '265', 'short_name' => 'MW'),
    '266' => array('name' => 'LESOTHO', 'code' => '266', 'short_name' => 'LS'),
    '267' => array('name' => 'BOTSWANA', 'code' => '267', 'short_name' => 'BW'),
    '268' => array('name' => 'SWAZILAND', 'code' => '268', 'short_name' => 'SZ'),
    '269' => array('name' => 'COMOROS', 'code' => '269', 'short_name' => 'KM'),
    '290' => array('name' => 'SAINT HELENA', 'code' => '290', 'short_name' => 'SH'),
    '291' => array('name' => 'ERITREA', 'code' => '291', 'short_name' => 'ER'),
    '297' => array('name' => 'ARUBA', 'code' => '297', 'short_name' => 'AW'),
    '298' => array('name' => 'FAROE ISLANDS', 'code' => '298', 'short_name' => 'FO'),
    '299' => array('name' => 'GREENLAND', 'code' => '299', 'short_name' => 'GL'),
    '350' => array('name' => 'GIBRALTAR', 'code' => '350', 'short_name' => 'GI'),
    '351' => array('name' => 'PORTUGAL', 'code' => '351', 'short_name' => 'PT'),
    '352' => array('name' => 'LUXEMBOURG', 'code' => '352', 'short_name' => 'LU'),
    '353' => array('name' => 'IRELAND', 'code' => '353', 'short_name' => 'IE'),
    '354' => array('name' => 'ICELAND', 'code' => '354', 'short_name' => 'IS'),
    '355' => array('name' => 'ALBANIA', 'code' => '355', 'short_name' => 'AL'),
    '356' => array('name' => 'MALTA', 'code' => '356', 'short_name' => 'MT'),
    '357' => array('name' => 'CYPRUS', 'code' => '357', 'short_name' => 'CY'),
    '358' => array('name' => 'FINLAND', 'code' => '358', 'short_name' => 'FI'),
    '359' => array('name' => 'BULGARIA', 'code' => '359', 'short_name' => 'BG'),
    '370' => array('name' => 'LITHUANIA', 'code' => '370', 'short_name' => 'LT'),
    '371' => array('name' => 'LATVIA', 'code' => '371', 'short_name' => 'LV'),
    '372' => array('name' => 'ESTONIA', 'code' => '372', 'short_name' => 'EE'),
    '373' => array('name' => 'MOLDOVA, REPUBLIC OF', 'code' => '373', 'short_name' => 'MD'),
    '374' => array('name' => 'ARMENIA', 'code' => '374', 'short_name' => 'AM'),
    '375' => array('name' => 'BELARUS', 'code' => '375', 'short_name' => 'BY'),
    '376' => array('name' => 'ANDORRA', 'code' => '376', 'short_name' => 'AD'),
    '377' => array('name' => 'MONACO', 'code' => '377', 'short_name' => 'MC'),
    '378' => array('name' => 'SAN MARINO', 'code' => '378', 'short_name' => 'SM'),
    '380' => array('name' => 'UKRAINE', 'code' => '380', 'short_name' => 'UA'),
    '381' => array('name' => 'KOSOVO', 'code' => '381', 'short_name' => 'XK'),
    '382' => array('name' => 'MONTENEGRO', 'code' => '382', 'short_name' => 'ME'),
    '385' => array('name' => 'CROATIA', 'code' => '385', 'short_name' => 'HR'),
    '386' => array('name' => 'SLOVENIA', 'code' => '386', 'short_name' => 'SI'),
    '387' => array('name' => 'BOSNIA AND HERZEGOVINA', 'code' => '387', 'short_name' => 'BA'),
    '389' => array('name' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'code' => '389', 'short_name' => 'MK'),
    '420' => array('name' => 'CZECH REPUBLIC', 'code' => '420', 'short_name' => 'CZ'),
    '421' => array('name' => 'SLOVAKIA', 'code' => '421', 'short_name' => 'SK'),
    '423' => array('name' => 'LIECHTENSTEIN', 'code' => '423', 'short_name' => 'LI'),
    '500' => array('name' => 'FALKLAND ISLANDS (MALVINAS)', 'code' => '500', 'short_name' => 'FK'),
    '501' => array('name' => 'BELIZE', 'code' => '501', 'short_name' => 'BZ'),
    '502' => array('name' => 'GUATEMALA', 'code' => '502', 'short_name' => 'GT'),
    '503' => array('name' => 'EL SALVADOR', 'code' => '503', 'short_name' => 'SV'),
    '504' => array('name' => 'HONDURAS', 'code' => '504', 'short_name' => 'HN'),
    '505' => array('name' => 'NICARAGUA', 'code' => '505', 'short_name' => 'NI'),
    '506' => array('name' => 'COSTA RICA', 'code' => '506', 'short_name' => 'CR'),
    '507' => array('name' => 'PANAMA', 'code' => '507', 'short_name' => 'PA'),
    '508' => array('name' => 'SAINT PIERRE AND MIQUELON', 'code' => '508', 'short_name' => 'PM'),
    '509' => array('name' => 'HAITI', 'code' => '509', 'short_name' => 'HT'),
    '590' => array('name' => 'SAINT BARTHELEMY', 'code' => '590', 'short_name' => 'BL'),
    '591' => array('name' => 'BOLIVIA', 'code' => '591', 'short_name' => 'BO'),
    '592' => array('name' => 'GUYANA', 'code' => '592', 'short_name' => 'GY'),
    '593' => array('name' => 'ECUADOR', 'code' => '593', 'short_name' => 'EC'),
    '595' => array('name' => 'PARAGUAY', 'code' => '595', 'short_name' => 'PY'),
    '597' => array('name' => 'SURINAME', 'code' => '597', 'short_name' => 'SR'),
    '598' => array('name' => 'URUGUAY', 'code' => '598', 'short_name' => 'UY'),
    '599' => array('name' => 'NETHERLANDS ANTILLES', 'code' => '599', 'short_name' => 'AN'),
    '670' => array('name' => 'TIMOR-LESTE', 'code' => '670', 'short_name' => 'TL'),
    '672' => array('name' => 'ANTARCTICA', 'code' => '672', 'short_name' => 'AQ'),
    '673' => array('name' => 'BRUNEI DARUSSALAM', 'code' => '673', 'short_name' => 'BN'),
    '674' => array('name' => 'NAURU', 'code' => '674', 'short_name' => 'NR'),
    '675' => array('name' => 'PAPUA NEW GUINEA', 'code' => '675', 'short_name' => 'PG'),
    '676' => array('name' => 'TONGA', 'code' => '676', 'short_name' => 'TO'),
    '677' => array('name' => 'SOLOMON ISLANDS', 'code' => '677', 'short_name' => 'SB'),
    '678' => array('name' => 'VANUATU', 'code' => '678', 'short_name' => 'VU'),
    '679' => array('name' => 'FIJI', 'code' => '679', 'short_name' => 'FJ'),
    '680' => array('name' => 'PALAU', 'code' => '680', 'short_name' => 'PW'),
    '681' => array('name' => 'WALLIS AND FUTUNA', 'code' => '681', 'short_name' => 'WF'),
    '682' => array('name' => 'COOK ISLANDS', 'code' => '682', 'short_name' => 'CK'),
    '683' => array('name' => 'NIUE', 'code' => '683', 'short_name' => 'NU'),
    '685' => array('name' => 'SAMOA', 'code' => '685', 'short_name' => 'WS'),
    '686' => array('name' => 'KIRIBATI', 'code' => '686', 'short_name' => 'KI'),
    '687' => array('name' => 'NEW CALEDONIA', 'code' => '687', 'short_name' => 'NC'),
    '688' => array('name' => 'TUVALU', 'code' => '688', 'short_name' => 'TV'),
    '689' => array('name' => 'FRENCH POLYNESIA', 'code' => '689', 'short_name' => 'PF'),
    '690' => array('name' => 'TOKELAU', 'code' => '690', 'short_name' => 'TK'),
    '691' => array('name' => 'MICRONESIA, FEDERATED STATES OF', 'code' => '691', 'short_name' => 'FM'),
    '692' => array('name' => 'MARSHALL ISLANDS', 'code' => '692', 'short_name' => 'MH'),
    '850' => array('name' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF', 'code' => '850', 'short_name' => 'KP'),
    '852' => array('name' => 'HONG KONG', 'code' => '852', 'short_name' => 'HK'),
    '853' => array('name' => 'MACAU', 'code' => '853', 'short_name' => 'MO'),
    '855' => array('name' => 'CAMBODIA', 'code' => '855', 'short_name' => 'KH'),
    '856' => array('name' => 'LAO PEOPLES DEMOCRATIC REPUBLIC', 'code' => '856', 'short_name' => 'LA'),
    '870' => array('name' => 'PITCAIRN', 'code' => '870', 'short_name' => 'PN'),
    '880' => array('name' => 'BANGLADESH', 'code' => '880', 'short_name' => 'BD'),
    '886' => array('name' => 'TAIWAN, PROVINCE OF CHINA', 'code' => '886', 'short_name' => 'TW'),
    '960' => array('name' => 'MALDIVES', 'code' => '960', 'short_name' => 'MV'),
    '961' => array('name' => 'LEBANON', 'code' => '961', 'short_name' => 'LB'),
    '962' => array('name' => 'JORDAN', 'code' => '962', 'short_name' => 'JO'),
    '963' => array('name' => 'SYRIAN ARAB REPUBLIC', 'code' => '963', 'short_name' => 'SY'),
    '964' => array('name' => 'IRAQ', 'code' => '964', 'short_name' => 'IQ'),
    '965' => array('name' => 'KUWAIT', 'code' => '965', 'short_name' => 'KW'),
    '966' => array('name' => 'SAUDI ARABIA', 'code' => '966', 'short_name' => 'SA'),
    '967' => array('name' => 'YEMEN', 'code' => '967', 'short_name' => 'YE'),
    '968' => array('name' => 'OMAN', 'code' => '968', 'short_name' => 'OM'),
    '971' => array('name' => 'UNITED ARAB EMIRATES', 'code' => '971', 'short_name' => 'AE'),
    '972' => array('name' => 'ISRAEL', 'code' => '972', 'short_name' => 'IL'),
    '973' => array('name' => 'BAHRAIN', 'code' => '973', 'short_name' => 'BH'),
    '974' => array('name' => 'QATAR', 'code' => '974', 'short_name' => 'QA'),
    '975' => array('name' => 'BHUTAN', 'code' => '975', 'short_name' => 'BT'),
    '976' => array('name' => 'MONGOLIA', 'code' => '976', 'short_name' => 'MN'),
    '977' => array('name' => 'NEPAL', 'code' => '977', 'short_name' => 'NP'),
    '992' => array('name' => 'TAJIKISTAN', 'code' => '992', 'short_name' => 'TJ'),
    '993' => array('name' => 'TURKMENISTAN', 'code' => '993', 'short_name' => 'TM'),
    '994' => array('name' => 'AZERBAIJAN', 'code' => '994', 'short_name' => 'AZ'),
    '995' => array('name' => 'GEORGIA', 'code' => '995', 'short_name' => 'GE'),
    '996' => array('name' => 'KYRGYZSTAN', 'code' => '996', 'short_name' => 'KG'),
    '998' => array('name' => 'UZBEKISTAN', 'code' => '998', 'short_name' => 'UZ'),
    '1242' => array('name' => 'BAHAMAS', 'code' => '1242', 'short_name' => 'BS'),
    '1246' => array('name' => 'BARBADOS', 'code' => '1246', 'short_name' => 'BB'),
    '1264' => array('name' => 'ANGUILLA', 'code' => '1264', 'short_name' => 'AI'),
    '1268' => array('name' => 'ANTIGUA AND BARBUDA', 'code' => '1268', 'short_name' => 'AG'),
    '1284' => array('name' => 'VIRGIN ISLANDS, BRITISH', 'code' => '1284', 'short_name' => 'VG'),
    '1340' => array('name' => 'VIRGIN ISLANDS, U.S.', 'code' => '1340', 'short_name' => 'VI'),
    '1345' => array('name' => 'CAYMAN ISLANDS', 'code' => '1345', 'short_name' => 'KY'),
    '1441' => array('name' => 'BERMUDA', 'code' => '1441', 'short_name' => 'BM'),
    '1473' => array('name' => 'GRENADA', 'code' => '1473', 'short_name' => 'GD'),
    '1599' => array('name' => 'SAINT MARTIN', 'code' => '1599', 'short_name' => 'MF'),
    '1649' => array('name' => 'TURKS AND CAICOS ISLANDS', 'code' => '1649', 'short_name' => 'TC'),
    '1664' => array('name' => 'MONTSERRAT', 'code' => '1664', 'short_name' => 'MS'),
    '1670' => array('name' => 'NORTHERN MARIANA ISLANDS', 'code' => '1670', 'short_name' => 'MP'),
    '1671' => array('name' => 'GUAM', 'code' => '1671', 'short_name' => 'GU'),
    '1684' => array('name' => 'AMERICAN SAMOA', 'code' => '1684', 'short_name' => 'AS'),
    '1758' => array('name' => 'SAINT LUCIA', 'code' => '1758', 'short_name' => 'LC'),
    '1767' => array('name' => 'DOMINICA', 'code' => '1767', 'short_name' => 'DM'),
    '1784' => array('name' => 'SAINT VINCENT AND THE GRENADINES', 'code' => '1784', 'short_name' => 'VC'),
    '1809' => array('name' => 'DOMINICAN REPUBLIC', 'code' => '1809', 'short_name' => 'DO'),
    '1868' => array('name' => 'TRINIDAD AND TOBAGO', 'code' => '1868', 'short_name' => 'TT'),
    '1869' => array('name' => 'SAINT KITTS AND NEVIS', 'code' => '1869', 'short_name' => 'KN'),
    '1876' => array('name' => 'JAMAICA', 'code' => '1876', 'short_name' => 'JM'),
);
// End Phu Vo

// Added by Phuc on 2020.07.14 to config address field for modules
$addressConfig = array(
    'address_fields_map' => array(
        'Accounts' => array('bill_street', 'ship_street'),
        'CPTarget' => array('lane'),
        'Leads' => array('lane'),
        'Contacts' => array('mailingstreet', 'otherstreet'),
        'Calendar' => array('location'),
        'Vendors' => array('street'),
        'Quotes' => array('bill_street', 'ship_street'),
        'Invoice' => array('bill_street', 'ship_street'),
        'SalesOrder' => array('bill_street', 'ship_street'),
        'PurchaseOrder' => array('bill_street', 'ship_street'),
    ),
    'coordinates_refresh_interval' => 720,  // Minutes
);
// Ended by Phuc

// Added by Phuc on 2020.08.04 to define customer modules
$customerModules = ['Contacts', 'Leads', 'CPTarget'];
// Ended by Phuc

$workflowConfig = array(
    'max_schedule_workflows' => 10, // Define how many schedule workflows can be created. WARNING: larger value may lead the system to slow down!!!
);

// Added by Phu Vo on 2021.03.18
// Modified by Vu Mai on 2023-03-03
$loginPageConfig = [
    'en_us' => [
        'icon' => 'https://cloudgo.vn/crm_icons/England.png',
        'language' => 'English',
        'main_title' => 'SOLUTIONS</br> DIGITAL TRANSFORMATION',
        'main_links' => [
            ['text' => 'Overview of CRM software', 'url' => 'https://cloudgo.vn/tong-quan-ve-crm'],
            ['text' => 'Detailed user manual', 'url' => 'https://docs.onlinecrm.vn/'],
            ['text' => 'Online support', 'url' => 'https://www.messenger.com/t/137541699619429'],
        ],
        'social_buttons' => [
            ['image' => 'https://cloudgo.vn/media/social_icons/facebook.png', 'url' => 'https://www.facebook.com/cloudgovn'], 
            ['image' => 'https://cloudgo.vn/media/social_icons/youtube.png', 'url' => 'https://www.youtube.com/channel/UCtAmsHdNh-wbSmTkmdGOkTA'],
            ['image' => 'https://cloudgo.vn/media/social_icons/linkedin.png', 'url' => 'https://www.linkedin.com/company/onlinecrm-giai-phap-crm-chuyen-sau-theo-nganh'],
            ['image' => 'https://cloudgo.vn/media/social_icons/twitter.png', 'url' => 'https://twitter.com/cloudgovn'], 
            ['image' => 'https://cloudgo.vn/media/social_icons/zalo.png', 'url' => 'https://zalo.me/2690296811687754643'],
        ],
        'hotline' => '1900 29 29 90',
        'footer_links' => [
            ['text' => 'About CloudGO', 'url' => 'https://cloudgo.vn/'],
            ['text' => 'Help', 'url' => 'http://docs.onlinecrm.vn/'],
        ],
    ],
    'vn_vn' => [
        'icon' => 'https://cloudgo.vn/crm_icons/Vietnam.png',
        'language' => 'Tiếng Việt',
        'main_title' => 'GIẢI PHÁP</br> CHUYỂN ĐỔI SỐ TINH GỌN',
        'main_links' => [
            ['text' => 'Giới thiệu tổng quan phần mềm', 'url' => 'https://cloudgo.vn/tong-quan-ve-crm'],
            ['text' => 'Hướng dẫn sử dụng chi tiết các chức năng', 'url' => 'https://docs.onlinecrm.vn/'],
            ['text' => 'Hỗ trợ trực tuyến', 'url' => 'https://www.messenger.com/t/137541699619429'],
        ],
        'social_buttons' => [
            ['image' => 'https://cloudgo.vn/media/social_icons/facebook.png', 'url' => 'https://www.facebook.com/cloudgovn'],
            ['image' => 'https://cloudgo.vn/media/social_icons/youtube.png', 'url' => 'https://www.youtube.com/channel/UCtAmsHdNh-wbSmTkmdGOkTA'],
            ['image' => 'https://cloudgo.vn/media/social_icons/linkedin.png', 'url' => 'https://www.linkedin.com/company/onlinecrm-giai-phap-crm-chuyen-sau-theo-nganh'],
            ['image' => 'https://cloudgo.vn/media/social_icons/twitter.png', 'url' => 'https://twitter.com/cloudgovn'],
            ['image' => 'https://cloudgo.vn/media/social_icons/zalo.png', 'url' => 'https://zalo.me/2690296811687754643'],
        ],
        'hotline' => '1900 29 29 90',
        'footer_links' => [
            ['text' => 'Về CloudGO', 'url' => 'https://cloudgo.vn/'],
            ['text' => 'Hướng dẫn sử dụng', 'url' => 'http://docs.onlinecrm.vn/'],
        ],
    ]
];
// End Phu Vo

// Added by Phu Vo on 2021.04.21
$moduleIcons = array(
    'Default' => 'fad fa-puzzle-piece',
    'Potentials' => 'fad fa-sack-dollar',
    'Contacts' => 'fad fa-user-tie',
    'Accounts' => 'fad fa-building',
    'Leads' => 'fad fa-user',
    'Documents' => 'fad fa-folder-open',
    'Calendar' => 'fad fa-calendar',
    'Calendar-Event' => 'fad fa-calendar-star',
    'Calendar-Call' => 'fad fa-phone',
    'Calendar-Meeting' => 'fad fa-users-class',
    'Calendar-Task' => 'fad fa-tasks',
    'Emails' => 'fad fa-envelope',
    'HelpDesk' => 'fad fa-file-exclamation',
    'Products' => 'fad fa-box',
    'Faq' => 'fad fa-question-circle',
    'Events' => 'fad fa-calendar-star',
    'Vendors' => 'fad fa-warehouse',
    'PriceBooks' => 'fad fa-book',
    'Quotes' => 'fad fa-file-spreadsheet',
    'PurchaseOrder' => 'fad fa-file-alt',
    'SalesOrder' => 'fad fa-file-invoice',
    'Invoice' => 'fad fa-file-invoice-dollar',
    'Rss' => 'fad fa-rss-square',
    'Reports' => 'fad fa-chart-bar',
    'Campaigns' => 'fad fa-bullhorn',
    'Portal' => 'fad fa-star',
    'Webmails' => 'fad fa-envelope-square',
    'Users' => 'fad fa-user-alt',
    'Import' => 'fad fa-download',
    'MailManager' => 'fad fa-envelope',
    'Mobile' => 'fad fa-mobile-alt',
    'ModTracker' => 'fad fa-history',
    'PBXManager' => 'fad fa-phone-office',
    'ServiceContracts' => 'fad fa-file-signature',
    'Services' => 'fad fa-hand-holding-box',
    'WSAPP' => 'fad fa-mailbox',
    'Assets' => 'fad fa-cabinet-filing',
    'CustomerPortal' => 'fad fa-browser',
    'EmailTemplates' => 'fad fa-envelope',
    'Google' => 'fad fa-goModCommentsogle',
    'ModComments' => 'fad fa-comments-alt',
    'ProjectMilestone' => 'fad fa-flag',
    'ProjectTask' => 'fad fa-clipboard-check',
    'Project' => 'fad fa-briefcase',
    'RecycleBin' => 'fad fa-trash-alt',
    'SMSNotifier' => 'fad fa-sms',
    'Webforms' => 'fad fa-wpforms',
    'ExtensionStore' => 'fad fa-store',
    'CPSMSTemplate' => 'fad fa-book-alt',
    'CPKanban' => 'fad fa-columns',
    'CPNotifications' => 'fad fa-bell',
    'CPTargetList' => 'fad fa-users-class',
    'CPSocialIntegration' => 'fa-light fa-share-nodes',
    'CPSocialMessageTemplate' => 'fad fa-comment-lines',
    'CPSocialArticle' => 'fad fa-newspaper',
    'CPTarget' => 'fad fa-file-user',
    'CPSocialMessageLog' => 'fad fa-comment-dots',
    'CPSocialArticleLog' => 'fad fa-newspaper',
    'CPSocialFeedback' => 'fad fa-comment-exclamation',
    'CPReceipt' => 'fad fa-file-download',
    'CPAssetAccount' => 'fad fa-credit-card',
    'CPPayment' => 'fad fa-file-upload',
    'CPTransferMoney' => 'fad fa-hand-holding-usd',
    'CPReportDebits' => 'fad fa-chart-line',
    'CPMauticIntegration' => 'fad fa-puzzle-piece',
    'CPMauticContactHistory' => 'fad fa-history',
    'CPComboProducts' => 'fad fa-box-full',
    'CPChatBotIntegration' => 'fad fa-puzzle-piece',
    'CPChatMessageLog' => 'fad fa-comment-dots',
    'CPLocalization' => 'fad fa-map-marked-alt',
    'CPEventManagement' => 'fad fa-calendar',
    'CPEventRegistration' => 'fad fa-registered',
    'CPSMSOTTMessageLog' => 'fad fa-comment-dots',
    'CPAICameraIntegration' => 'fad fa-cctv',
    'CPEmployee' => 'fad fa-address-book',
    'CPEmployeeCheckinLog' => 'fad fa-user-check',
    'CPZaloAdsForm' => 'fad fa-list-alt',
    'CPSLACategory' => 'fad fa-stopwatch',
    'CPTicketCommunicationLog' => 'fad fa-comments',
    'CPOTTIntegration' => 'fad fa-message-plus',
    'CPTelesales' => 'fad fa-user-headset',
);
// End Phu Vo

// Added by Tin Bui on 2022.03.15 - Ticket configs
$ticketConfigs = [
    'file_upload_validation' => [
        'max_upload_files' => 5, // -1 ~ unlimited, 
        'max_total_files_size' => 25600, // (KB)
    ],
    'survey_form_lifetime' => 10, // Days number survey form available
    'max_days_can_reopen' => 3, // Days number reopen ticket when reply ticket email
    'auto_close_hours' => 24, // Auto close tickets in wait close after 24 hours
];
// Ended by Tin Bui

// Added by Vu Mai on 2023-03-03
$companyContactInfos = [
    'phone' => [
        'icon' => 'resources/images/icon-phone.svg',
        'url' => '',
        'value' => '1900 29 29 90',
    ],
    'zalo' => [
        'icon' => 'resources/images/icon-zalo.png',
        'url' => 'https://zalo.me/2690296811687754643',
        'value' => 'CÔNG TY TNHH CÔNG NGHỆ CLOUDGO',
    ],
    'facebook' => [
        'icon' => 'resources/images/icon-facebook.svg',
        'url' => 'https://www.facebook.com/cloudgovn',
        'value' => 'Cloudgo.vn - Bộ giải pháp chuyển đổi số tinh gọn',
    ],
    'mail' => [
        'icon' => 'resources/images/icon-mail.png',
        'url' => 'mailto:support@cloudgo.vn',
        'value' => 'support@cloudgo.vn',
    ],
];
// End Vu Mai

// In case using shared host, upload wkhtmltopdf inside CRM folder and point $wkhtmltopdfPath to its binary file absolute path. Ex: /home/abc/<domains>/wkhtmltopdf/bin/wkhtmltopdf
$wkhtmltopdfPath = 'wkhtmltopdf';

// Added by Tung Nguyen on 2022.04.19 - Custom Picklist field: Ignore sort picklist field by its picklist value order with custom picklist field
$customPickListFields = [];

// Load custom config. ALL CONFIG MUST BE BEFORE THIS LINE!!!
$customConfigFile = 'config_override.cus.php';

if (file_exists($customConfigFile)) {
    require_once('include/utils/CustomConfigUtils.php');
    CustomConfigUtils::loadCustomConfigs();
}
// End File. DO NOT ADD ANY LINE AFTER THIS LINE!!!