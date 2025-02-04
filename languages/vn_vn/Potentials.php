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
    'Potentials' => 'Cơ hội',
    'SINGLE_Potentials' => 'Cơ hội',
    'LBL_ADD_RECORD' => 'Tạo mới Cơ hội',
    'LBL_RECORDS_LIST' => 'DS Cơ hội',

    // Blocks
    'LBL_OPPORTUNITY_INFORMATION' => 'Thông tin Cơ hội',

    //Field Labels
    'Potential No' => 'Mã Cơ hội',
    'Amount' => 'Giá trị cơ hội',
    'Next Step' => 'Bước tiếp theo',
    'Sales Stage' => 'Bước bán hàng',
    'Probability' => 'Tỉ lệ thành công',
    'Campaign Source' => 'Chiến dịch',
    'Forecast Amount' => 'Doanh số dự kiến',
    'Related To' => 'Công ty',
    'Contact Name' => 'Người liên hệ',
    'Type' => 'Loại',
    'Expected Close Date' => 'Ngày chốt dự kiến',

    //Dashboard widgets
    'Funnel' => 'Phân tích phễu bán hàng',
    'Potentials by Stage' => 'Cơ hội theo bước bán hàng',
    'Total Revenue' => 'Doanh số theo nhân viên',
    'Top Potentials' => 'Top Cơ hội',
    'Forecast' => 'Doanh thu dự kiến',

    //Added for Existing Picklist Strings

    'Prospecting' => 'Thăm dò', // Modified by Phu Vo on 2020.12.14
    'Qualification' => 'Đánh giá năng lực',
    'Needs Analysis' => 'Phân tích nhu cầu',
    'Value Proposition' => 'Tham khảo giá',
    'Id. Decision Makers' => 'Nhận diện người ra quyết định',
    'Perception Analysis' => 'Phân tích nhận thức',
    'Proposal or Price Quote' => 'Đề xuất/Báo giá',
    'Negotiation or Review' => 'Thương lượng đàm phán', // Modified by Phu Vo on 2020.12.14
    'Closed Won' => 'Thành công',
    'Closed Lost' => 'Thất bại',

    '--None--' => '--Chưa chọn--',
    'Existing Business' => 'Khách hàng cũ',
    'New Business' => 'Khách hàng mới',
    'LBL_EXPECTED_CLOSE_DATE_ON' => 'Ngày chốt dự kiến',

    //widgets headers
    'LBL_RELATED_CONTACTS' => 'Liên hệ liên quan',
    'LBL_RELATED_PRODUCTS' => 'Sản phẩm liên quan',

    //Convert Potentials
    'LBL_CONVERT_POTENTIAL' => 'Chuyển đổi cơ hội',
    'LBL_CREATE_PROJECT' => 'Tạo Dự án',
    'LBL_POTENTIALS_FIELD_MAPPING' => 'Thiết lập dữ liệu',
    'LBL_CONVERT_POTENTIALS_ERROR' => 'Bạn phải bật module Dự án để chuyển đổi Cơ hội',
    'LBL_POTENTIALS_FIELD_MAPPING_INCOMPLETE' => 'Chưa thiết lập dữ liệu cho việc chuyển đổi cơ hội. Hãy vào chức năng (Cài đặt > Quản lý Module > Cơ hội > Thiết lập dữ liệu)',

    //Potentials Custom Field Mapping
    'LBL_CUSTOM_FIELD_MAPPING' => 'Thiết lập chuyển đổi Cơ hội -> Dự án',

	// Begin for Potential result and probability
	'0' => '0%',
    '10' => '10%',
    '20' => '20%',
    '30' => '30%',
    '40' => '40%',
    '50' => '50%',
    '70' => '70%',
    '60' => '60%',
    '80' => '80%',
    '90' => '90%',
    '100' => '100%',
	'LBL_POTENTIAL_RESULT' => 'Kết quả',
    'LBL_POTENTIAL_LOST_REASON' => 'Lý do thất bại',
    'price_higher_than_budget' => 'Giá cao hơn so với ngân sách khách hàng',
    'poor_features' => 'Tính năng nghèo nàn',
    'unfriendly_ui_ux' => 'Giao diện không thân thiện',
    'customer_has_no_plan' => 'Khách hàng chưa có kế hoạch',
    'customer_selected_another_partner' => 'Khách hàng lựa chọn nhà cung cấp khác',
    'requirement_not_suite_with_crm' => 'Không phù hợp yêu cầu',
    // End fo Potential result and probability
    
    // [Core] Added by Phu Vo on 2020.12.14
    'Proposal' => 'Đề xuất giải pháp',
    'Quotation' => 'Báo giá',
    'Pending' => 'Tạm hoãn',
    // End Phu Vo

    // Added by Hieu Nguyen on 2021-08-04
    'LBL_PROGRESS_BAR_VISITED_NODE_TOOLTIP' => 'Bước bán hàng: %node_value<br/>Bước trước đó: %prev_value<br/>Người cập nhật: %updated_by<br/>Thời gian cập nhật: %updated_time',
    'LBL_PROGRESS_BAR_WON_RESULT_TOOLTIP' => 'Chốt thành công tại bước %sales_stage',
    'LBL_PROGRESS_BAR_LOST_RESULT_TOOLTIP' => 'Chốt thất bại tại bước %sales_stage với lý do: %lost_reason',
    // End Hieu Nguyen
    
    // Added by Phu Vo on 2021.08.10
    'LBL_LOST_REASON_DESCRIPTION' => 'Mô tả lý do thất bại',
    'LBL_ACTUAL_CLOSING_DATE' => 'Ngày chốt thực tế',
    // End Phu Vo
);

$jsLanguageStrings = array(
    'JS_SELECT_PROJECT_TO_CONVERT_LEAD' => 'Hãy chọn Dự án để chuyển đổi Cơ hội',
    
    // Added by Phu Vo on 2021.09.08 to add sales stage confirmation message
    'JS_SALES_STAGE_REVERT_CONFIRMATION_MSG' => 'Cơ hội đã được đánh dấu là <strong>%sales_stage</strong>, nếu cập nhật bước bán hàng sẽ hủy bỏ kết quả trước đó. Bạn có muốn tiếp tục?',
    // End Phu Vo
);
