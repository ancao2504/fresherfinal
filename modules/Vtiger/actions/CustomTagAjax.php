<?php
/*
	Action CustomTagAjax
	Author: Vu Mai
	Date: 2022-09-07
	Purpose: handle logic for CustomTag
*/

class Vtiger_CustomTagAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('getAssignableTags');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if (!$permission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function getAssignableTags(Vtiger_Request $request) {
		global $current_user, $adb;

		$requestData = $request->getAll();
		$requestData['keyword'] = $adb->sql_escape_string($requestData['keyword']);
		$result = CPSocialIntegration_SocialChatboxPopup_Model::getAssignableTags($current_user->id, $requestData['keyword']);

		// Response
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}