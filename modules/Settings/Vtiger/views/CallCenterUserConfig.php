<?php

/*
	File: CallCenterUserConfig.php
	Author: Phu Vo
	Date: 2019.07.30
	Purpose: Process System Call Center Config View
*/

class Settings_Vtiger_CallCenterUserConfig_View extends Settings_Vtiger_BaseConfig_View {

	var $connector = null;

	function __construct() {
		parent::__construct($isFullView = true);
		$this->loadActiveConnector();
	}

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	protected function loadActiveConnector() {
		$serverModel = PBXManager_Server_Model::getInstance();
		$this->connector = $serverModel->getConnector();
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_CALLCENTER_USER_CONFIG', $moduleName);
	}

	public function process(Vtiger_Request $request) {
		global $current_user;

		$moduleName = $request->getModule(false);
		$activeConnectorName = '';

		// Fetch active connector name base on model
		if ($this->connector) {
			$activeConnectorName = $this->connector->getGatewayName();
		}

		// Fetch config from user preferences
		$callCenterUserConfig = Users_Preferences_Model::loadPreferences($current_user->id, 'callcenter_config', true) ?? [];

		// Get user record model
		$currentUserRecordModel = Users_Record_Model::getCurrentUserModel();

		// Fetch and process uploaded file
		$uploadedFileBase64 = '';
		
		if (!empty($callCenterUserConfig['custom_ringtone'])) {
			$customRingtoneFileName = "upload/webphone_ringtone/ringtone_{$current_user->id}";
			$uploadedFileBase64 = 'data:audio/mpeg;base64,' . base64_encode(file_get_contents($customRingtoneFileName));
		}

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('ACTIVE_CONNECTOR_NAME', $activeConnectorName);
		$viewer->assign('VENDOR_CONFIG', $callCenterUserConfig);
		$viewer->assign('USER_RECORD_MODEL', $currentUserRecordModel);
		$viewer->assign('UPLOADED_FILE_BASE_64', $uploadedFileBase64);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->display('modules/Settings/Vtiger/tpls/CallCenterUserConfig.tpl');
	}
}