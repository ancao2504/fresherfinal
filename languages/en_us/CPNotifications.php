<?php

$languageStrings = Array(
	'CPNotifications' => 'Notifications',
	'SINGLE_CPNotifications' => 'Notifications',
	'ModuleName ID' => 'Notifications ID',
	'LBL_NOTIFICATION_LIST_EMPTY' => 'No notifications',
	'LBL_REMINDER_LIST_EMPTY' => 'No reminders',

	// Added by Phu Vo on 2019.03.22
	'LBL_CONFIG_USER_NOTIFICATION_TITLE' => 'Config User Notifications',
	'LBL_CONFIG_SYSTEM_NOTIFICATION_TITLE' => 'Config System Notifications',
	'LBL_CONFIG_NOTIFICAITON' => 'Notification Configuration',
	'LBL_CONFIG_SYSTEM_NOTIFICAITON' => 'System Notification Configuration',
	'LBL_NOTIFICATION_INFO' => 'Notifications',
	'LBL_RECEIVE_NOTIFICATION' => 'Receive Notifications',
	'LBL_NOTIFY_CUSTOMER_BIRTHDAY' => 'Receive Customer Birthday Notifications',
	'LBL_NOTIFY_ASSIGN_TASK' => 'Receive Task Assignment Notifications (Task, Lead, Opportunity...)',
	'LBL_NOTIFY_UPDATE_PROFILE' => 'Receive Record Updating Notifications',
	'LBL_NOTIFY_OVERDUE_TASK' => 'Receive Over Duedate Task, Ticket, Contract Notificaitions',
	'LBL_NOTIFY_UPDATE_FOLLOWING_RECORD' => 'Receive Starred Record Update Notifications',
	'LBL_NOTIFY_METHOD' => 'Receive Notifications Method',
	'LBL_NOTIFY_BY_POPUP' => 'Popup',
	'LBL_NOTIFY_BY_APP' => 'App',
	'LBL_NOTIFY_CONFIG_DUEDAY_COMING' => 'Config Dueday Coming Notify',
	'LBL_NOTIFY_FOLLOW' => 'Receive Follow Notifications',
	'LBL_REFRESH_TASK_NOTIFY_INTERVAL' => 'Task Notify Interval',
	'LBL_TASK_GOING_TO_OVERDUE' => 'Coming',
	'LBL_TASK_OVERDUE' => 'Overdue',
	'LBL_BIRTHDAY_TODAY' => 'Today',
	'LBL_BIRTHDAY_COMING' => 'Coming',
	'LBL_REMIND_COMING_MINUTES' => 'Minutes',
	'LBL_REMIND_COMING_HOUR' => 'hour',
	'LBL_REMIND_COMING_HOURS' => 'hours',
	'LBL_REMIND_COMING_DAY' => 'day',
	'LBL_REMIND_COMING_DAYS' => 'days',
	'LBL_BTN_DISMISS' => 'Dismiss',
	'LBL_BTN_VIEW' => 'View',
	'LBL_NOTIFY_POPUP_TIPS' => 'Click "View" to view this record\'s detail or click "Cancel" to dismiss this message',
	'LBL_TITLE_NOTIFICATION' => 'Notifications',
	'LBL_TITLE_ACTIVITY' => 'Activity reminder',
	'LBL_TITLE_BIRTHDAY' => 'Customer\'s birthday notifications',
	'LBL_READ_ALL' => 'Mark all as read',
	'LBL_CONFIGS' => 'Configs',
	
	// message language mapping
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_COMING' => 'You have %activity_type <strong>%record_name</strong> has to be done in <strong>%coming_days</strong> day(s) <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_TODAY' => 'You have %activity_type <strong>%record_name</strong> has to be done today <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_CONTRACT_COMING' => 'Contract <strong>%record_name</strong> will be expired in <strong>%coming_days</strong> day(s) <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_OVERDUE' => '%activity_type <strong>%record_name</strong> was over due <strong>%overdue_days</strong> day(s) ago<i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_CONTRACT_OVERDUE' => 'Contract <strong>%record_name</strong> was over due <strong>%overdue_days</strong> day(s) <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_TODAY' => '<strong>%customer_name</strong>\'s <strong>%birthday_count</strong> birthday',
	'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_COMING' => '<strong>%coming_days</strong> day(s) to <strong>%customer_name</strong>\'s birthday <i>(%birthday)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_INVITE' => '<strong>%inviter</strong> has invited you to join in %activity_type <strong>%activity_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_COMMENT' => '<strong>%commenter</strong> has commented in %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_CLOSE_DEAL' => '<strong>%updater</strong> has <strong>Won</strong> a deal: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN' => '<strong>%assigner</strong> has assigned you a(n) %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN_GROUP' => '<strong>%assigner</strong> has assigned %group_name a(n) %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_MASS_ASSIGN' => 'User <strong>%assigner</strong> has assigned you <strong>%activity_count</strong> %activity_type(s/es). Please check your assigned data.',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE' => 'Your %activity_type <strong>%record_name</strong> has been updated by <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_GROUP' => '<strong>%group_name</strong>\'s %activity_type <strong>%record_name</strong> has been updated by <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_STARRED' => '%activity_type <strong>%record_name</strong> that you are following has been updated by <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_ALERT_MISSED_CALL' => 'You have a missed call from %customer_type <strong>%customer_name</strong>', // Added by Phu Vo on 2019.06.07 for missed call alert message
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER' => 'Your %activity_type <strong>%record_name</strong> has been transfer to <strong>%new_main_owner</strong> as main owner by <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER_GROUP' => 'Your %activity_type <strong>%record_name</strong> has been transfer to <strong>%new_main_owner</strong> by <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_INBOUND_MSG' => 'New %channel message from <strong>%sender_name</strong>: "%message"',
	'MSG_NOTIFICATIONS_REPLY_COMMENT' => '<strong>%commenter</strong> has reply your comment in %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MENTION_COMMENT' => '<strong>%commenter</strong> has mentioned you in a comment in %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_TRANSFER_CHAT_MSG' => '<strong>%assigner</strong> has transfer to you %channel conversation with %activity_type: <strong>%customer_name</strong>',
	// End Phu Vo

	// Added by Hieu Nguyen on 2021-04-05
	'MSG_NOTIFICATIONS_EMPLOYEE_CHECKIN' => '<strong>%employee_name</strong> has checked in at <strong>%checkin_time</strong>',
	// End Hieu Nguyen

	// Added by Tin Bui on 2022.02.18
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFY_CUSTOMER_WAS_REPLY' => 'The customer responded to your ticket <strong>%ticket_number</strong>. Please continue processing!',
	// Ended by Tin Bui

	// Added by Hieu Nguyen on 2022-06-09
	'LBL_NOTIFY_SUBTAB_UPDATES' => 'Updates',
	'LBL_NOTIFY_SUBTAB_CHECKINS' => 'Check-ins',
	// End Hieu Nguyen
);

$jsLanguageStrings = Array(
	// Added by Hieu Nguyen on 2022-06-30
	'JS_BTN_ACCEPT' => 'Accept',
	'JS_BTN_DECLINE' => 'Decline',
	'JS_ACCEPT_INVITATION_SUCCESS_MSG' => 'Accepted the invitation successfully!',
	'JS_ACCEPT_INVITATION_ERROR_MSG' => 'Cannot accept the invitation. Please try again!',
	'JS_DECLINE_INVITATION_SUCCESS_MSG' => 'Declined the invitation successfully!',
	'JS_DECLINE_INVITATION_ERROR_MSG' => 'Cannot decline the invitation. Please try again!',
	// End Hieu Nguyen
);