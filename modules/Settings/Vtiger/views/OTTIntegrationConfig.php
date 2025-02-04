<?php

/*
	View OTTIntegrationConfig
	Author: Hieu Nguyen
	Date: 2022-06-14
	Purpose: show UI for user to update parameters to integrate with OTT services
*/

class Settings_Vtiger_OTTIntegrationConfig_View extends Settings_Vtiger_BaseConfig_View {

	function getPageTitle(Vtiger_Request $request) {
		$qualifiedName = $request->getModule(false);
		return vtranslate('LBL_OTT_INTEGRATION_CONFIG', $qualifiedName);
	}

	function process(Vtiger_Request $request) {
		require_once('integration_providers.php');
		checkAccessForbiddenFeature('OTTIntegration');
		$mode = $request->getMode();
		$viewer = $this->getViewer($request);
		$qualifiedName = $request->getModule(false);
		$channel = $request->get('channel');

		// Check access supported channel
		$supportedChannels = CPOTTIntegration_Config_Helper::getChannels();

		if (!empty($channel)) {
			if (empty($supportedChannels[$channel])) {
				throw new AppException(vtranslate('LBL_NOT_ACCESSIBLE'));
			}
		}
		else {
			$channel = array_keys($supportedChannels)[0];
		}

		$providerInfos = $providers['ott'][$channel];
		$activeGatewayInstance = CPOTTIntegration_Gateway_Model::getActiveGateway($channel);
		$language = Vtiger_Language_Handler::getLanguage();

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('MODE', $mode);
		$viewer->assign('MODULE_NAME', $qualifiedName);
		$viewer->assign('LANGUAGE', $language);
		$viewer->assign('ACTIVE_CHANNEL', $channel);
		$viewer->assign('INTRO_KEY', 'intro_' . ($language == 'vn_vn' ? 'vn' : 'en'));

		// For vendor list
		if ($mode == 'ShowList') {
			$gatewayList = CPOTTIntegration_Gateway_Model::getGatewayList($channel);
			$viewer->assign('GATEWAY_LIST', $gatewayList);
			$viewer->assign('PROVIDER_INFOS', $providerInfos);

			if (!empty($activeGatewayInstance)) {
				$viewer->assign('ACTIVE_GATEWAY', $activeGatewayInstance->getName());
			}
		}

		// For vendor detail
		if ($mode == 'ShowDetail') {
			$gateway = $request->get('gateway');
			$gatewayInstance = null;

			$viewer->assign('IS_EDIT', false);

			// Check active gateway
			if (!empty($activeGatewayInstance)) {
				if ($activeGatewayInstance->getName() != $gateway) {
					throw new AppException(vtranslate('LBL_VENDOR_INTEGRATION_CONNECT_MULTI_GATEWAY_ERROR_MSG', $qualifiedName));
				}

				// Edit active gateway
				$gatewayInstance = $activeGatewayInstance;
				$viewer->assign('IS_EDIT', true);
			}

			// Connect new gateway
			if (empty($gatewayInstance)) {
				$gatewayInstance = CPOTTIntegration_Gateway_Model::getGateway($gateway, $channel);
			}

			$viewer->assign('GATEWAY_INSTANCE', $gatewayInstance);
			$viewer->assign('PROVIDER_INFO', $providerInfos[$gateway]);
		}
		
		$viewer->display('modules/Settings/Vtiger/tpls/OTTIntegrationConfig.tpl');
	}
}