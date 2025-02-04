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
	'ModuleManager' => 'Quản lý module',
	'LBL_IMPORT_MODULE' => 'Thêm module',
	'LBL_VTIGER_EXTENSION_STORE' => 'Kho ứng dụng',
	'LBL_PUBLISHER' => 'Nhà cung cấp',
	'LBL_LICENSE' => 'Giấy phép',
	'LBL_PUBLISHED_ON' => 'Được đăng trên',
	'LBL_INSTALL' => 'Cài đặt',
	'LBL_UPGRADE' => 'Nâng cấp',
	'LBL_VERSION' => 'Phiên bản',
	'LBL_DECLINE' => 'Từ chối',
	'LBL_ACCEPT_AND_INSTALL' => 'Chấp nhận và Cài đặt',
	'LBL_ALREADY_EXISTS' => 'Đã tồn tại',
	'LBL_OK' => 'OK',
	'LBL_EXTENSION_NOT_COMPATABLE' => 'Ứng dụng không tương thích với hệ thống',
	'LBL_INVALID_FILE' => 'File không hợp lệ',
	'LBL_NO_LICENSE_PROVIDED' => 'Chưa được cấp phép',
	'LBL_INSTALLATION' => 'Cài đặt',
	'LBL_FAILED' => 'Thất bại',
	'LBL_SUCCESSFULL' => 'Thành công',
	'LBL_INSTALLATION_LOG' => 'Kết quả cài đặt',
	//Install From file labels
	'LBL_VERIFY_IMPORT_DETAILS' => 'Thông tin kiểm tra',
	'LBL_MODULE_NAME' => 'Tên module',
	'LBL_REQ_VTIGER_VERSION' => 'Phiên bản hệ thống',
	'LBL_LICENSE_ACCEPT_AGREEMENT' => 'Tôi chấp nhận bản quyền.',
	'LBL_EXISTS'=>'Đã tồn tại',
	'LBL_PROCEED_WITH_IMPORT' => 'Bạn có muốn cài đặt không?',
	'LBL_VERSION_NOT_SUPPORTED' => 'Ứng dụng không tương thích với hệ thống',
	'LBL_FINISH' => 'Kết thúc',
	'LBL_IMPORT_ZIP' => 'Cài dặt từ file Zip',
	'LBL_NOT_FOUND' => "Xin lỗi, Không có ứng dụng nào tương thích với hệ thống",
	'LBL_INVALID_IMPORT_TRY_AGAIN' => ' đã tồn tại, hãy thử lại sau.',
	'LBL_IMPORTED_MODULE' => ' đã cài đặt xong.',
	'LBL__IMPORTING_MODULE' => 'Đang cài đặt module',
	'LBL_EXTENSION_STORE' => 'Kho ứng dụng',
	'LBL_ACCEPT_WITH_THE_DISCLAIMER' => 'Tôi hiểu và chấp nhận rủi ro này',
	'LBL_INSTALL_FROM_ZIP' => 'Cài từ file Zip',
	'LBL_DELETE_EXIST_DIRECTORY' => 'Hãy xóa thư mục module đã tồn tại để tiếp tục cài đặt',

	'LBL_UPDATE_MODULE_FROM_FILE' => 'Cập nhật module từ file',
	'LBL_UPDATED_MODULE' => ' module đã được cập nhật.',
	'LBL__UPDATING_MODULE' => 'Cập nhật module',
	'LBL_PHP_EXTENSION_LOADER_IS_NOT_AVAIABLE' => 'Hãy cài đặt thư viện extension loader cho PHP',
	'LBL_IMPORT_MODULE_FROM_ZIP' => 'Cài đặt từ file Zip',
	'LBL_UPDATE_NOW' => 'Cập nhật',
	'LBL_IMPORT_NOW' => 'Cài đặt',
	'LBL_DISCLAIMER_FOR_IMPORT_FROM_ZIP' => '<b>Chú ý - File cài đặt có thể không an toàn</b> <br>Có thể các file cài đặt này không được cung cấp bởi vTiger CRM và chưa được vTiger CRM kiểm duyệt nên có thể gây mất an toàn cho hệ thống. Nếu bạn thực sự tin tưởng đó là những file an toàn thì hãy cài. Mọi thông tin chi tiết xin vui lòng liên hệ với vTiger CRM qua website <a href="http://vtextension.com" target="_blank"> http://vtextension.com</a>',

	// Added by Hieu Nguyen on 2018-08-15
	'LBL_MODULE_BUILDER_BTN_CREATE_NEW_MODULE' => 'Tạo Module mới',
	'LBL_MODULE_BUILDER_MODAL_TITLE' => 'Tạo Module mới',
	'LBL_MODULE_BUILDER_MODULE_NAME' => 'Tên Module',
	'LBL_MODULE_BUILDER_MODULE_DISPLAY_NAME_EN' => 'Tên hiển thị (Tiếng Anh)',
	'LBL_MODULE_BUILDER_MODULE_DISPLAY_NAME_VN' => 'Tên hiển thị (Tiếng Việt)',
	'LBL_MODULE_BUILDER_MODULE_MENU_GROUP' => 'Nhóm Menu',
	'LBL_MODULE_BUILDER_MODULE_IS_EXTENSION' => 'Là module mở rộng (không sinh bảng)', //-- Added by Kelin Thang on 2019-11-25 -- Module is Extension
	'LBL_MODULE_BUILDER_MODULE_HAS_RELATED_ACTIVITIES_LIST' => 'Có subpanel Hoạt động liên quan', //-- Added by Kelin Thang on 2019-11-25 -- Enable Activity for module
	'LBL_MODULE_BUILDER_MODULE_IS_PERSON' => 'Là đối tượng chỉ người (giống Lead/Contact)' // Added by Phu Vo on 2020.08.11
	// End Hieu Nguyen
);
$jsLanguageStrings = array(
    'JS_ON' => 'mở',
    'JS_PLEASE_INSTALL_EXTENSION_LOADER_TO_INSTALL_THIS_EXTENSION_FROM_BELOW_LINK' => 'Hãy cài đặt thư viện cài dặt module từ đường dẫn sau',
    'JS_EXTENSION_STORE_IS_NOT_SUPPOTED_WOULD_YOU_LIKE_TO_INSTALL' => 'Ứng dụng không được hỗ trợ, bạn có muốn cài đặt không?',
    'JS_PHP_EXTENSION_LOADER_NOT_INSTALLED' => 'Thư viện Php loader chưa được cài đặt',
    'JS_CLICK_HERE' => 'Click vào đây ',
    'JS_FOR_INSTALLATION' => 'để cài đặt',
    'JS_IMPORT_SUCCESSFULL' => 'Đã cài đặt',
    'JS_IMPORTED_MODULE' => ' đã cài đặt module.',
    'JS_UPDATE_SUCCESSFULL' => 'Đã cập nhật',
	'JS_UPDATED_MODULE' => 'đã cập nhật module',
	
	// Added by Hieu Nguyen on 2018-08-15
	'JS_MODULE_BUILDER_MODULE_NAME_EXISTS_ERROR_MSG' => 'Tên Module đã tồn tại!',
	'JS_MODULE_BUILDER_MODULE_NAME_EXISTS_IN_REGISTER_FILE_ERROR_MSG' => 'Module này đã tồn tại trong file CustomModules.php. Bạn hãy thử chạy lệnh Sửa chữa nhanh để phát sinh Module này!',
	'JS_MODULE_BUILDER_CREATE_MODULE_SUCCESS_MSG' => 'Module đã được tạo mới thành công!',
	'JS_MODULE_BUILDER_CREATE_MODULE_ERROR_MSG' => 'Không thể tạo Module. Bạn vui lòng thử lại!',
	// End Hieu Nguyen
);