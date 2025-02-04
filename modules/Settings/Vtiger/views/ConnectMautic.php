<?php

/*
	File: ConnectMautic.php
	Author: Hieu Nguyen
	Date: 2021-11-12
	Purpose: to connect Mautic using Oauth2 method
*/

require_once('vendor/autoload.php');
use Mautic\Auth\ApiAuth;

class Settings_Vtiger_ConnectMautic_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct();
	}

	public function preProcess (Vtiger_Request $request, $display=true) {
		return;
	}
	
	public function postProcess (Vtiger_Request $request) {
		return;
	}
	
	public function getHeaderScripts(Vtiger_Request $request) {
		return [];
	}
	
	function getHeaderCss(Vtiger_Request $request) {
		return [];
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT_MAUTIC', $moduleName);
	}

	public function process(Vtiger_Request $request) {
		global $site_URL;
		$moduleName = $request->getModule(false);

		if (empty($request->get('code'))) {
			$callbackUrl = $site_URL . '/index.php?module=Vtiger&parent=Settings&view=ConnectMautic';

			$settings = [
				'baseUrl' => $request->get('base_url'),
				'version' => 'OAuth2',
				'clientKey' => $request->get('client_id'),
				'clientSecret' => $request->get('client_secret'),
				'callback' => $callbackUrl,
			];

			$_SESSION['mautic_oauth_params'] = $settings;
		}
		else {
			$settings = $_SESSION['mautic_oauth_params'];
			unset($_SESSION['mautic_oauth_params']);
		}

		$apiAuth = new ApiAuth();
		$auth = $apiAuth->newAuth($settings);

		try {
			if ($auth->validateAccessToken()) {
				if ($auth->accessTokenUpdated()) {
					$config = CPMauticIntegration_Config_Helper::loadConfig();
					$credentials = $auth->getAccessTokenData();

					// Store credentials into config
					$credentials['base_url'] = $settings['baseUrl'];
					$credentials['client_id'] = $settings['clientKey'];
					$credentials['client_secret'] = $settings['clientSecret'];
					$config['credentials'] = $credentials;
					CPMauticIntegration_Config_Helper::saveConfig($config);

					echo '<div style="text-align: center; color: green; margin: 50px 10px">'. vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT_MAUTIC_SUCCESS_MSG', $moduleName) .'</div>';
				}
			}
			else {
				throw new Exception();
			}
		}
		catch (Exception $ex) {
			echo '<div style="text-align: center; color: red; margin: 50px 10px">'. vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG_CONNECT_MAUTIC_ERROR_MSG', $moduleName) .'</div>';
		}
	}
}