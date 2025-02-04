<?php

/*
    Webhook AbenlaConnector
    Author: Phu Vo
    Date: 2019-08-05
    Purpose: to handle request from Abenla webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class AbenlaConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        CallCenterUtils::checkConfig();
        
        // Get data from webhook
        $request = CallCenterUtils::getRequest();
        $data = $request->getAllPurified();

        CallCenterUtils::saveLog('[Abenla] Webhook data', null, $data);

        if (count($data) <= 2) {
            echo 'Listening!';
        }

        // Ignore empty caller or receiver signals
        if (empty($data['CallNumber']) || empty($data['ReceiptNumber'])) {
            exit;
        }

        // Ignore the duplicated signal when the call center retry a timeout request
        if (PBXManager_Abenla_Connector::isExists($data['KeyRinging'], $data['Status'])) {
            exit;
        }

        // Preprocess request base on Abenla logic
        
        $serverModel = PBXManager_Server_Model::getInstance();

        // Currently this provider has only INBOUND signal
        $data['direction'] = 'inbound'; 

        // If we using AutoCallV2 api, it will prepend CoLineNumber to CallNumber
        $coLineNumber = $serverModel->get('co_line_number');

        if (strpos($data['CallNumber'], $coLineNumber) > -1) {
            $data['CallNumber'] = str_replace($coLineNumber . '/', '', $data['CallNumber']);
        }

        // Check receiver
        $callId = $data['KeyRinging'];
        $receiverId = '';

        if ($data['Status'] == 'Ringing') {
            $agentExtNumber = CallCenterUtils::getAgentExtNumber($data['CallNumber'], $data['ReceiptNumber'], $data['direction']);
            $agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
            
            if (!empty($agent)) $receiverId = $agent['id'];
        }
        else {
            $receiverId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
        }

        // Send call event to Call Center Bridge
        if (!empty($receiverId)) {
            $stateMapping = [
                'Ringing' => 'RINGING',
                'Up' => 'ANSWERED',
                'Down' => 'HANGUP',
            ];

            $msg = [
                'call_id' => $callId,                           // Required
                'receiver_id' => $receiverId,                   // Required (CRM user id)
                'state' => $stateMapping[$data['Status']],      // Must be RINGING/ANSWERED/HANGUP/COMPLETED/CUSTOMER_INFO
            ];

            if ($data['Status'] == 'Ringing') {
                $msg['direction'] = strtoupper($data['direction']);  // Must be INBOUND/OUTBOUND

                $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['CallNumber'], $data['ReceiptNumber'], $data['direction']);
                $customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['direction'] == 'outbound', $agentExtNumber, true);
                CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

                // Register global variable so that it can be reused in handleCallEvent function
                $GLOBALS['agent'] = $agent;
                $GLOBALS['customer'] = $customer;

                // Added by Hieu Nguyen on 2020-02-04 to send related call log id to update this call log instead of creating a new one
                if (!empty($customer['call_log_id'])) {
                    $msg['call_log_id'] = $customer['call_log_id'];
                }
                // End Hieu Nguyen
            }

            PBXManager_Abenla_Connector::forwardToCallCenterBridge($msg);
            CallCenterUtils::saveDebugLog('[Abenla] Data sent to call popup for ' . $agentExtNumber, null, $msg);
        }

        // Save call history
        if (($data['Status'] == 'Ringing' && !empty($receiverId)) || $data['Status'] != 'Ringing') {
            PBXManager_Abenla_Connector::handleCallEvent($data);
        }    
    }
}