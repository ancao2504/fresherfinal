<?php

/*
    Webhook CloudFoneConnector
    Author: Hieu Nguyen
    Date: 2019-04-09
    Purpose: to handle request from CloudFone webhook and forward that request into real-time service
*/

require_once('include/utils/CallCenterUtils.php');

class CloudFoneConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        CallCenterUtils::checkConfig();
        
        // Get data from webhook
        $request = CallCenterUtils::getRequest();
        $data = $request->getAllPurified();

        // Edit by Tung Bui 30.09.2021 - Handle transfer case
        $numberPBXInfo = explode("-", $data['NumberPBX']);
        if ($numberPBXInfo[0] == 'TRANSFERRED') {
            // Refactor direction of parent call
            if (strlen($data['ReceiptNumber']) > 4) {
                $data['Direction'] = 'Outbound';
            }
        }        
        // END Edit by Tung Bui 30.09.2021

        CallCenterUtils::saveLog('[CloudFone] Webhook data', null, $data);

        if (count($data) <= 2) {
            echo 'Listening!';
        }        

        // Replace Ringing_Out, Up_Out, Down_Out into normal event name
        $data['Status'] = str_replace('_Out', '', $data['Status']);

        // Ignore empty caller or receiver signals
        if (empty($data['CallNumber']) || empty($data['ReceiptNumber'])) {
            exit;
        }

        // Ignore the duplicated signal when the call center retry a timeout request
        if (PBXManager_CloudFone_Connector::isExists($data['KeyRinging'], $data['Status'])) {
            exit;
        }

        // Check receiver
        $callId = $data['KeyRinging'];
        $receiverId = '';

        if ($data['Status'] == 'Ringing') {
            $agentExtNumber = CallCenterUtils::getAgentExtNumber($data['CallNumber'], $data['ReceiptNumber'], $data['Direction']);
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
                $msg['direction'] = strtoupper($data['Direction']);  // Must be INBOUND/OUTBOUND

                $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['CallNumber'], $data['ReceiptNumber'], $data['Direction']);
                $customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber, $data['Direction'] == 'Outbound', $agentExtNumber, true);
                CallCenterUtils::fillMsgDataForRingingEvent($msg, $customerPhoneNumber, $customer);

                // Register global variable so that it can be reused in handleCallEvent function
                $GLOBALS['agent'] = $agent;
                $GLOBALS['customer'] = $customer;

                // Send related call log id to update this call log instead of creating a new one
                if (!empty($customer['call_log_id'])) {
                    $msg['call_log_id'] = $customer['call_log_id'];
                }
            }

            PBXManager_CloudFone_Connector::forwardToCallCenterBridge($msg);
            CallCenterUtils::saveDebugLog('[CloudFone] Data sent to call popup for ' . $agentExtNumber, null, $msg);
        }

        // Save call history
        if (($data['Status'] == 'Ringing' && !empty($receiverId)) || $data['Status'] != 'Ringing') {
            PBXManager_CloudFone_Connector::handleCallEvent($data);
        }        
	}
}