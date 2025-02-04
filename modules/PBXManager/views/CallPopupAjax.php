<?php

class PBXManager_CallPopupAjax_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct();

        $this->exposeMethod('relatedListView');
        $this->exposeMethod('searchFaq');
        $this->exposeMethod('faqPopup');
        $this->exposeMethod('fullFaqSearchPopup');
    }

    function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if(!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    function relatedListView(Vtiger_Request $request) {
        $moduleName = PBXManager_CallPopup_Model::getmoduleNameFromRequest($request);

        $dataRows = PBXManager_CallPopup_Model::getRelatedListViewData($request);
        $headers = PBXManager_CallPopup_Model::getHeadersFromDataRows($dataRows);
        $fieldModels = PBXManager_CallPopup_Model::getFieldModelsFromDataRows($moduleName, $dataRows);
        $count = PBXManager_CallPopup_Model::getRelatedListViewCount($request);

        // Process for Calendar module
        if ($moduleName === 'Events') $moduleName = 'Calendar';

        // Prepare view parameters
        // Modified by Vu Mai on 2022-09-08 to update tab comment
        $viewer = $this->getViewer($request);

		if ($request->get('tab') == 'comment-list') {
			$customerId = $request->get('customer_id');
			$customerType = $request->get('customer_type');

			$viewer->assign('RECORD', $customerId);
			$viewer->assign('MODULE', $customerType);
			$viewer->assign('TOTAL_COUNT', $count);
			$viewer->display('modules/Vtiger/tpls/CustomComment.tpl');
		}
		else {
			$viewer->assign('DATAROWS', $dataRows);
			$viewer->assign('HEADERS', $headers);
			$viewer->assign('FIELD_MODELS', $fieldModels);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('TOTAL_COUNT', $count);
	
			$viewer->display('modules/PBXManager/tpls/CallPopupRelatedListView.tpl');
		}
		// End Vu Mai
    }

    function searchFaq(Vtiger_Request $request) {
        $keyword = $request->get('keyword');

        $dataRows = PBXManager_CallPopup_Model::getFaqsByKeyword($keyword);
        $dataCount = PBXManager_CallPopup_Model::getFaqsCountByKeyword($keyword);

        $viewer = $this->getViewer($request);
        $viewer->assign('DATA_ROWS', $dataRows);
        $viewer->assign('KEYWORD', $keyword);
        $viewer->assign('COUNT', $dataCount);
        $viewer->display('modules/PBXManager/tpls/CallPopupSearchFaq.tpl');
    }

    function faqPopup(Vtiger_Request $request) {
        $data = $request->getAll();
        $recordId = $data['record'];

        if (!$recordId) return;

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Faq');

        // Fetch customer email in needs
        if (empty($data['customer_email'])) $data['customer_email'] = PBXManager_CallPopup_Model::getCustomerEmail($data['customer_id'], $data['customer_type']);

        $viewer = $this->getViewer($request);
        $viewer->assign('RECORD', $recordModel);
        $viewer->assign('DATA', $data);
        $viewer->display('modules/PBXManager/tpls/CallPopupFaqPopup.tpl');
    }

    function fullFaqSearchPopup(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->display('modules/PBXManager/tpls/CallPopupSearchFaqFull.tpl');
    }
}
