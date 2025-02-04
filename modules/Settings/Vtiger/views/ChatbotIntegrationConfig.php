<?php

/*
	File: ChatbotIntegrationConfig.php
	Author: Phu Vo
	Date: 2019.03.22
	Purpose: Process Chatbot Integration Config View
	Refactored by Vu Mai on 2022-07-15
*/

class Settings_Vtiger_ChatbotIntegrationConfig_View extends Settings_Vtiger_BaseConfig_View {

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_CHATBOT_INTEGRATION_CONFIG', $moduleName);
	}

	// Modified by Hieu Nguyen on 2022-07-18 to handle logic rendering vendor list and vendor detail forms
	public function process(Vtiger_Request $request) {
		require_once('integration_providers.php');
		global $chatBotConfig;
		checkAccessForbiddenFeature('ChatbotIntegration');
		$moduleName = $request->getModule(false);
		$mode = $request->getMode();
		$language = Vtiger_Language_Handler::getLanguage();
		$providerInfos = $providers['chatbot'];
		$activeProviderInstance = CPChatBotIntegration_Provider_Model::getActiveProvider();

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('MODE', $mode);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('CHATBOT_CONFIG', $chatBotConfig);
		$viewer->assign('INTRO_KEY', 'intro_' . ($language == 'vn_vn' ? 'vn' : 'en'));

		// For vendor list
		if ($mode == 'ShowList') {
			$providerList = CPChatBotIntegration_Provider_Model::getProviderList();
			$viewer->assign('PROVIDER_LIST', $providerList);
			$viewer->assign('PROVIDER_INFOS', $providerInfos);

			if (!empty($activeProviderInstance)) {
				$viewer->assign('ACTIVE_PROVIDER', $activeProviderInstance->getName());
			}

			$viewer->display('modules/Settings/Vtiger/tpls/ChatbotIntegrationConfig.tpl');
		}

		// For vendor detail
		if ($mode == 'ShowDetail') {
			$provider = $request->get('provider');
			$providerInstance = null;

			$viewer->assign('IS_EDIT', false);

			// Check active provider
			if (!empty($activeProviderInstance)) {
				if ($activeProviderInstance->getName() != $provider) {
					throw new AppException(vtranslate('LBL_VENDOR_INTEGRATION_CONNECT_MULTI_PROVIDER_ERROR_MSG', $moduleName));
				}

				// Edit active provider
				$providerInstance = $activeProviderInstance;
				$viewer->assign('IS_EDIT', true);
			}
			else {
				// Create a temp instance corresponding to the selected provider to show the right config info
				$providerInstance = CPChatBotIntegration_Provider_Model::getProvider($provider);
			}

			$viewer->assign('PROVIDER_INSTANCE', $providerInstance);
			$viewer->assign('PROVIDER_INFO', $providerInfos[$provider]);
			$viewer->display('modules/Settings/Vtiger/tpls/ChatbotIntegrationConfig.tpl');
		}

		// For chatbot modal
		if ($mode == 'GetChatbotModal') {
			$provider = $request->get('provider');
			$chatbotId = $request->get('chatbot_id');
			$providerInstance = $activeProviderInstance;

			// Check active provider
			if (!empty($activeProviderInstance)) {
				if ($activeProviderInstance->getName() != $provider) {
					throw new AppException('Something wrong. Please reload the page!');
				}
			}
			else {
				// Create a temp instance corresponding to the selected provider to show the right config info
				$providerInstance = CPChatBotIntegration_Provider_Model::getProvider($provider);
			}

			// Get provider info
			$instanceInfo = $providerInstance->getInfo();
			
			// Get selected chatbot info by its chatbot id when client did not send the chatbot info
			$selectedChatbotInfo = $request->get('chatbot_info');

			if (empty($selectedChatbotInfo)) {
				$selectedChatbotInfo = $instanceInfo['chatbots'][$chatbotId];
			}

			// Respond
			if ($request->get('edit') == 'true') {
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_CHATBOT_INTEGRATION_MODAL_EDIT_CHATBOT_TITLE', $moduleName));
			}
			else {
				$viewer->assign('MODAL_TITLE', vtranslate('LBL_CHATBOT_INTEGRATION_MODAL_NEW_CHATBOT_TITLE', $moduleName));
			}

			$viewer->assign('CHATBOT_FIELDS', $instanceInfo['chatbot_fields']);
			$viewer->assign('CHATBOT_INFO', $selectedChatbotInfo);
			$viewer->display('modules/Settings/Vtiger/tpls/ChatbotIntegrationConfigChatbotModal.tpl');
		}
	} 
}