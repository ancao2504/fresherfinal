<?php

/*
    Class FacebookUtils
    Author: Hieu Nguyen
    Date: 2020-01-13
    Purpose: To provide util functions for handling integration with Facebook
*/

require_once('include/utils/WebhookUtils.php');
require_once('vendor/autoload.php');

use Facebook\Facebook;

class FacebookUtils extends WebhookUtils {

    static function getFacebookClient($config = []) {
        if (empty($config)) {
            if (!empty($_SESSION['fb_app_config'])) {
                $config = $_SESSION['fb_app_config'];   // Cache available right at the connect process
            }
            else {
                $config = self::retrieveAppConfig();    // Retrieve from db after the connect process finished
            }
        }

        return new Facebook($config);
    }

    static function getLoginUrl($appId, $appSecret, $callbackUrl) {
        $config = [
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.10'
        ];

        $_SESSION['fb_app_config'] = $config;   // Cache app config
        $fb = self::getFacebookClient($config); // Only need to provide app config at the first time
        $helper = $fb->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl($callbackUrl, []);

        return $loginUrl;
    }

    static function callFacebookApi(Facebook $fb, string $method, string $path, string $accessToken, array $params = []) {
        $result = null;

        // TODO: implement logi to call Facebook API

        return $result;
    }

    static function getLoginToken() {
        $fb = self::getFacebookClient();
        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();

            if (!isset($accessToken)) {
                if ($helper->getError()) {
                    $error = [
                        'error' => $helper->getError(),
                        'code' => $helper->getErrorCode(),
                        'reason' => $helper->getErrorReason(),
                        'description' => $helper->getErrorDescription()
                    ];

                    self::saveDebugLog('[Facebook] OauthCallback error: ', [], [], $error);
                } 
                else {
                    self::saveDebugLog('[Facebook] OauthCallback error');
                }

                return '';
            }
        } 
        catch (Exception $ex) {
            self::saveDebugLog('[Facebook] OauthCallback error: ' . $ex->getMessage());
            return '';
        }

        return $accessToken->getValue();
    }

    // Fetch all fanpage that granted to the logged in facebook user
    static function fetchFBFanpageList($loginToken) {
        $config = $_SESSION['fb_app_config'];
        $fb = self::getFacebookClient();
        $response = $fb->get('/me/accounts', $loginToken);
        $data = $response->getDecodedBody();
        $fanpageList = [];

        foreach ($data['data'] as $pageInfo) {
            $fanpageList[$pageInfo['id']] = [
                'id' => $pageInfo['id'],
                'name' => $pageInfo['name'],
                'avatar' => "http://graph.facebook.com/{$pageInfo['id']}/picture?app_id={$config['app_id']}",
                'access_token' => $pageInfo['access_token'],
            ];
        }

        $_SESSION['fb_fanpage_list'] = $fanpageList;   // Store this list to get access token after user submit the form

        return $fanpageList;
    }

    static function storeAccessToken(array $fanpageIds) {
        $tokens = Settings_Vtiger_Config_Model::loadConfig('fb_fanpage_tokens', true) ?? [];
        self::storeAppConfig($_SESSION['fb_app_config']);   // Store in db to no need to ask user again when calling apis

        foreach ($fanpageIds as $pageId) {
            $tokens[$pageId] = $_SESSION['fb_fanpage_list'][$pageId];
            $tokens[$pageId]['issue_time'] = date('Y-m-d H:i:s');
        }

        Settings_Vtiger_Config_Model::saveConfig('fb_fanpage_tokens', $tokens);

        // Clear cache
        unset($_SESSION['fb_app_config']);
        unset($_SESSION['fb_fanpage_list']);

        return true;
    }

    static function retrieveFBFanpageInfo($pageId) {
        $tokens = Settings_Vtiger_Config_Model::loadConfig('fb_fanpage_tokens', true) ?? [];

        if (!empty($tokens[$pageId])) {
            return $tokens[$pageId];
        }

        return null;
    }

    static function retrieveAccessToken($pageId) {
        $pageInfo = self::retrieveFBFanpageInfo($pageId);

        if (!empty($pageInfo) && !empty($pageInfo['access_token'])) {
            return $pageInfo['access_token'];
        }

        return null;
    }

    static function storeAppConfig(array $config) {
        Settings_Vtiger_Config_Model::saveConfig('fb_app_config', $config);
        return true;
    }

    static function retrieveAppConfig() {
        $config = Settings_Vtiger_Config_Model::loadConfig('fb_app_config', true) ?? [];
        return $config;
    }

    static function getFBFanpageList($storePageInfo = false) {
        $tokens = Settings_Vtiger_Config_Model::loadConfig('fb_fanpage_tokens', true);
        if (empty($token)) return [];

        $fb = self::getFacebookClient();
        $fanpageList = [];

        foreach ($tokens as $pageId => $token) {
            $accessToken = $token['access_token'];
            $pageInfo = self::callFacebookApi($fb, 'GET', "{$pageId}?fields=name,fan_count", $accessToken);

            $fanpageList[] = [
                'id' => $pageId,
                'name' => $pageInfo['name'],
                'avatar' => $token['avatar'],
                'token_issue_time' => $token['issue_time'],
                'connected' => true,
                'fan_count' => $pageInfo['fan_count'],
            ];

            // Store page info back to the config
            if ($storePageInfo) {
                $tokens[$pageId]['name'] = $pageInfo['name'];
                Settings_Vtiger_Config_Model::saveConfig('fb_fanpage_tokens', $tokens);
            }
        }

        return $fanpageList;
    }

    static function getFBMessage(array $data) {
        // TODO
    }

    static function getFBMessageFromTemplate($templateId, string $message = '') {
        // TODO
    }

    static function handleWebhookEvents(array $data) {
        // TODO: Phu need to fill this function
    }

    static function saveDebugLog(string $description, array $headers = null, array $input = null, array $response = null) {
        global $socialConfig;

        if ($socialConfig['facebook']['debug'] == true) {
            parent::saveLog($description, $headers, $input, $response);
        }
    }
}