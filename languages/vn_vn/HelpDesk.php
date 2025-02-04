<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * Modified by: Kelvin Thang 
 * Date: 2018-06-26
 */
$languageStrings = array(
	// Basic Strings
	'HelpDesk' => 'Ticket',
	'SINGLE_HelpDesk' => 'Ticket',
	'LBL_ADD_RECORD' => 'Thêm Ticket',
	'LBL_RECORDS_LIST' => 'DS Ticket',

	// Blocks
	'LBL_TICKET_INFORMATION' => 'Thông tin Ticket',
	'LBL_TICKET_RESOLUTION' => 'Giải pháp xử lý',

	//Field Labels
	'Ticket No' => 'Mã Ticket',
	'Severity' => 'Mức độ nghiêm trọng',
	'Update History' => 'Lịch sử cập nhật',
	'Hours' => 'Số giờ xử lý',
	'Days' => 'Số ngày xử lý',
	'Title' => 'Tiêu đề',
	'Solution' => 'Giải pháp',
	'From Portal' => 'Từ cổng thông tin KH',
	'Related To' => 'Công ty',
	'Contact Name' => 'Người liên hệ',
	//Added for existing picklist entries

	'Big Problem'=>'Vấn đề lớn',
	'Small Problem'=>'Vấn đề nhỏ',
	'Other Problem'=>'Vấn đề khác',

	'Normal'=>'Bình thường',
	'High'=>'Cao',
	'Urgent'=>'Rất cao',

	'Minor'=>'Thấp',
    'Feature'=>'Trung bình',
	'Major'=>'Cao',
	'Critical'=>'Rất cao',

	'Open'=>'Mở',
	'Wait For Response'=>'Chờ phản hồi',
	'Closed'=>'Đã đóng',
	'LBL_STATUS' => 'Tình trạng',
	'LBL_SEVERITY' => 'Mức độ nghiêm trọng',
	//DetailView Actions
	'LBL_CONVERT_FAQ' => 'Chuyển thành FAQ',
	'LBL_RELATED_TO' => 'Liên quan tới',

	//added to support i18n in ticket mails
	'Ticket ID'=>'Mã Ticket',
	'Hi' => 'Chào',
	'Dear'=> 'Kính gửi',
	'LBL_PORTAL_BODY_MAILINFO'=> 'Ticket: ',
	'LBL_DETAIL' => 'chi tiết như sau :',
	'LBL_REGARDS'=> 'Trân trọng',
	'LBL_TEAM'=> 'Bộ phận CSKH',
	'LBL_TICKET_DETAILS' => 'Thông tin chi tiết',
	'LBL_SUBJECT' => 'Tiêu đề : ',
	'created' => 'đã tạo',
	'replied' => 'đã trả lời',
	'reply'=>'Thông tin phản hồi từ',
	'customer_portal' => 'trong cổng thông tin CSKH',
	'link' => 'Sử dụng đường link sau để gửi thông tin phản hồi:',
	'Thanks' => 'Cảm ơn',
	'Support_team' => 'Bộ phận CSKH',
	'The comments are' => 'Ý kiến phản hồi',
	'Ticket Title' => 'Tiêu đề ticket',
	'Re' => 'Lại :',

	//This label for customerportal.
	'LBL_STATUS_CLOSED' =>'Đã đóng',//Do not convert this label. This is used to check the status. If the status 'Closed' is changed in vtigerCRM server side then you have to change in customerportal language file also.
	'LBL_STATUS_UPDATE' => 'Tình trạng của Ticket đã được cập nhật thành',
	'LBL_COULDNOT_CLOSED' => 'Không thể đóng được Ticket',
	'LBL_CUSTOMER_COMMENTS' => 'Sau đây là thông tin khách hàng đã cung cấp:',
	'LBL_RESPOND'=> 'Vui lòng trả lời trong thời gian sớm nhất.',
	'LBL_SUPPORT_ADMIN' => 'Trưởng phòng CSKH',
	'LBL_RESPONDTO_TICKETID' =>'Trả lời cho Ticket ID',
	'LBL_RESPONSE_TO_TICKET_NUMBER' =>'Trả lời cho Mã Ticket',
	'LBL_TICKET_NUMBER' => 'Mã Ticket',
	'LBL_CUSTOMER_PORTAL' => ' từ Cổng thông tin CSKH - Khẩn cấp',
	'LBL_LOGIN_DETAILS' => 'Sau đây là thông tin truy cập cổng thông tin CSKH:',
	'LBL_MAIL_COULDNOT_SENT' =>'Không thể gửi mail',
	'LBL_USERNAME' => 'Tên truy cập :',
	'LBL_PASSWORD' => 'Mật khẩu :',
	'LBL_SUBJECT_PORTAL_LOGIN_DETAILS' => 'Thông tin truy cập cổng thông tin CSKH',
	'LBL_GIVE_MAILID' => 'Vui lòng cung cấp email của bạn',
	'LBL_CHECK_MAILID' => 'Hãy kiểm tra lại địa chỉ email',
	'LBL_LOGIN_REVOKED' => 'Thông tin truy cập của bạn đã bị thu hồi. Vui lòng liên hệ với người quản trị hệ thống',
	'LBL_MAIL_SENT' => 'Thông tin đăng nhập vào cổng thông tin CSKH đã được gửi cho bạn qua email',
	'LBL_ALTBODY' => 'Đây là nội dung email',
	'HelpDesk ID' => 'Mã ticket',
	//Portal shortcuts
	'LBL_ADD_DOCUMENT'=>"Thêm tài liệu",
	'LBL_OPEN_TICKETS'=>"Ticket đang mở",
	'LBL_CREATE_TICKET'=>"Tạo ticket",
    'Reopen' => 'Mở lại',
    'LBL_HELPDESK_RATING' => 'Đánh giá',
	'High Prioriy Tickets' => 'Ticket ưu tiên',
    
    // Added by Phu Vo on 2021.08.10
    'Reopen' => 'Mở lại',
    'LBL_HELPDESK_RATING' => 'Đánh giá',
    'High Prioriy Tickets' => 'Ticket ưu tiên',
    'LBL_RATING_NOTE' => 'Nội dung đánh giá',
	// End Phu Vo

	// Added by Phu Vo on 2021.12.30
	'LBL_LEAD_SOURCE' => 'Nguồn cấp 1',
	'LBL_LEAD_SOURCE_LEVEL_2' => 'Nguồn cấp 2',
    'LBL_HELPDESK_CUSTOMER_TYPE' => 'Loại khách hàng',
	'Existing Customer' => 'Khách hàng cũ',
	'New Customer' => 'Khách hàng mới',
	// End Phu Vo

    // Added by Tin Bui on 2022.03.15
    'LBL_HELPDESK_CONTACT_TYPE' => 'Loại',
    'LBL_CONTACT_EMAIL' => 'Email',
    'LBL_CONTACT_MOBILE' => 'Di động',
    'LBL_TAB_REPLIES' => 'Phản hồi',
    'LBL_SELECT_EMAIL_TEMPLATE' => 'Chọn mẫu email',
    'LBL_SELECT_FAQ_TEMPLATE' => 'Chọn từ Kho kiến thức',
    'LBL_BTN_SEND_EMAIL_AND_UPDATE_STATUS' => 'Gửi phản hồi',
    'LBL_REPLY_TICKET' => 'Phản hồi ticket',
    'LBL_INTERNAL_COMMENTS' => 'Trao đổi nội bộ',
    'LBL_REPLIES_HISTORY' => 'Lịch sử trao đổi',
    'LBL_HELPDESK_RELATED_EMAILS' => 'Email liên quan',
    'LBL_CPTICKETCOMMUNICATIONLOG_LIST' => 'Lịch sử trao đổi ticket',
    'LBL_SLA_INFORMATIONS' => 'Quản lý SLA',
    'LBL_TOTAL_TIME' => 'Tổng thời gian',
    'LBL_TOTAL_PROCESS_TIME' => 'Tổng thời gian xử lý',
    'LBL_SLA_TOTAL_PROCESS_TIME' => 'Tổng thời gian xử lý tiêu chuẩn',
    'LBL_TOTAL_WAITING_FOR_ASSIGNMENT_TIME' => 'Tổng thời gian chờ phân công',
    'LBL_PROCESS_START_DATE' => 'Ngày bắt đầu xử lý',
    'LBL_PROCESS_END_DATE' => 'Ngày xử lý hoàn thành',
    'LBL_OVER_SLA' => 'Vượt SLA',
    'LBL_OVER_SLA_NOTE' => 'Ghi chú',
    'LBL_ASSIGNMENT_DATE' => 'Ngày giao',
    'LBL_HELPDESK_OVER_SLA_REASON' => 'Lý do vượt SLA',
    'CPSLACategory' => 'Danh mục SLA',
    'Assgined' => 'Đã phân công',
    'Wait Close' => 'Đã xong (Chờ đóng)',
    'Cancel' => 'Hủy',
    'LBL_UPDATE_TICKET_STATUS' => 'Cập nhật tình trạng',
    'LBL_CHANGE_STATUS_MODAL_TITLE' => 'Cập nhật tình trạng',
    'LBL_UPDATE_STATUS_HISTORY' => 'Lịch sử cập nhật trạng thái',
    'LBL_CONTACT_TICKETS' => 'Ticket khác',
    'LBL_UPDATED_STATUS_FROM_TO' => '<span style="color: #008ECF">%user_name%</span> đã cập nhật trạng thái từ <span style="color: #008ECF">%old_status%</span> đến <span style="color: #008ECF">%new_status%</span>',
    'LBL_OPENED_TICKET' => '<span style="color: #008ECF">%user_name%</span> đã tạo ticket',
    'LBL_RATING_INFORMATIONS' => 'Nhận xét của khách hàng',
    'LBL_RATING_DESCRIPTION' => 'Mô tả đánh giá',
    'LBL_HELPDESK_SURVEY_STATUS' => 'Tình trạng khảo sát',
    'not_yet_sent_mail' => 'Chưa gửi mail',
    'sent_mail' => 'Đã gửi mail',
    'customer_did_survey' => 'Đã đánh giá',
    'customer_reopen_ticket' => 'Ticket được mở lại',
    'LBL_IS_SEND_SURVEY' => 'Gửi khảo sát',
    'LBL_TICKET_SURVEY_FORM' => 'Form khảo sát',
    'LBL_CONTACT_INFORMATIONS' => 'Thông tin liên hệ',
    'LBL_SLA_INFORMATIONS' => 'Quản lý SLA',
    'LBL_RATING_INFORMATIONS' => 'Nhận xét của khách hàng',
    'LBL_SURVEY_FORM_TICKET_TITLE' => 'Vấn đề',
    'LBL_SURVEY_FORM_TICKET_CREATEDTIME' => 'Thời điểm yêu cầu',
    'LBL_SURVEY_FORM_TICKET_ASIGNEE' => 'Người phụ trách',
    'LBL_SURVEY_FORM_TICKET_TOTAL_PROCESS_TIME' => 'Tổng thời gian xử lý',
    'LBL_SURVEY_FORM_TICKET_SATISFACTION_SCORE' => 'Đánh giá',
    'LBL_SURVEY_FORM_TICKET_RATING_DESCRIPTION' => 'Nội dung đánh giá',
    'LBL_CPTICKETCOMMUNICATIONLOG_LIST' => 'Lịch sử trao đổi ticket',
	'complain' => 'Khiếu nại',
	'complaints_about_the_service' => 'Phàn nàn dịch vụ',
	'user_support' => 'Hỗ trợ sử dụng',
	'sales_consultant' => 'Tư vấn bán hàng',
	'troubleshooting' => 'Giải đáp thắc mắc',
	'feedback' => 'Góp ý',
	'other' => 'Khác',
    'complain' => 'Khiếu nại',
    'complaints_about_the_service' => 'Phàn nàn dịch vụ',
    'user_support' => 'Hỗ trợ sử dụng',
    'sales_consultant' => 'Tư vấn bán hàng',
    'troubleshooting' => 'Giải đáp thắc mắc',
    'feedback' => 'Góp ý',
    'other' => 'Khác',
    'customer_reply_many_times' => 'Khách hàng phản hồi nhiều lần',
    'customer_makes_many_request_at_same_time' => 'Khách hàng gửi nhiều yêu cầu đồng thời',
    'processes_slowly' => 'Xử lý chậm',
    'take_time_for_internal_communication' => 'Mất thời gian trau đổi nội bộ',
    'forgot_to_update_status' => 'Quên cập nhật trạng thái',
    'orther' => 'Khác',
    'LBL_OVERSLA_MODAL_TITLE' => 'Ticket đã vượt SLA, vui lòng nhập lý do',
    'rating_1' => '1 Sao',
    'rating_2' => '2 Sao',
    'rating_3' => '3 Sao',
    'rating_4' => '4 Sao',
    'rating_5' => '5 Sao',
    // Ended by Tin Bui
);

