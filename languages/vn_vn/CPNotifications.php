<?php

$languageStrings = Array(
	'CPNotifications' => 'Thông báo',
	'SINGLE_CPNotifications' => 'Thông báo',
	'ModuleName ID' => 'Thông báo ID',
	'LBL_NOTIFICATION_LIST_EMPTY' => 'Không có thông báo nào',
	'LBL_REMINDER_LIST_EMPTY' => 'Không có cảnh báo nào',

	// Added by Phu Vo on 2019.03.22
	'LBL_CONFIG_USER_NOTIFICATION_TITLE' => 'Thiết lập thông báo người dùng',
	'LBL_CONFIG_SYSTEM_NOTIFICATION_TITLE' => 'Thiết lập thông báo hệ thống',
	'LBL_CONFIG_NOTIFICAITON' => 'Tùy chỉnh cảnh báo',
	'LBL_CONFIG_SYSTEM_NOTIFICAITON' => 'Cấu hình cảnh báo hệ thống',
	'LBL_NOTIFICATION_INFO' => 'Thông tin thông báo',
	'LBL_RECEIVE_NOTIFICATION' => 'Nhận thông báo',
	'LBL_NOTIFY_CUSTOMER_BIRTHDAY' => 'Thông báo sinh nhật khách hàng',
	'LBL_NOTIFY_ASSIGN_TASK' => 'Nhận thông báo giao việc (giao Task, giao Lead, giao Opportunity...',
	'LBL_NOTIFY_UPDATE_PROFILE' => 'Nhận thông báo cập nhật trên hồ sơ đang phụ trách',
	'LBL_NOTIFY_OVERDUE_TASK' => 'Thông báo task quá hạn, activity gần đến hạn, hợp đồng sắp hết hạn',
	'LBL_NOTIFY_UPDATE_FOLLOWING_RECORD' => 'Nhận thông báo cập nhật trên hồ sơ đang theo dõi',
	'LBL_NOTIFY_METHOD' => 'Nhận thông báo theo hình thức',
	'LBL_NOTIFY_BY_POPUP' => 'Popup',
	'LBL_NOTIFY_BY_APP' => 'App',
	'LBL_NOTIFY_CONFIG_DUEDAY_COMING' => 'Cài đặt nhắc công việc sắp đến hạn trước',
	'LBL_NOTIFY_FOLLOW' => 'Nhận thông tin theo dõi',
	'LBL_REFRESH_TASK_NOTIFY_INTERVAL' => 'Tự động nhắc nhở công việc (Call/Task/Meeting) trước',
	'LBL_TASK_GOING_TO_OVERDUE' => 'Sắp đến hạn',
	'LBL_TASK_OVERDUE' => 'Quá hạn',
	'LBL_BIRTHDAY_TODAY' => 'Hôm nay',
	'LBL_BIRTHDAY_COMING' => 'Sắp đến',
	'LBL_REMIND_COMING_MINUTES' => 'phút',
	'LBL_REMIND_COMING_HOUR' => 'giờ',
	'LBL_REMIND_COMING_HOURS' => 'giờ',
	'LBL_REMIND_COMING_DAY' => 'ngày',
	'LBL_REMIND_COMING_DAYS' => 'ngày',
	'LBL_BTN_DISMISS' => 'Bỏ qua',
	'LBL_BTN_VIEW' => 'Xem',
	'LBL_NOTIFY_POPUP_TIPS' => 'Nhấp vào nút "Xem" để xem chi tiết hồ sơ hoặc nút "Bỏ qua" để bỏ qua thông báo này',
	'LBL_TITLE_NOTIFICATION' => 'Nhắc nhở, cảnh báo',
	'LBL_TITLE_ACTIVITY' => 'Cảnh báo hoạt động đến hạn/quá hạn',
	'LBL_TITLE_BIRTHDAY' => 'Nhắc nhở sinh nhật khách hàng',
	'LBL_READ_ALL' => 'Đánh dấu tất cả đã đọc',
	'LBL_CONFIGS' => 'Thiết lập',
	
	// message language mapping
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_COMING' => 'Bạn có %activity_type <strong>%record_name</strong> <strong>%coming_days</strong> ngày nữa phải hoàn thành <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_TODAY' => 'Bạn có %activity_type <strong>%record_name</strong> phải hoàn thành trong hôm nay <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_CONTRACT_COMING' => 'Hợp đồng <strong>%record_name</strong> <strong>%coming_days</strong> ngày nữa sẽ hết hạn <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_ACTIVITY_OVERDUE' => '%activity_type <strong>%record_name</strong> đã quá hạn <strong>%overdue_days</strong> ngày <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_CONTRACT_OVERDUE' => 'Hợp đồng <strong>%record_name</strong> đã hết hạn <strong>%overdue_days</strong> ngày <i>(%due_time)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_TODAY' => 'Sinh nhật lần thứ <strong>%birthday_count</strong> của <strong>%customer_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_BIRTHDAY_COMING' => 'Còn <strong>%coming_days</strong> ngày nữa đến sinh nhật <strong>%customer_name</strong> <i>(%birthday)</i>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_INVITE' => '<strong>%inviter</strong> đã mời bạn tham gia %activity_type <strong>%activity_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_COMMENT' => '<strong>%commenter</strong> đã bình luận vào %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_CLOSE_DEAL' => '<strong>%updater</strong> đã chốt <strong>Thành công</strong> Cơ hội: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN' => '<strong>%assigner</strong> đã giao cho bạn %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_ASSIGN_GROUP' => '<strong>%assigner</strong> đã giao cho nhóm <strong>%group_name</strong> một %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_MASS_ASSIGN' => 'Người dùng <strong>%assigner</strong> đã giao cho bạn <strong>%activity_count</strong> %activity_type. Vui lòng kiểm tra lại danh sách dữ liệu đang phụ trách.',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE' => '%activity_type <strong>%record_name</strong> của bạn đã được cập nhật bởi <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_GROUP' => '%activity_type <strong>%record_name</strong> của nhóm <strong>%group_name</strong> đã được cập nhật bởi <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_STARRED' => '%activity_type <strong>%record_name</strong> mà bạn đang theo dõi đã được cập nhật bởi <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_ALERT_MISSED_CALL' => 'Bạn có một cuộc gọi nhỡ từ %customer_type <strong>%customer_name</strong>', // Added by Phu Vo on 2019.06.07 for missed call alert message
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER' => '%activity_type <strong>%record_name</strong> của bạn đã được giao lại cho <strong>%new_main_owner</strong> làm người phụ trách bởi <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFICATION_UPDATE_MAIN_OWNER_GROUP' => '%activity_type <strong>%record_name</strong> của bạn đã được giao lại cho <strong>%new_main_owner</strong> bởi <strong>%updater</strong>',
	'MSG_NOTIFICATIONS_MESSAGE_INBOUND_MSG' => 'Tin nhắn %channel mới từ <strong>%sender_name</strong>: "%message"',
	'MSG_NOTIFICATIONS_REPLY_COMMENT' => '<strong>%commenter</strong> đã trả lời bình luận của bạn trong %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_MENTION_COMMENT' => '<strong>%commenter</strong> đã nhắc đến bạn trong một bình luận trong %activity_type: <strong>%record_name</strong>',
	'MSG_NOTIFICATIONS_TRANSFER_CHAT_MSG' => '<strong>%assigner</strong> đã chuyển cho bạn phụ trách chat %channel với %activity_type: <strong>%customer_name</strong>',
	// End Phu Vo

	// Added by Hieu Nguyen on 2021-04-05
	'MSG_NOTIFICATIONS_EMPLOYEE_CHECKIN' => '<strong>%employee_name</strong> đã checkin lúc <strong>%checkin_time</strong>',
	// End Hieu Nguyen

	// Added by Tin Bui on 2022.02.18
	'MSG_NOTIFICATIONS_MESSAGE_NOTIFY_CUSTOMER_WAS_REPLY' => 'Khách hàng đã phản hồi ticket <strong>%ticket_number</strong> của bạn. Vui lòng tiếp tục xử lý!',
	// Ended by Tin Bui

	// Added by Hieu Nguyen on 2022-06-09
	'LBL_NOTIFY_SUBTAB_UPDATES' => 'Cập nhật',
	'LBL_NOTIFY_SUBTAB_CHECKINS' => 'Check-in',
	// End Hieu Nguyen
);

$jsLanguageStrings = Array(
	// Added by Hieu Nguyen on 2022-06-30
	'JS_BTN_ACCEPT' => 'Chấp nhận',
	'JS_BTN_DECLINE' => 'Từ chối',
	'JS_ACCEPT_INVITATION_SUCCESS_MSG' => 'Chấp nhận lời mời thành công!',
	'JS_ACCEPT_INVITATION_ERROR_MSG' => 'Không thể chấp nhận lời mời. Bạn vui lòng thử lại!',
	'JS_DECLINE_INVITATION_SUCCESS_MSG' => 'Từ chối lời mới thành công!',
	'JS_DECLINE_INVITATION_ERROR_MSG' => 'Không thể từ chối lời mời. Bạn vui lòng thử lại!',
	// End Hieu Nguyen
);