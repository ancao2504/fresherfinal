<?php

/*
    MiTek_Connector
    Author: Hieu Nguyen
    Date: 2019-04-16
    Purpose: to handle communication with MiTek Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_MiTek_Connector extends PBXManager_Base_Connector {

    // Return the connector name
    public function getGatewayName() {
        return 'MiTek';
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $serviceUrl = $this->getServiceUrl('call/clicktocall');
        $headers = [];

        $params = [
            'secret' => $this->apiKey,
            'extension' => $user->phone_crm_extension,
            'phone' => $receiverNumber
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'GET', $params);
        CallCenterUtils::saveDebugLog('[MiTek] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->code == 200 && $response->message == 'success') {
            return ['success' => true];
        }

        return ['success' => false];
    }

    // Get the right call direction from the request
    static function getCallDirection($callType) {
        if (strpos(strtoupper($callType), 'INBOUND') !== false) {
            return 'inbound';
        }

        return 'outbound';
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingData($callRecordModel) {
        $recordingUrl = $callRecordModel->get('recordingurl');
        $recordingData = getRemoteFile($recordingUrl);
        
        return $recordingData;
    }

    // Return the agent ext number based on the incomming customer number
    static function handleSkillBasedRouting($customerNumber, $hotline) {
        if (strpos($customerNumber, '84') === 0) {
            $customerNumber = '0' . substr($customerNumber, 2);   // Replace prefix 84 with 0
        }

        $extNumber = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);

        $response = ['extension' => $extNumber];
        return $response;
    }

    static function isExists($callId, $status) {
        return PBXManager_Data_Model::isExists($callId, $status);
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        // New call
        if ($data['event'] == 'ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['fromnumber'], $data['tonumber'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => $data['event'],
                'starttime' => $data['calldate'],
                'sourceuuid' => $data['callrefid'],
                'gateway' => 'MiTek',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => '',    // No data
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call update
        if ($data['event'] != 'ringing' && $data['event'] != 'completed') {
            $params = [
                'callstatus' => $data['event']
            ];

            PBXManager_Data_Model::handleHangupCall($data['callrefid'], $params);
        }

        // Call completed
        if ($data['event'] == 'completed') {
            if (!empty($data['recording_file'])) {
                $params['callstatus'] = $data['event'];
                $params['endtime'] = $data['endtime'];
                $params['totalduration'] = $data['duration'];
                $params['billduration'] = $data['billsec'];
                $params['recordingurl'] = $data['recording_file'];
            }

            PBXManager_Data_Model::handleHangupCall($data['callrefid'], $params);
        }
    }
}