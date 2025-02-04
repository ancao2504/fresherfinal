<?php

/*
	Action SaveGoogleIntegrationConfig
	Author: Hieu Nguyen
	Date: 2022-06-15
	Purpose: to save settings submitted from Google Integration Config form
*/

require_once('include/utils/CustomConfigUtils.php');

class Settings_Vtiger_SaveGoogleIntegrationConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('saveSettings');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}

	public function saveSettings(Vtiger_Request $request) {
		$config = $request->get('config');
		$customConfig = [
			'googleConfig.oauth.client_id' => $config['oauth']['client_id'],
			'googleConfig.oauth.client_secret' => $config['oauth']['client_secret'],
			'googleConfig.maps.maps_and_places_api_key' => $config['maps']['maps_and_places_api_key'],
			'googleConfig.maps.geocoding_api_key' => $config['maps']['geocoding_api_key'],
			'googleConfig.recaptcha.site_key' => $config['recaptcha']['site_key'],
			'googleConfig.recaptcha.secret_key' => $config['recaptcha']['secret_key'],
			'googleConfig.firebase.fcm_sender_id' => $config['firebase']['fcm_sender_id'],
			'googleConfig.firebase.fcm_server_key' => $config['firebase']['fcm_server_key'],
		];
		CustomConfigUtils::saveCustomConfigs($customConfig);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}
}