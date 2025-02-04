<?php

/**
 * Name: HanaIframe
 * Author: Phu Vo
 * Date: 2020.04.06
 * Description: CRM iframe entrypoint to provide an iframe integrated UI for Hana Chatbot
 */

require_once('include/utils/HanaUtils.php');

class HanaIframe extends CPChatBotIntegration_BaseIframe_View {

    function checkPermission(Vtiger_Request $request) {
        checkAccessForbiddenFeature('HanaIntegration');
        $this->checkAuthenticate($request); // Added by Hieu Nguyen on 2020-09-15 to check authenticate
        $this->checkConfig('Hana');
        $this->getIframeModel()->validateRequest($request);
        parent::checkPermission($request);
    }

    // Implemented by Hieu Nguyen on 2020-09-15 to check authenticate
    function checkAuthenticate(Vtiger_Request $request) {
        authenticateUserByAccessKey($request->get('access_key'));
        
        if (empty($_SESSION['authenticated_user_id'])) {
            throw new AppException('Anauthorized access!', 401);
        }
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        global $current_user;

        // Save request to log file
        $requestData = $request->getAll();
        HanaUtils::saveLog('[Hana] Hana Call Iframe With User: ' . $current_user->user_name, [], $requestData);

        parent::preProcess($request, $display);
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
        $this->iframeModel = CPChatBotIntegration_ChatbotIframe_Model::getInstance('Hana');

        return $this->iframeModel;
    }
}