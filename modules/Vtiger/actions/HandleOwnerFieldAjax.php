<?php

/* 
	Action HandleOwnerFieldAjax
	Author: Hieu Nguyen
	Date: 2019-05-22
	Purpose: to handle request from the client for custom owner field
*/

class Vtiger_HandleOwnerFieldAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('loadOwnerList');
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function loadOwnerList(Vtiger_Request $request) {
		$keyword = trim($request->get('keyword'));
		$userOnly = $request->get('user_only');
		$assignableUsersOnly = $request->get('assignable_users_only');
		$skipCurrentUser = $request->get('skip_current_user');
		$skipUsers = $request->get('skip_users');
		$skipGroups = $request->get('skip_groups');
		$ownerList = Vtiger_CustomOwnerField_Helper::getOwnerList($keyword, $userOnly == 'true', $assignableUsersOnly == 'true', $skipCurrentUser == 'true', $skipUsers, $skipGroups);

		$response = new Vtiger_Response();
		$response->setResult($ownerList);
		$response->emit();
	}
}