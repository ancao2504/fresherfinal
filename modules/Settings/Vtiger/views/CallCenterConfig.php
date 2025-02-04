<?php

/*
	File: CallCenterConfig.php
	Author: Phu Vo
	Date: 2019.07.30
	Purpose: Process System Call Center Config View
	Refactored by Vu Mai on 2022-07-18
*/

class Settings_Vtiger_CallCenterConfig_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct($isFullView = true);
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_CALLCENTER_SYSTEM_CONFIG', $moduleName);
	}

	// Modified by Vu Mai on 2022-07-19
	public function process(Vtiger_Request $request) {
		require_once('integration_providers.php');
		global $callCenterConfig;
		$moduleName = $request->getModule(false);
		$roleList = Settings_Roles_Record_Model::getAll();
		$mode = $request->getMode();
		$tab = $request->get('tab', 'GeneralConfig');
		$providerInfos = $providers['callcenter'];
		$language = Vtiger_Language_Handler::getLanguage();
		$activeProviderInstance = PBXManager_Server_Model::getActiveConnector();
		$config = PBXManager_Config_Helper::getCallCenterConfig();

		if (!empty($config['missed_call_alert_email_template'])) {
			$emailTemplateRecordModel = EmailTemplates_Record_Model::getInstanceById($config['missed_call_alert_email_template']);
		}

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('CONFIG', $config);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ROLE_LIST', $roleList);
		$viewer->assign('EMAIL_TEMPLATE_RECORD', $emailTemplateRecordModel);
		$viewer->assign('MODE', $mode);
		$viewer->assign('TAB', $tab);
		$viewer->assign('CALLCENTER_CONFIG', $callCenterConfig);
		$viewer->assign('INTRO_KEY', 'intro_' . ($language == 'vn_vn' ? 'vn' : 'en'));

		// For vendor list
		if ($mode == 'ShowList') {
			$providerList = PBXManager_Server_Model::getConnectorList();

			$viewer->assign('PROVIDER_INFOS', $providerInfos);
			$viewer->assign('PROVIDER_LIST', $providerList);
			
			if (!empty($activeProviderInstance)) {
				$viewer->assign('ACTIVE_PROVIDER', $activeProviderInstance->getGatewayName());
			}
		}

		// For vendor detail
		if ($mode == 'ShowDetail') {
			$provider = $request->get('provider');
			$providerInstance = null;

			$viewer->assign('IS_EDIT', false);

			// Check active provider
			if (!empty($activeProviderInstance)) {
				if ($activeProviderInstance->getGatewayName() != $provider) {
					throw new AppException(vtranslate('LBL_VENDOR_INTEGRATION_CONNECT_MULTI_PROVIDER_ERROR_MSG', $moduleName));
				}

				// Edit active provider
				$providerInstance = $activeProviderInstance;
				$viewer->assign('IS_EDIT', true);
			}
			else {
				// Create a temp instance corresponding to the selected provider to show the right config info
				$providerInstance = PBXManager_Server_Model::getConnectorByName($provider);
			}

			$viewer->assign('PROVIDER_INSTANCE', $providerInstance);
			$viewer->assign('PROVIDER_INFO', $providerInfos[$provider]);
		}

		$viewer->display('modules/Settings/Vtiger/tpls/CallCenterConfig.tpl');
	}
}