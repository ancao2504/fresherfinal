<?php

/**
 * Name: AhaIframe
 * Author: Phu Vo
 * Date: 2020.04.06
 * Description: CRM iframe entrypoint to provide an iframe integrated UI for Aha Chatbot
 */

require_once('include/utils/AhaUtils.php');

class AhaIframe extends CPChatBotIntegration_BaseIframe_View {

    function checkPermission(Vtiger_Request $request) {
        $this->checkConfig('Aha');
        $this->getIframeModel()->validateRequest($request);
        parent::checkPermission($request);
    }

    function preProcess(Vtiger_Request $request, $display = true) {
        global $current_user;

        // Save request to log file
        $requestData = $request->getAll();
        AhaUtils::saveLog('[Aha] Aha Call Iframe With User: ' . $current_user->user_name, [], $requestData);

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
        $this->iframeModel = CPChatBotIntegration_ChatbotIframe_Model::getInstance('Aha');

        return $this->iframeModel;
    }
}