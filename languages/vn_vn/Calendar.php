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
    // Basic Strings
	'Calendar'=>'Lịch',
	'SINGLE_Calendar' => 'Công việc',
	'SINGLE_Events' => 'Hoạt động',
	'LBL_ADD_TASK' => 'Thêm Công việc',
	'LBL_ADD_EVENT' => 'Thêm Hoạt động',
	'LBL_RECORDS_LIST' => 'Danh sách Công việc',
	'LBL_EVENTS' => 'Hoạt động',
	'LBL_TODOS' => 'Công việc',
	'LBL_CALENDAR_SETTINGS' => 'Thiết lập lịch',
	'LBL_CALENDAR_SHARING' => 'Chia sẻ lịch',
	'LBL_DEFAULT_EVENT_DURATION' => 'Thời gian diễn ra hoạt động',
	'LBL_CALL' => 'Cuộc gọi',
	'LBL_OTHER_EVENTS' => 'Hoạt động khác',
	'LBL_MINUTES' => 'Phút',
	'LBL_SELECT_USERS' => 'Chọn người dùng',
	'LBL_EVENT_OR_TASK' => 'Hoạt động / Công việc',
    'LBL_DEFAULT_STATUS_TYPE' => 'Tình trạng & Kiểu mặc định',
    'LBL_STATUS' => 'Tình trạng',
    'LBL_TYPE' => 'Loại',
	// Blocks
	'LBL_TASK_INFORMATION' => 'Chi tiết Công việc',
    'LBL_EVENT_INFORMATION'=> 'Thông tin Hoạt động',

	//Fields
	'Subject' => 'Tiêu đề',
	'Start Date & Time' => 'Ngày giờ bắt đầu',
	'Activity Type'=>'Loại',
	'Send Notification'=>'T.Báo khi thay đổi', // Modified by Phu Vo on 2020.02.20 Request #252
	'Location'=>'Địa điểm',
	'End Date & Time' => 'Ngày giờ kết thúc',
	'Visibility' => 'Chế độ chia sẻ', // Modified by Phu Vo on 2020.02.20 Request #252
	'Recurrence' => 'Lặp lại',
	
	//Visibility picklist values
	'Private' => 'Riêng tư',
	'Public' => 'Công khai',
	
	//Side Bar Names
	'LBL_ACTIVITY_TYPES' => 'Lịch của tôi',
	'LBL_CONTACTS_SUPPORT_END_DATE' => 'Ngày hỗ trợ cuối',
	'LBL_CONTACTS_BIRTH_DAY' => 'Ngày sinh',
	'LBL_ADDED_CALENDARS' => 'Lịch đã thêm',


	//Activity Type picklist values
	'Call' => 'Cuộc gọi',
	'Meeting' => 'Cuộc họp',
	'Task' => 'Công việc',

	//Status picklist values
	'Planned' => 'Lên kế hoạch',
	'Completed' => 'Hoàn thành',
	'Pending Input' => 'Đang chờ xử lý',
	'Not Started' => 'Chưa bắt đầu',
	'Deferred' => 'Đang hoãn',
	'Held' => 'Đã kết thúc',
	'Not Held' => 'Hủy',
	
	//Priority picklist values
	'Medium' => 'Trung bình',

	'LBL_CHANGE_OWNER' => 'Chuyển cho người khác',

	'LBL_EVENT' => 'Hoạt động',
	'LBL_TASK' => 'Công việc',
	'LBL_TASKS' => 'Công việc',

	'LBL_RECORDS_LIST' => 'Danh sách',
	'LBL_CALENDAR_VIEW' => 'Lịch của tôi',
	'LBL_SHARED_CALENDAR' => 'Lịch chia sẻ',

	//Repeat Lables - used by getTranslatedString
	'LBL_DAY0' => 'Chủ nhật',
	'LBL_DAY1' => 'Thứ 2',
	'LBL_DAY2' => 'Thứ 3',
	'LBL_DAY3' => 'Thứ 4',
	'LBL_DAY4' => 'Thứ 5',
	'LBL_DAY5' => 'Thứ 6',
	'LBL_DAY6' => 'Thứ 7',

	'first' => 'Đầu tiên',
	'last' => 'Cuối cùng',
	'LBL_DAY_OF_THE_MONTH' => 'ngày trong tháng',
	'LBL_ON' => 'trên',

	'Daily'=>'Hàng ngày',
	'Weekly'=>'Hàng tuần',
	'Monthly'=>'Hàng tháng',
	'Yearly'=>'Hàng năm',
	
	//Import and Export Labels
	'LBL_IMPORT_RECORDS' => 'Nhập dữ liệu',
	'LBL_RESULT' => 'Kết quả',
	'LBL_FINISH' => 'Kết thúc',
	'LBL_TOTAL_TASKS_IMPORTED' => 'Không có công việc nào được nhập ',
	'LBL_TOTAL_TASKS_SKIPPED' => 'Không có công việc nào bị bỏ qua ',
	'LBL_TOTAL_EVENTS_IMPORTED' => 'Không có hoạt động nào được nhập ',
	'LBL_TOTAL_EVENTS_SKIPPED' => 'Không có hoạt động nào bị bỏ qua ',
	
	'ICAL_FORMAT' => 'Định dạng cuộc gọi',
	'LBL_LAST_IMPORT_UNDONE'=>'Lần nhập liệu cuối cùng không hoàn thành',
	'LBL_UNDO_LAST_IMPORT' => 'Hủy lần nhập liệu cuối cùng',
	
	//Fixing colors for Shared Calendar and My Calendar
	'LBL_EDIT_COLOR' => 'Sửa màu sắc',
	'LBL_ADD_CALENDAR_VIEW' => 'Thêm chế độ xem lịch',
	'LBL_SELECT_USER_CALENDAR' => 'Chọn người dùng',
	'LBL_SELECT_CALENDAR_COLOR' => 'Chọn màu',
	'LBL_EDITING_CALENDAR_VIEW' => 'Sửa chế độ xem lịch',
	'LBL_DELETE_CALENDAR' => 'Xóa lịch',
	'LBL_SELECT_ACTIVITY_TYPE' => 'Chọn loại công việc',
	'Tasks' => 'Công việc',
	'LBL_SELECT_FIELDS_FOR_RANGE' => 'Chọn khoảng thời gian',
	'LBL_DUPLICATE_VIEW_EXIST' => 'Chế độ xem đã tồn tại',
    
    // For Event Invitation
    'LBL_ACTIVITY_NOTIFICATION' => 'Đây là thông báo khi có một công việc được giao cho bạn',
    'LBL_ACTIVITY_INVITATION' => 'Bạn vừa được nhắc tới trong một công việc',
    'LBL_DETAILS_STRING' => 'Thông tin chi tiết',
    'LBL_CREATED' => 'đã tạo',
    'LBL_UPDATED' => 'đã cập nhật',
    'Due Date' => 'Ngày kết thúc',
    'Priority' => 'Mức độ ưu tiên',
    'Related To' => 'Liên quan tới',
    'LBL_CONTACT_LIST' => 'Danh sách liên hệ',
    'LBL_APP_DESCRIPTION' => 'Mo tả',
    'LBL_REGARDS_STRING' => 'Trân trọng cảm ơn',
    'LBL_EVENT_INFORMATION' => 'Thông tin Hoạt động',
	'LBL_UPDATED_INVITATION' => 'Cập nhật lời mời',
	'LBL_INVITATION' => 'Lời mời',
	
	//Recurring Events
	'LBL_EDIT_RECURRING_EVENT' => 'Sửa hoạt động định kỳ',
	'LBL_ALL_EVENTS_EDIT_INFO' => 'Tất cả các hoạt động sẽ thay đổi. Bất cứ thay đổi nào cũng được lưu lại.',
	'LBL_FUTURE_EVENTS_EDIT_INFO' => 'Các hoạt động sau sẽ bị thay đổi, bất cứ thay đổi nào trong tương lai cũng không được giữ lại.',
	'LBL_ONLY_THIS_EVENT_EDIT_INFO' => 'Tất cả các hoạt động khác vẫn được giữ như cũ.',
	'LBL_EDIT_RECURRING_EVENTS_INFO' => 'Bạn có muốn lưu các thay đổi cho ',
	
	'LBL_DELETE_RECURRING_EVENT' => 'Xóa hoạt động định kỳ',
	'LBL_ALL_EVENTS_DELETE_INFO' => 'Tất cả các hoạt động trong chuỗi sẽ bị xóa.',
	'LBL_FUTURE_EVENTS_DELETE_INFO' => 'Hoạt động này và các hoạt động sau sẽ bị xóa.',
	'LBL_ONLY_THIS_EVENT_DELETE_INFO' => 'Tất cả các hoạt động trong chuỗi vẫn được giữ nguyên như cũ.',
	'LBL_DELETE_RECURRING_EVENTS_INFO' => 'Bạn có muốn xóa hoạt động nào?',
	'LBL_ONLY_THIS_EVENT' => 'Chỉ hoạt động này',
	'LBL_FUTURE_EVENTS' => 'Các hoạt động sau',
	'LBL_ALL_EVENTS' => 'Tất cả các hoạt động',
	
	//Reminder Email
	'LBL_REMINDER_NOTIFICATION' => 'Đây là thông báo cho các hoạt động',
    'LBL_SELECT_EVENT_TYPE' => 'Loại hoạt động',
    'LBL_THIS_WEEK' => 'Tuần này',
    'LBL_ADD_TASK_AND_PRESS_ENTER' => 'Thêm công việc và nhấn phím Enter',

	//Months
	'LBL_JANUARY' => 'Tháng 1',
	'LBL_FEBRUARY' => 'Tháng 2',
	'LBL_MARCH' => 'Tháng 3',
	'LBL_APRIL' => 'Tháng 4',
	'LBL_MAY' => 'Tháng 5',
	'LBL_JUNE' => 'Tháng 6',
	'LBL_JULY' => 'Tháng 7',
	'LBL_AUGUST' => 'Tháng 8',
	'LBL_SEPTEMBER' => 'Tháng 9',
	'LBL_OCTOBER' => 'Tháng 10',
	'LBL_NOVEMBER' => 'Tháng 11',
	'LBL_DECEMBER' => 'Tháng 12',
	'LBL_CLICK_HERE_TO_VIEW' => 'Click vào đây để xem',

    // Added by Hieu Nguyen on 2019-10-28
    'LBL_ADD_CALL' => 'Thêm Cuộc gọi',
    'LBL_ADD_MEETING' => 'Thêm Cuộc họp',
	// End Hieu Nguyen
	
	// Added by Phuc on 2019.10.28 to update label for calendar setting
	'LBL_SELECTED_USERS' => 'Người dùng',
	'LBL_CUSTOMIZE_FOR_EVENT' => 'Tùy chỉnh module Hoạt động',
	'LBL_CUSTOMIZE_FOR_TASK' => 'Tùy chỉnh module Công việc',
	// Ended by Phuc

    // Added by Hieu Nguyen on 2019-10-30 to modify the Shared Calendar page
    'LBL_USER_FEED_EDITOR_ADD_NEW_FEED_TITLE' => 'Thêm lịch chia sẻ',
    'LBL_USER_FEED_EDITOR_UPDATE_FEED_TITLE' => 'Cập nhật lịch chia sẻ',
    'LBL_USER_FEED_EDITOR_SELECT_USER_FEED' => 'Chọn lịch chia sẻ',
    'LBL_USER_FEED_EDITOR_SELECTED_USER_FEED' => 'Lịch chia sẻ đã chọn',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-14 to support mass feed actions
    'LBL_FEED_MASS_ACTION_TOGGLE_ALL_TITLE' => 'Ẩn/Hiện tất cả',
    'LBL_FEED_MASS_ACTION_DELETE_ALL_TITLE' => 'Xóa tất cả',
    // End Hieu Nguyen

    // Added missing labels by Hieu Nguyen on 2019-11-19
    'Time Start' => 'Thời gian bắt đầu',
    'End Time' => 'Thời gian kết thúc',
    'No Time' => 'Không có thời gian',
    'LBL_CHECKIN' => 'Checkin',
    'LBL_CHECKIN_DATE' => 'Ngày Checkin',
    'LBL_CHECKIN_TIME' => 'Ngày Checkin',
    'LBL_CHECKIN_ADDRESS' => 'Địa điểm Checkin',
    'LBL_CHECKIN_SALESMAN_IMAGE' => 'Hình ảnh Checkin Salesman',
    'LBL_CHECKIN_CUSTOMER_IMAGE' => 'Hình ảnh Checkin Khách hàng',
    'LBL_CHECKIN_LONGITUDE' => 'Kinh độ Checkin',
    'LBL_CHECKIN_LATITUDE' => 'Vĩ độ Checkin',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-20
    'LBL_EVENT_STATUS' => 'Tình trạng hoạt động',
    'LBL_TASK_STATUS' => 'Tình trạng tác vụ',
    'Send Reminder' => 'Nhắc lịch qua email',
    'Duration (hours)' => 'Thời lượng (giờ)', // Edited by Phu Vo on 2020.07.13
    'Duration (minutes)' => 'Thời lượng (phút)', // Edited by Phu Vo on 2020.07.13
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-21
    'LBL_CALENDAR_SETTINGS_DISPLAY_OPTIONS' => 'Tùy chọn hiển thị',
    'LBL_CALENDAR_SETTINGS_CREATE_OPTIONS' => 'Tùy chọn tạo mới',
	'LBL_CALENDAR_SETTINGS_ADVANCED_OPTIONS' => 'Tùy chọn nâng cao',
    'LBL_CALENDAR_SETTINGS_SHARING_OPTIONS' => 'Tùy chọn chia sẻ',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-27
	'Contact Name' => 'Người liên hệ',
    'LBL_RELATED_LEAD' => 'Lead',
    'LBL_RELATED_ACCOUNT' => 'Công ty',
    'LBL_INVITEES' => 'Lời mời',
	'LBL_CONTACT_INVITEES' => 'Người liên hệ được mời',
    'LBL_USER_INVITEES' => 'Người dùng được mời',
    'Queued' => 'Chờ gửi',
    'Sent' => 'Đã gửi',
    'Accepted' => 'Đã đồng ý',
    'Rejected' => 'Đã từ chối',
    'Failed' => 'Không gửi được',
    'No email' => 'Không có email',
    'Sent failed' => 'Gửi mail thất bại',
	// End Hieu Nguyen
	
	// Added by Phu Vo on 2019.12.09 copy language from module Events
    'LBL_PBX_CALL_ID' => 'Nhật ký cuộc gọi',
    'LBL_EVENTS_CALL_DIRECTION' => 'Hướng cuộc gọi',
	// End Phu Vo

    // Added by Hieu Nguyen on 2019-12-20
    'LBL_MISSED_CALL' => 'Là cuộc gọi nhỡ', // [Core] Update label by Phu Vo on 2020.08.28
    // End Hieu Nguyen

    // Added by Phu Vo on 2019.12.24
    'LBL_EVENTS_CALL_PURPOSE' => 'Mục đích cuộc gọi đi',
    'LBL_EVENTS_CALL_PURPOSE_OTHER' => 'Mục đích cuộc gọi đi khác',
    'LBL_EVENTS_CALL_RESULT' => 'Kết quả cuộc gọi',
    'call_purpose_marketing' => 'Gọi tiếp thị sản phẩm',
    'call_purpose_consulting' => 'Gọi chăm sóc, tư vấn',
    'call_purpose_process_feedback' => 'Gọi xử lý yêu cầu hiện trạng',
    'call_purpose_other' => 'Khác',
    'call_result_call_back_later' => 'Hẹn gọi lại',
    'call_result_customer_interested' => 'Khách hàng quan tâm',
	'call_result_wrong_number' => 'Sai số',
    'call_result_called_back' => 'Đã gọi lại',
	'call_result_not_interested' => 'Không quan tâm',
	'call_result_could_not_be_contacted' => 'Không liên lạc được',
    'LBL_EVENTS_INBOUND_CALL_PURPOSE' => 'Mục đích cuộc gọi đến',
    'LBL_EVENTS_INBOUND_CALL_PURPOSE_OTHER' => 'Mục đích cuộc gọi đến khác',
    'inbound_call_purpose_consult_asking' => 'Yêu cầu tư vấn',
    'inbound_call_purpose_products_services_info_enquiry' => 'Tìm hiểu thông tin SP, DV',
    'inbound_call_purpose_complain_and_feedback' => 'Phàn nàn và góp ý',
    'inbound_call_purpose_support_asking' => 'Ticket',
    'inbound_call_purpose_quotes_contracts_orders_feedback' => 'Vấn đề báo giá, hợp đồng, đơn hàng',
    'inbound_call_purpose_other' => 'Khác',
	// End Phu Vo
	
	// Request #331: Added by Phu Vo to translate calendar label
	'End Date' => 'Ngày kết thúc',
	// End Request #331

	// Added by Phu on 2020.03.20
	'LBL_EMAIL_ASSIGNED_EVENT' => 'Phân công công việc: %s',
	// Ended by Phu

    // [Core] Added by Phu Vo on 2020.07.10
    'LBL_DURATION' => 'Thời lượng',
    'LBL_DURATION_DAYS' => 'ngày',
    'LBL_DURATION_HOURS' => 'giờ',
    'LBL_DURATION_MINS' => 'phút',
    'LBL_DURATION_SECS' => 'giây',
    // End by Phu Vo

    // [CallCenter] Added by Phu Vo on 2020.07.28
    'LBL_IS_AUTO_CALL' => 'Là cuộc gọi tự động',
    // End Phu Vo

    // Added by Phu Vo on 2020.12.25
	'Cancelled' => 'Đã hủy',
	'LBL_ACTIVITY_WORKFLOWS' => 'Workflow %activity_type',
    // End Phu Vo

	// Added by Hieu Nguyen on 2022-02-17
	'Inbound' => 'Cuộc gọi vào',
	'Outbound' => 'Cuộc gọi ra',
	// End Hieu Nguyen
);

