<?php

/**
 * Name: BBHIframe
 * Author: Phu Vo
 * Date: 2020.04.06
 * Description: CRM iframe entrypoint to provide an iframe integrated UI for BBH Chatbot
 */

require_once('include/utils/BBHUtils.php');

class BBHIframe extends CPChatBotIntegration_BaseIframe_View {

    function checkPermission(Vtiger_Request $request) {
        checkAccessForbiddenFeature('BBHIntegration');
        $this->fixUrlParams($request);  // Added by Hieu Nguyen on 2020-09-17 to do a quick fix for url params
        $this->checkConfig('BotBanHang');

        // Modified by Hieu Nguyen on 2020-09-16 to support auth mode
        if ($request->get('mode') != 'auth') {
            $this->checkAccessToken($request);
            $this->getIframeModel()->validateRequest($request);
        }
        // End Hieu Nguyen

        parent::checkPermission($request);
    }

    // Implemented by Hieu Nguyen on 2020-09-17 to check access token in every iframe request
    function checkAccessToken(Vtiger_Request &$request) {
        $accessToken = $request->get('access_token');
        if (empty($accessToken)) die('No param access_token provided!');
        $chatBotConfig = CPChatBotIntegration_Config_Helper::getConfig();

        // Call API to check access token
        $serviceUrl = BBHUtils::getServiceUrl('widget', 'partner-authenticate');
        $params = [
            'access_token' => $accessToken,
            'secret_key' => $chatBotConfig['params']['widget_secret_key'],
        ];

        $result = BBHUtils::callBBHApi($serviceUrl, 'POST', [], $params);

        // Access token is not valid. Refuse the request
        if (!$result['succes']) die('Provided access_token is not valid!');

        // Collect data
        $data = $result['data']['public_profile'];
        $data = array_merge($data, $result['data']['conversation_contact']);
        $pageId = $data['fb_page_id'];

        // Get issued tokens
        $issuedTokens = Settings_Vtiger_Config_Model::loadConfig('chatbot_bbh_tokens', true) ?? [];

        // Oauth token is not matched. Refuse the request
        if (!$issuedTokens || !$issuedTokens[$pageId] || $issuedTokens[$pageId]['token'] != $data['token_partner']) {
            die('Provided token_partner not matched!');
        }

        // Set data into the request
        foreach ($data as $key => $value) {
            if ($key != 'token_partner') $request->set($key, $value);       // This param is secret
            if ($key == 'fb_client_id') $request->set('client_id', $value); // Standardize this param
        }
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        global $current_user;

        // Save request to log file
        $requestData = $request->getAll();
        BBHUtils::saveLog('[BBH] BBH Call Iframe With User: ' . $current_user->user_name, [], $requestData);

        parent::preProcess($request, $display);
    }

    // Override by Hieu Nguyen on 2020-09-16 to support auth mode
    function process(Vtiger_Request $request) {
        $this->fixUrlParams($request);  // Added by Hieu Nguyen on 2020-09-17 to do a quick fix for url params

        if ($request->get('mode') == 'auth') {
            $this->handleAuthRequest($request);
            exit;
        }

        parent::process($request);
    }

    // Implemented by Hieu Nguyen on 2020-09-16 to handle auth request
    function handleAuthRequest(Vtiger_Request $request) {
        global $chatBotConfig;
        $pageId = $request->get('page_id');
        $accessToken = $request->get('access_token');
        if (empty($pageId)) die('No param page_id provided!');
        if (empty($accessToken)) die('No param access_token provided!');
        $success = false;
        $errorMsg = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $request->get('username');
            $password = $request->get('password');
            
            if (empty($username) || empty($password)) {
                $errorMsg = 'Both Username and Password must be provided!';
            }
            else {
                // Login
                $userEntity = CRMEntity::getInstance('Users');
                $userEntity->column_fields['user_name'] = $username;

                if ($userEntity->doLogin($password)) {
                    $success = true;
                }
                else {
                    $errorMsg = 'Credentials not matched. Please try again!';
                }

                if ($success) {
                    // Generate token
                    $oauthToken = md5(time());
                    $tokenInfo = ['token' => $oauthToken, 'issued_time' => date('Y-m-d H:i:s')];

                    // Save new token
                    $tokens = Settings_Vtiger_Config_Model::loadConfig('chatbot_bbh_tokens', true) ?? [];
                    $tokens[$pageId] = $tokenInfo;
                    Settings_Vtiger_Config_Model::saveConfig('chatbot_bbh_tokens', $tokens);

                    // Redirect with oauth token
                    $redirectUrl = "{$chatBotConfig['bbh']['oauth_redirect_url']}?access_token={$accessToken}&token_partner={$oauthToken}";
                    header("Location: {$redirectUrl}");
                    exit;
                }
            }
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('ERROR_MSG', $errorMsg);
        $viewer->display('modules/CPChatBotIntegration/tpls/ChatbotIframeAuth.tpl');
    }

	function getIframeData(Vtiger_Request $request) {
        $iframeData = parent::getIframeData($request);
        $iframeModel = $this->getIframeModel();

        $iframeData['meta_data'] = $iframeModel->getIframeMetaData();

        // Get Customer info
        $customer = $iframeModel->getCustomerFromRequest($request->getAll());

        $iframeData['customer_data'] = $iframeModel->getCustomerDataByRecordModel($customer);
        $iframeData['customer_display'] = $iframeModel->getCustomerDisplayDataFromRecordModel($customer);
        $iframeData['bot_name'] = $iframeModel->channel;

        return $iframeData;
    }

    function getIframeModel() {
        if (!empty($this->iframeModel)) return $this->iframeModel;
        $this->iframeModel = CPChatBotIntegration_ChatbotIframe_Model::getInstance('BotBanHang');

        return $this->iframeModel;
    }

    // Implemented by Hieu Nguyen on 2020-09-17 to do work-arround fix for wrong url params concat by Bot Ban Hang
    function fixUrlParams(Vtiger_Request &$request) {
        $modeString = $request->get('mode');
        
        if (strpos($modeString, '?') > 0) {
            $mode = substr($modeString, 0, strpos($modeString, '?'));
            $request->set('mode', $mode);

            $remainingString = substr($modeString, strpos($modeString, '?') + 1);
            parse_str($remainingString, $remainingParams);

            foreach ($remainingParams as $key => $value) {
                $request->set($key, $value);
            }
        }
    }
}