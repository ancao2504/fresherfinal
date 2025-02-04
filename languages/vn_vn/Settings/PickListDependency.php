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
	'LBL_ADD_RECORD' => 'Thêm Dropdown phụ thuộc',
    'PickListDependency' => 'Dropdown phụ thuộc',
	'LBL_PICKLIST_DEPENDENCY' => 'Dropdown phụ thuộc',
	'LBL_SELECT_MODULE' => 'Module',
	'LBL_SOURCE_FIELD' => 'Trường cha',
	'LBL_TARGET_FIELD' => 'Trường con',
	'LBL_SELECT_FIELD' => 'Chọn trường',
	'LBL_CONFIGURE_DEPENDENCY_INFO' => 'Click vào ô tương ứng để thiết lập giá trị cho trường con',
	'LBL_CONFIGURE_DEPENDENCY_HELP_1' => 'Chỉ các giá trị của trường cha đã chọn dưới đây mới hiển thị (trừ lần đầu tiên)',
	'LBL_CONFIGURE_DEPENDENCY_HELP_2' => "Nếu bạn muốn thay đổi giá trị của trường cha thì hãy click vào nút 'Chọn giá trị nguồn' bên tay phải",
	'LBL_CONFIGURE_DEPENDENCY_HELP_3' => 'Giá trị được chọn của trường đích được đánh dấu là ',
	'LBL_SELECT_SOURCE_VALUES' => 'Chọn giá trị nguồn',
	'LBL_SELECT_SOURCE_PICKLIST_VALUES' => 'Chọn giá trị Dropdown nguồn',
	'LBL_ERR_CYCLIC_DEPENDENCY' => 'Thiết lập phụ thuộc này không được phép vì nó sẽ đi vào đệ quy vô hạn',
	'LBL_SELECT_ALL_VALUES' => 'Chọn tất',
	'LBL_UNSELECT_ALL_VALUES' => 'Bỏ chọn tất',
	'LBL_CYCLIC_DEPENDENCY_ERROR' => 'Điều này có thể sẽ đi vào đệ quy vô hạn vì trường %s đã được cấu hình cho trường %s',
	
	// Added by Hieu Nguyen on 2018-07-03
	'Source Field' => 'Trường cha',
	'Target Field' => 'Trường con',
);

$jsLanguageStrings = array(
	'JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE' => 'Bạn có muốn xóa Dropdown phụ thuộc này không?',
	'JS_DEPENDENCY_DELETED_SUEESSFULLY' => 'Đã xóa giá trị phụ thuộc',
	'JS_PICKLIST_DEPENDENCY_SAVED' => 'Đã lưu Dropdown phụ thuộc',
    'JS_DEPENDENCY_ATLEAST_ONE_VALUE' => 'Bạn cần chọn ít nhất một giá trị cho',
	'JS_SOURCE_AND_TARGET_FIELDS_SHOULD_NOT_BE_SAME' => 'Trường nguồn và đích không được giống nhau',
	'JS_SELECT_SOME_VALUE' => 'Chọn một vài giá trị'
);