$jsLanguageStrings = array(
	'LBL_ADD_EVENT_TASK' => 'Thêm Hoạt động/Công việc',
	'JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR' => 'Đa thêm công việc vào lịch của bạn',
    'LBL_CANT_SELECT_CONTACT_FROM_LEADS' => 'Không thể chọn Liên hệ liên quan tới Lead',
    'JS_FUTURE_EVENT_CANNOT_BE_HELD' => 'Không thể kết thúc hoạt động trong tương lai',
	
	//Calendar view label translation
	'LBL_MONTH' => 'Tháng',
	'LBL_TODAY' => 'Hôm nay',
    'LBL_TOMORROW' => 'Ngày mai',
	'LBL_DAY' => 'Ngày',
	'LBL_WEEK' => 'Tuần',
	
	'LBL_SUNDAY' => 'Chủ nhật',
	'LBL_MONDAY' => 'Thứ 2',
	'LBL_TUESDAY' => 'Thứ 3',
	'LBL_WEDNESDAY' => 'Thứ 4',
	'LBL_THURSDAY' => 'Thứ 5',
	'LBL_FRIDAY' => 'Thứ 6',
	'LBL_SATURDAY' => 'Thứ 7',
	
	'LBL_SUN' => 'CN',
	'LBL_MON' => 'T2',
	'LBL_TUE' => 'T3',
	'LBL_WED' => 'T4',
	'LBL_THU' => 'T5',
	'LBL_FRI' => 'T6',
	'LBL_SAT' => 'T7',
	
	'LBL_JANUARY' => 'Tháng 1',
	'LBL_FEBRUARY' => 'Tháng 2',
	'LBL_MARCH' => 'Tháng 3',
	'LBL_APRIL' => 'Tháng 4',
	'LBL_MAY' => 'Tháng 5',
	'LBL_JUNE' => 'Tháng 6',
	'LBL_JULY' => 'Tháng 7',
	'LBL_AUGUST' => 'Tháng 8',
	'LBL_SEPTEMBER' => 'Tháng 9',
	'LBL_OCTOBER' => 'Tháng 10',
	'LBL_NOVEMBER' => 'Tháng 11',
	'LBL_DECEMBER' => 'Tháng 12',
	
	'LBL_SHORT_JAN' => 'T1',
	'LBL_SHORT_FEB' => 'T2',
	'LBL_SHORT_MAR' => 'T3',
	'LBL_SHORT_APR' => 'T4',
	'LBL_SHORT_MAY' => 'T5',
	'LBL_SHORT_JUN' => 'T6',
	'LBL_SHORT_JUL' => 'T7',
	'LBL_SHORT_AUG' => 'T8',
	'LBL_SHORT_SEP' => 'T9',
	'LBL_SHORT_OCT' => 'T10',
	'LBL_SHORT_NOV' => 'T11',
	'LBL_SHORT_DEC' => 'T12',
	'LBL_ALL_DAY' => 'Cả ngày',
	//End
	
	//Fixing colors for Shared Calendar and My Calendar
	'JS_CALENDAR_VIEW_COLOR_UPDATED_SUCCESSFULLY' => 'Đã cập nhật màu lịch',
	'JS_CALENDAR_VIEW_DELETE_CONFIRMATION' => 'Bạn có muốn xóa chế độ xem lịch này không?',
	'JS_CALENDAR_VIEW_ADDED_SUCCESSFULLY' => 'Đã thêm chế độ xem lịch',
	'JS_CALENDAR_VIEW_DELETED_SUCCESSFULLY' => 'Đã xóa chế độ xem lịch',
	'JS_NO_CALENDAR_VIEWS_TO_ADD' => 'Không có chế độ xem lịch nào',
	'JS_EDIT_CALENDAR' => 'Sửa lịch',
    
    //v7
    'JS_EVENT_UPDATED' => 'Hoạt động đã được cập nhật',
    'JS_NO_EVENTS_F0R_THE_DAY' => 'Không có hoạt động nào trong ngày',
    'LBL_AGENDA' => 'Sắp tới',
    'JS_CALENDAR_VIEW_YOU_ARE_EDITING_NOT_FOUND' => 'Không tìm thấy chế độ xem lịch',
    
    'JS_DELETE' => 'Xóa',
    'JS_EDIT' => 'Sửa',
    'JS_MARK_AS_HELD' => 'Đánh dấu đã kết thúc',
    'JS_CREATE_FOLLOW_UP' => 'Tạo theo dõi',
    'JS_RECURRING_EVENT' => 'Hoạt động định kỳ',
    'JS_DETAILS' => 'Chi tiết',
    'JS_CHECK_START_AND_END_DATE'=>'Thời gian kết thúc phải >= thời gian bắt đầu',
    'JS_CHECK_START_AND_END_DATE_SHOULD_BE_GREATER'=> 'Thời gian kết thúc phải > thời gian bắt đầu',

    // Added by Hieu Nguyen on 2019-10-30 to modify the Shared Calendar page
    'JS_USER_FEED_EDITOR_LOAD_ERROR_MSG' => 'Không tải được màn hình tùy chỉnh lịch chia sẻ. Bạn vui lòng thử lại!',
    'JS_USER_FEED_EDITOR_SELECTED_USER_NOT_IN_SAVED_FEED_ERROR_MSG' => 'Có lỗi xảy ra: người dùng được chọn không nằm trong danh sách lịch đã lưu. Bạn vui lòng reload lại trang này!',
    'JS_USER_FEED_EDITOR_USER_NOT_SELECTED_ERROR_MSG' => 'Bạn phải chọn một người dùng cần thêm lịch trước!',
    'JS_USER_FEED_EDITOR_SAVE_ERROR_MSG' => 'Có lỗi xảy ra khi lưu tùy chọn lịch chia sẻ. Bạn vui lòng thử lại!',
    'JS_USER_FEED_EDITOR_ADD_PRIVATE_CALENDAR_ERROR_MSG' => 'Người dùng này đã thiết lập lịch riêng tư. Bạn không thể xem được lịch riêng tư của người khác!',
    'JS_USER_FEED_EDITOR_ADD_UNSHARED_CALENDAR_ERROR_MSG' => 'Người dùng này hiện tại không chia sẻ lịch cho bạn. Bạn không thể xem lịch của người khác nếu họ không chia sẻ!',
    'JS_USER_FEED_EDITOR_ADD_FEED_SUCCESS_MSG' => 'Đã thêm lịch chia sẻ thành công!',
    'JS_USER_FEED_EDITOR_UPDATE_FEED_SUCCESS_MSG' => 'Đã cập nhật lịch chia sẻ thành công!',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-08 to modify the event popover
    'JS_EVENT_POPOVER_MAIN_OWNER_NAME' => 'Người phụ trách',
    'JS_EVENT_POPOVER_STATUS' => 'Tình trạng',
    'JS_EVENT_POPOVER_TIME' => 'Thời gian',
    'JS_EVENT_POPOVER_LOCATION' => 'Địa điểm',
    'JS_EVENT_POPOVER_SHOW_MAP' => 'Xem bản đồ',
    'JS_EVENT_POPOVER_DESCRIPTION' => 'Mô tả',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-11 to show confirm message before filling customer's address into Event's location
    'JS_FILL_CUSTOMER_ADDRESS_INTO_EVENT_LOCATION_CONFIRM_MSG' => 'Bạn có muốn điền Địa chỉ của khách hàng vừa chọn vào trường Địa điểm của Hoạt động này không?',
    'JS_FILL_CUSTOMER_ADDRESS_INTO_TASK_LOCATION_CONFIRM_MSG' => 'Bạn có muốn điền Địa chỉ của khách hàng vừa chọn vào trường Địa điểm của Công việc này không?',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-14 to support mass feed actions
    'JS_FEED_MASS_ACTION_DELETE_ALL_NO_FEED_REMAIN_WARNING_MSG' => 'Không còn lịch nào để xóa!',
    'JS_FEED_MASS_ACTION_DELETE_ALL_CONFIRM_MSG' => 'Bạn có chắc chắn muốn xóa tất cả lịch bên dưới?',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-21
    'JS_CALENDAR_SETTINGS_SHARED_CALENDAR_ACTIVITY_TYPES_EMPTY_ERROR_MSG' => 'Bạn phải chọn ít nhất 1 loại hoạt động để hiển thị trên màn hình Lịch chia sẻ!',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2019-11-22
    'JS_FILL_SELECTED_CONTACT_AS_INVITEE_CONFIRM_MSG' => 'Bạn có muốn điền Người liên hệ vừa chọn vào danh sách lời mời Người liên hệ của Hoạt động này không?',
    // End Hieu Nguyen

    // Added by Hieu Nguyen on 2020-02-24
    'JS_REMINDER_TIME_TIME_MUST_GREATER_THAN_0_MINUTES_VALIDATE_MSG' => 'Thời gian nhắc nhở phải lớn hơn 0 phút!',
	// End Hieu Nguyen
	
	// Added by Phu Vo on 2020.02.28 for calendar
	'JS_CALENDAR_MOVE_EVENTS_TO_ALLDAY_ERROR_MSG' => 'Bạn không thể thực hiện hành động này!',
	'JS_CALENDAR_MOVE_TASK_TO_EVENTS_ZONE_ERROR_MSG' => 'Bạn không thể thực hiện hành động này!',
	// End Phu Vo
);