$jsLanguageStrings=array(
	'LBL_ADD_DOCUMENT'=>'Thêm tài liệu',
	'LBL_OPEN_TICKETS'=>'Ticket đang mở',
	'LBL_CREATE_TICKET'=>'Tạo ticket',

    // Added by Tin Bui on 2022.03.15
    'JS_EMAIL_CONTENT_WAS_EMPTY' => 'Chưa nhập nội dung email',
    'JS_CONFIRM_SEND_REPLY' => 'Xác nhận gửi email',
    'JS_CONFIRM_SEND_REPLY_AND_UPDATE_STATUS' => 'Xác nhận gửi email và cập nhật trạng thái ticket từ %oldstatus% thành %newstatus%',
    'JS_SEND_EMAIL_SUCCESS' => 'Gửi email thành công',
    'JS_SEND_EMAIL_FAILED' => 'Gửi email không thành công',
    'JS_SENT_AT' => 'Đã gửi lúc',
    'JS_CALLED_AT' => 'Đã gọi lúc',
    'JS_NO_RECORD' => 'Không có dữ liệu',
    'JS_INVAILD_EMAIL' => 'Email không hợp lệ',
    'JS_UPDATE_TICKET_STATUS_SUCCESS' => 'Cập nhật trạng thái ticket thành công',
    'JS_UPDATE_TICKET_STATUS_FAILED' => 'Cập nhật trạng thái ticket không thành công',
    'JS_LBL_BTN_SEND_EMAIL' => 'Gửi phản hồi',
    'JS_LBL_BTN_SEND_EMAIL_AND_UPDATE_STATUS' => 'Gửi phản hồi và cập nhật trạng thái',
    'JS_LBL_BTN_SELECT_EMAIL_TEMPLATE' => 'Mẫu email',
    'JS_LBL_BTN_SELECT_FAQ' => 'Kho kiến thức',
    'JS_UPDATE_TICKET_STATUS_FAILED_MISSING_DATA' => 'Cập nhật trạng thái ticket không thành công<br>Vui lòng nhập đủ các thông tin cần thiết',
    'JS_MSG_ENTER_VALID_EMAIL' => 'Nhập email hợp lệ rồi nhấn enter',
    // Ended by Tin Bui
);
