<?php

/*
	File: CentralizeChatboxConfig.php
	Author: Vu Mai
	Date: 2022-07-29
	Purpose: Process System Centralized Chatbox Config View
*/

class Settings_Vtiger_CentralizedChatboxConfig_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct($isFullView = true);
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_CENTRALIZED_CHATBOX_CONFIG', $moduleName);
	}

	public function process (Vtiger_Request $request) {
		checkAccessForbiddenFeature('CentralizedChatbox');
		global $centralizedChatboxConfig;
		$moduleName = $request->getModule(false);

		// Render view
		$view = $this->getViewer($request);
		$view->assign('MODULE_NAME', $moduleName);
		$view->assign('CONFIG', $centralizedChatboxConfig);
		$view->display('modules/Settings/Vtiger/tpls/CentralizedChatboxConfig.tpl');
	}
}