<?php

/*
	File: GenerateWebhookUrl.php
	Author: Hieu Nguyen
	Date: 2021-03-03
	Purpose: Generate Webhook URL for 3rd party integration
*/

class Settings_Vtiger_GenerateWebhookUrl_View extends Settings_Vtiger_BaseConfig_View {

	var $integrationMapping = [
		'call_center' => [],
		'sms' => [],
		'ott' => [],
		'social' => [
			'Facebook' => 'Facebook',
			'Zalo' => 'Zalo OA'
		],
		'chatbot' => [
			'Hana' => 'Hana.ai',
			'BBH' => 'BotBanHang.vn',
			'Tawk' => 'Tawk.to'
		],
		'ai_camera' => [],
		'mkt_automation' => [
			'Mautic' => 'Mautic',
		], 
	];

	var $webhookMapping = [
		'call_center' => [
			'call_event_url' => 'Call Event URL',
			'inbound_routing_url' => 'Inbound Routing URL',
		],
		'sms' => [
			'callback_url' => 'Callback URL'
		],
		'ott' => [
			'callback_url' => 'Callback URL'
		],
		'social' => [
			'oauth_callback_url' => 'Oauth Callback URL',
			'webhook_url' => 'Webhook URL'
		],
		'chatbot' => [
			'iframe_url' => 'Iframe URL',
			'webhook_url' => 'Webhook URL'
		],
		'ai_camera' => [
			'webhook_url' => 'Webhook URL'
		],
		'mkt_automation' => [
			'oauth_callback_url' => 'Oauth Callback URL',
			'webhook_url' => 'Webhook URL'
		],
	];

	function __construct() {
		parent::__construct($isFullView = true);
		$this->loadCallCenterProviders();
		$this->loadSMSProviders();
		$this->loadOTTProviders();
		$this->loadAICameraProviders();
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_GENERATE_WEBHOOK_URL', $moduleName);
	}

	private function loadCallCenterProviders() {
		$pattern = 'modules/PBXManager/connectors/*.php';
			
		foreach (glob($pattern) as $connectorFile) {
			$connectorName = basename($connectorFile, '.php');
			$this->integrationMapping['call_center'][$connectorName] = $connectorName;
		}
	}

	private function loadSMSProviders() {
		$pattern = 'modules/SMSNotifier/providers/*.php';
			
		foreach (glob($pattern) as $connectorFile) {
			$connectorName = basename($connectorFile, '.php');
			$this->integrationMapping['sms'][$connectorName] = $connectorName;
		}
	}

	private function loadOTTProviders() {
		$pattern = 'modules/CPOTTIntegration/gateways/*.php';
		$channels = CPOTTIntegration_Config_Helper::getChannels();
		$providers = [];
			
		foreach (glob($pattern) as $gatewayFile) {
			$gatewayName = basename($gatewayFile, '.php');
			$providers[] = $gatewayName;
		}

		$this->integrationMapping['ott']['groups'] = [];

		foreach ($providers as $providerName) {
			foreach ($channels as $channelName => $channelLabel) {
				if (empty($this->integrationMapping['ott']['groups'][$channelName])) {
					$this->integrationMapping['ott']['groups'][$channelName] = [
						'label' => $channelLabel,
						'options' => []
					];
				}

				try {
					$gatewayInstance = CPOTTIntegration_Gateway_Model::getGateway($providerName, $channelName);
					
					if (!empty($gatewayInstance)) {
						$this->integrationMapping['ott']['groups'][$channelName]['options'][$providerName] = $providerName;
					}
				}
				catch (Exception $e) {
					// Nothing to do
				}
			}
		}
	}

	private function loadAICameraProviders() {
		require('integration_providers.php');
		$aiCameraProviders = $providers['ai_camera'];
			
		foreach ($aiCameraProviders as $providerName => $providerInfo) {
			$this->integrationMapping['ai_camera'][$providerName] = $providerInfo['display_name'];
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('INTEGRATION_MAPPING', $this->integrationMapping);
		$viewer->assign('WEBHOOK_MAPPING', $this->webhookMapping);
		$viewer->display('modules/Settings/Vtiger/tpls/GenerateWebhookUrl.tpl');
	}
}