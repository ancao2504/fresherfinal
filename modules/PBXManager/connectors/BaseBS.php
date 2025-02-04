<?php

/*
    BaseBS_Connector
    Author: Hieu Nguyen
    Date: 2021-07-06
    Purpose: to handle communication with BaseBS Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_BaseBS_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text',
    ];
    protected $webserviceUrl;

    // Return the connector name
    public function getGatewayName() {
        return 'BaseBS';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken($user)
        ];

        $params = [
            'extension' => $user->phone_crm_extension,
            'phone' => $receiverNumber,
        ];

        $serviceUrl = $this->getServiceUrl('MakeCall');
        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[BaseBS] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->code == 200) {
            return ['success' => true];
        }

        return ['success' => false, 'message' => $response->message];
    }

    // Get tenant access token
    function getAccessToken($user) {
        $user = Users_Record_Model::getCurrentUserModel();
        $userConfig = Users_Preferences_Model::loadPreferences($user->getId(), 'callcenter_config', true) ?? [];

        $params = [
            'username' => $userConfig['username'],
            'accessKey' => $userConfig['access_key'],
            'extension' => $user->phone_crm_extension
        ];

        $serviceUrl = $this->getServiceUrl('GetToken');
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[BaseBS] Get token request: '. $serviceUrl, [], $params, $response);

        if ($response && $response->code == 200) {
            return $response->token;
        }

        return null;
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingData($callRecordModel) {
        $recordingUrl = $callRecordModel->get('recordingurl');
        $recordingData = getRemoteFile($recordingUrl);
        
        return $recordingData;
    }

    // Return the agent ext number based on the incomming customer number
    static function handleSkillBasedRouting($customerNumber, $hotline) {
        return 'Not supported yet!';
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'RINGING' => 'ringing',
            'ANSWER' => 'answered',
            'NOANSWER' => 'hangup',
            'SUCCESS' => 'hangup',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        $callId = $data['LinkedID'];

        // New call
        if ($data['CallStatus'] == 'RINGING') {
            $customerPhoneNumber = $data['CallPhone'];

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => $data['CallStartTime'],
                'sourceuuid' => $callId,
                'gateway' => 'BaseBS',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => $data['Hotline'],
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call update
        if ($data['CallStatus'] == 'ANSWER') {
            $params = [
                'callstatus' => 'answered'
            ];

            PBXManager_Data_Model::updateCallStatus($callId, $params);
        }

        // Call ended
        if ($data['CallStatus'] == 'NOANSWER' || $data['CallStatus'] == 'SUCCESS') {
            $params['callstatus'] = 'hangup';
            $params['endtime'] = $data['CallEndTime'];
            $params['totalduration'] = $data['TotalDuration'];

            if ($data['CallStatus'] == 'SUCCESS') {
                $params['billduration'] = $data['BillDuration'];
            }

            PBXManager_Data_Model::updateCall($callId, $params);
        }
    }
}