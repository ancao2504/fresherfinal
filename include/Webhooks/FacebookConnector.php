<?php

/*
    Webhook FacebookConnector
    Author: Hieu Nguyen
    Date: 2020-01-13
    Purpose: to handle HTTP call back from Facebook platform
*/

require_once('include/utils/FacebookUtils.php');

class FacebookConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        if (!session_id())  session_start();

        // Retrieve logged in user for checking permission
        $user = FacebookUtils::getAuthenticatedUser($this);

        // Get data from webhook
        $request = FacebookUtils::getRequest();
        $data = $request->getAll();

        FacebookUtils::saveLog('[Zalo] Webhook data', null, $data);
        
        if (isset($data['action']) && $user && is_admin($user)) {
            // Redirect to facebook oauth page
            if ($data['action'] == 'GetOauthUrl') {
                $loginUrl = FacebookUtils::getLoginUrl($data['app_id'], $data['app_secret'], $data['callback_url']);
                echo $loginUrl;
            }

            // Oauth callback
            if ($data['action'] == 'OauthCallback') {
                $this->displayFBFanpageSelector();
            }

            // Connect fanpage
            if ($data['action'] == 'ConnectFanpage') {
                if (empty($data['fanpage_ids'])) {
                    unset($_SESSION['fb_fanpage_list']);
                    echo '<center><span style="color: red">' . vtranslate('LBL_SOCIAL_CONFIG_FB_FANPAGE_SELECTOR_NO_FANPAGE_SELECTED_ERROR_MSG', 'CPSocialIntegration') . '</span></center>';
                    exit;
                }

                $result = FacebookUtils::storeAccessToken($data['fanpage_ids']);
                self::displayConnectFanpageResult($result);
            }

            return;
        }

        // Handle webhook events
        if (isset($data['event_name'])) {
            FacebookUtils::handleWebhookEvents($data);
            echo 'OK';
            return;
        }

        echo 'Listening!';
	}

    function displayFBFanpageSelector() {
        $loginToken = FacebookUtils::getLoginToken();
        $fanpageList = FacebookUtils::fetchFBFanpageList($loginToken);
        // $fanpageList = $_SESSION['fb_fanpage_list'];

        $viewer = new Vtiger_Viewer();
        $viewer->assign('FANPAGE_LIST', $fanpageList);
        $viewer->display('modules/Settings/Vtiger/tpls/SocialIntegrationConfigFBFanpageSelector.tpl');
    }

    function displayConnectFanpageResult($result) {
        $guideMsg = vtranslate('LBL_SOCIAL_CONFIG_CLICK_HERE_TO_CONTINUE', 'CPSocialIntegration');
        echo '<center><a href="#" onclick="window.opener.handleConnectFBFanpageResult(self, '. $result .');">'. $guideMsg .'</a></center>';
    }
}