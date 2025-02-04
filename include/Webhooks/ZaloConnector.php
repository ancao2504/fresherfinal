<?php

/*
	Webhook ZaloConnector
	Author: Hieu Nguyen
	Date: 2019-07-03
	Purpose: to handle HTTP call back from Zalo platform
*/

require_once('include/utils/ZaloUtils.php');

class ZaloConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		if (!CPSocialIntegration_Config_Helper::isZaloEnabled()) {
			echo 'Zalo Integration is supported!';
			exit;
		}

		if (!session_id())  session_start();

		// Retrieve logged in user for checking permission
		$user = ZaloUtils::getAuthenticatedUser($this);

		// Get data from webhook
		$request = ZaloUtils::getRequest();
		$data = $request->getAll();

		ZaloUtils::saveLog('[Zalo] Webhook data', null, $data);
		
		// Oauth callback
		if (isset($data['state']) && $data['state'] == 'OauthCallback') {
			// Accept oauth token from logged in admin user only
			if ($user && is_admin($user)) {
				$zaloConfig = CPSocialIntegration_Config_Helper::getZaloOAConfig();
				$appId = $zaloConfig['credentials']['app_id'];
				$secretKey = $zaloConfig['credentials']['secret_key'];
				$authCode = $request->get('code');	// Zalo returns this auth code after user accept the auth request
				$verifyCode = $_SESSION['zalo_oauth_verify_code'];	// Connection form stores this verify code in $_SESSION before calling auth request

				$success = false;
				$tokenResult = ZaloOauthUtils::getNewAccessToken($appId, $secretKey, $authCode, $verifyCode);
				
				if (!empty($tokenResult['access_token'])) {
					$success = ZaloUtils::storeAccessToken($data['oa_id'], $tokenResult);
				}

				$this->displayOauthCallbackResult($success);
				return;
			}
		}

		// Handle webhook events
		if (isset($data['event_name'])) {
			// To prevent error when Zalo send a verification event
			if (!empty($data['message']) && $data['message']['text'] == 'This is testing message') {
				echo 'You sent me a testing message!';
				return;
			}

			ZaloUtils::handleWebhookEvents($data);
			echo 'OK';
			return;
		}

		echo 'Listening!';
	}

	function displayOauthCallbackResult($success) {
		echo '<center><a href="#" onclick="window.opener.handleConnectZaloOAResult(self, '. ($success ? 'true' : 'false') .');">Click here to continue</a></center>';
	}
}