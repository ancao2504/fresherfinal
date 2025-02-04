<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 *	Modified by: Hieu Nguyen
 *	Date: 2018-07-03
 */
 
$languageStrings = array(
	'SINGLE_Settings:Webforms' => 'Form thu thập dữ liệu',
	//Basic Field Names
	'WebForm Name' => 'Tên Form',
	'Public Id' => 'Mã Form',
	'Enabled' => 'Tình trạng',
	'Module' => 'Module',
	'Return Url' => 'Url trả về',
	'Post Url' => 'Url nhận dữ liệu',
    'Captcha Enabled' => 'Kích hoạt chế độ chống spam',
	'SINGLE_Webforms' => 'Form thu thập dữ liệu',

	//Actions
	'LBL_SHOW_FORM' => 'Xem Form',
	'LBL_DUPLICATES_EXIST' => 'Tên Form đã tồn tại',

	//Blocks
	'LBL_WEBFORM_INFORMATION' => 'Thông tin form thu thập',
	'LBL_FIELD_INFORMATION' => 'Trường dữ liệu',
	'LBL_FIELD_NAME' => 'Tên trường',
	'LBL_OVERRIDE_VALUE' => 'Giá trị mặc định',
	'LBL_MANDATORY' => 'Bắt buộc',
	'LBL_WEBFORM_REFERENCE_FIELD' => 'Tên trường tham chiếu',
	'LBL_SELECT_FIELDS_OF_TARGET_MODULE' => 'Chọn trường dữ liệu cho Module ...',
	'LBL_ALLOWS_YOU_TO_MANAGE_WEBFORMS' => 'Cho phép bạn quản lý các Form',
	'LBL_ADD_FIELDS' => 'Thêm field',
	'LBL_EMBED_THE_FOLLOWING_FORM_IN_YOUR_WEBSITE' => 'Tích hợp Form sau vào Website của bạn',
	'LBL_SELECT_VALUE' => 'Chọn giá trị',
	'LBL_LABEL' => 'tiêu đề',
	'LBL_SAVE_FIELDS_ORDER' => 'Lưu thứ tự các trường',
	'LBL_HIDDEN' => 'Ẩn',
	'LBL_ENABLE_TARGET_MODULES_FOR_WEBFORM' => 'Kích hoạt module đích cho Form',
	'LBL_ASSIGN_USERS' => 'Phân công dữ liệu',
    'LBL_ASSIGN_ROUND_ROBIN' => 'Phân công cho người dùng theo cơ chế xoay vòng',
    'LBL_ROUNDROBIN_USERS_LIST' => 'Danh sách Người dùng theo cơ chế xoay vòng',
	'LBL_ADD_RECORD' => 'Thêm Form',
	
	// Added by Hieu Nguyen on 2018-07-03
	'Webform Name' => 'Tên Form',
	'LBL_UPLOAD_DOCUMENTS' => 'Upload Tài liệu',
	'LBL_ADD_FILE_FIELD' => 'Thêm trường Upload',
	'LBL_FIELD_LABEL' => 'Tên trường',
	'LBL_FILE_FIELD_INFO' => 'Với mỗi File được upload lên từ Webform, một Tài liệu mới sẽ được tạo ra để chứa File đính kèm. Tài liệu này sẽ được liên kế với thông tin %s tương ứng.',
	'LBL_NO_FILE_FIELD' => 'Không có trường Upload nào.',
	'LBL_COPY_TO_CLIPBOARD' => 'Sao chép vào Clipboard',
);
$jsLanguageStrings = array(
	'JS_WEBFORM_DELETED_SUCCESSFULLY' => 'Đã xóa form',
	'JS_LOADING_TARGET_MODULE_FIELDS' => 'Đang tải trường dữ liệu của Module đích',
	'JS_SELECT_AN_OPTION' => 'Chọn một giá trị',
	'JS_LABEL' => 'tiêu đề',
	'JS_MANDATORY_FIELDS_WITHOUT_OVERRIDE_VALUE_CANT_BE_HIDDEN' => 'Không thể ẩn các trường dữ liệu bắt buộc mà không có giá trị ghi đè',
	'JS_REFERENCE_FIELDS_CANT_BE_MANDATORY_WITHOUT_OVERRIDE_VALUE' => 'Các trường tham chiếu không thể là trường bắt buộc nếu không có giá trị ghi đè',
	'JS_TYPE_TO_SEARCH' => 'Nhập để tìm kiếm',
	"JS_WEBFORM_WITH_THIS_NAME_ALREADY_EXISTS" => 'Tên form đã tồn tại',

	// Added by Hieu Nguyen on 2018-07-03
	'JS_MAX_FILE_FIELDS_LIMIT' => 'Bạn chỉ có thể thêm tối đa %s trường Upload.',
	'JS_COPIED_SUCCESSFULLY' => 'Đã sao chép thành công.',
	'JS_COPY_FAILED' => 'Sao chép không thành công. Hãy sao chép thủ công.',
);