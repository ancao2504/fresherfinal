<?php

/*
	File: ConfigAjax.php
	Author: Vu Mai
	Date: 2022-08-16
	Purpose: return view for config ajax request
*/

require_once('include/utils/LangUtils.php');

class Settings_Vtiger_ListPipelineAjax_View extends CustomView_Base_View {

	function __construct() {
		// $this->exposeMethod('getCustomerStatusList');
		// $this->exposeMethod('getCustomerStatusModal');
		// $this->exposeMethod('getCallResultToStatusMappingList');
		// $this->exposeMethod('getCustomerStatusUpdateOptionModal');
	}

	function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess(); 
	}

	function process(Vtiger_Request $request) {
        
        $pipleList =  Settings_Vtiger_Pipeline_Model::getPipelineList();
        $viewer = $this->getViewer($request);
        // $viewer->assign('TEST_PIPELINE', $pipleList);
        $viewer->assign('PIPELINE_LIST', $pipleList);
        $result = $viewer->fetch('modules/Settings/Vtiger/tpls/ItemListPipeline.tpl');
        echo $result;
        // return $pipleList;
	}

	function getCustomerStatusList(Vtiger_Request $request) {
		// $campaignPurpose = $request->get('purpose');
		// $customerStatusList =  CPTelesales_Config_Helper::loadConfigByTableType($campaignPurpose, 'status_list');

		// // Render view
		// $viewer = $this->getViewer($request);
		// $viewer->assign('CAMPAIGN_PURPOSE', $campaignPurpose);
		// $viewer->assign('CUSTOMER_STATUS_LIST', $customerStatusList);
		// $result = $viewer->fetch('modules/CPTelesales/tpl/CustomerStatusList.tpl');

		// echo $result;
	}

	function getCustomerStatusModal(Vtiger_Request $request) {
		// $moduleName = $request->getModule(false);
		// $campaignPurpose = $request->get('purpose');
		
		// // Respond
		// $viewer = $this->getViewer($request);

		// if ($request->get('type') == 'delete') {
		// 	$customerStatusLableKeyList = CPTelesales_Config_Helper::getCustomerStatusLableKeyListForDropwdown($campaignPurpose);

		// 	$viewer->assign('TYPE', 'delete');
		// 	$viewer->assign('SELECTED_STATUS', $request->get('current_value'));
		// 	$viewer->assign('CUSTOMER_STATUS_LABEL_KEY_LIST', $customerStatusLableKeyList);
		// 	$viewer->assign('MODAL_TITLE', vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_MODAL_DELETE_CUSTOMER_STATUS_TITLE', $moduleName));
		// }
		// else {
		// 	if ($request->get('edit') == 'true') {
		// 		$currentValue = $request->get('current_value');
		// 		$curentStatus = CPTelesales_Config_Helper::loadConfigByTableType($campaignPurpose, 'status_list')[$currentValue];
		// 		$modStringsEn = LangUtils::readModStrings('CampaignCustomerStatus', 'en_us');
		// 		$modStringsVn = LangUtils::readModStrings('CampaignCustomerStatus', 'vn_vn');
		// 		$valueToEdit = CPTelesales_Logic_Helper::generateCustomerStatusLabelKey($campaignPurpose, $currentValue);

		// 		$viewer->assign('CURRENT_VALUE', $currentValue);
		// 		$viewer->assign('LABEL_DISPLAY_EN', $modStringsEn['languageStrings'][$valueToEdit]);
		// 		$viewer->assign('LABEL_DISPLAY_VN', $modStringsVn['languageStrings'][$valueToEdit]);
		// 		$viewer->assign('CURRENT_COLOR', $curentStatus['color']);
		// 		$viewer->assign('MODAL_TITLE', vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_MODAL_EDIT_CUSTOMER_STATUS_TITLE', $moduleName));
		// 	}
		// 	else {
		// 		$viewer->assign('MODAL_TITLE', vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_MODAL_ADD_CUSTOMER_STATUS_TITLE', $moduleName));
		// 	}
		// }

		// $viewer->assign('CAMPAIGN_PURPOSE', $campaignPurpose);
		// $viewer->display('modules/CPTelesales/tpl/CustomerStatusModal.tpl');
	}

	function getCallResultToStatusMappingList(Vtiger_Request $request) {
		// $campaignPurpose = $request->get('purpose');

		// $callResultList = CPTelesales_Logic_Helper::getCallResultList();
		// $callResultToStatusMappingList = CPTelesales_Config_Helper::loadConfigByTableType($campaignPurpose, 'call_result_to_status_mapping');

		// $customerStatusLableKeyList = CPTelesales_Config_Helper::getCustomerStatusLableKeyListForDropwdown($campaignPurpose);

		// // Render view
		// $viewer = $this->getViewer($request);
		// $viewer->assign('CALL_RESULT_LIST', $callResultList);
		// $viewer->assign('CALL_RESULT_TO_STATUS_MAPPING_LIST', $callResultToStatusMappingList);
		// $viewer->assign('CUSTOMER_STATUS_LABEL_KEY_LIST', $customerStatusLableKeyList);
		// $result = $viewer->fetch('modules/CPTelesales/tpl/CallResultMappingStatusList.tpl');

		// echo $result;
	}

	function getCustomerStatusUpdateOptionModal(Vtiger_Request $request) {
		// $moduleName = $request->getModule(false);
		// $purpose = $request->get('purpose');

		// $replaceParams = [
		// 	'%purpose' => vtranslate($purpose, 'Campaigns'),
		// ];

		// // Render view
		// $viewer = $this->getViewer($request);
		// $viewer->assign('MODAL_TITLE', vtranslate('LBL_TELESALES_CAMPAIGN_CONFIG_MODAL_UPDATE_CUSTOMER_STATUS_OPTION_TITLE', $moduleName, $replaceParams));
		// $result = $viewer->fetch('modules/CPTelesales/tpl/CustomerStatusUpdateOptionModal.tpl');
		// echo $result;
	}
}