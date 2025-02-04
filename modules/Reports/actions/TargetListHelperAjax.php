<?php

/*
	Action TargetListHelperAjax
	Author: Hieu Nguyen
	Date: 2021-07-16
	Purpose: to handle request from Add To Target List and Remove From Target List buttons
*/

class Reports_TargetListHelperAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('loadTargetLists');
		$this->exposeMethod('addToTargetList');
		$this->exposeMethod('removeFromTargetList');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = 'CPTargetList';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function loadTargetLists(Vtiger_Request $request) {
		$targetLists = Reports_TargetList_Helper::getTargetLists();
		$response = new Vtiger_Response();
		$response->setResult(['target_lists' => $targetLists]);
		$response->emit();
	}

	public function addToTargetList(Vtiger_Request $request) {
		$reportId = $request->get('report_id');
		$targetListId = $request->get('target_list_id');

		$reportRecordModel = Reports_Record_Model::getInstanceById($reportId);
        $reportRecordModel->setModule('Reports');
		$reportRecordModel->set('advancedFilter', $request->get('advanced_filter'));

		$result = Reports_TargetList_Helper::addToTargetList($reportRecordModel, $targetListId);

		if ($result == 'NO_RECORD') {
			$result = ['success' => false, 'error_code' => $result];
		}
		else {
			$result = ['success' => true, 'result' => $result];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function removeFromTargetList(Vtiger_Request $request) {
		$reportId = $request->get('report_id');
		$targetListId = $request->get('target_list_id');

		$reportRecordModel = Reports_Record_Model::getInstanceById($reportId);
        $reportRecordModel->setModule('Reports');
		$reportRecordModel->set('advancedFilter', $request->get('advanced_filter'));

		$result = Reports_TargetList_Helper::removeFromTargetList($reportRecordModel, $targetListId);

		if ($result == 'NO_RECORD') {
			$result = ['success' => false, 'error_code' => $result];
		}
		else {
			$result = ['success' => true, 'result' => $result];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}