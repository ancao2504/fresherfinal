<?php

/*
    OmiCall_Connector
    Author: Hieu Nguyen
    Date: 2021-04-20
    Purpose: to handle communication with OmiCall Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_OmiCall_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text',
        'api_key' => 'password',
    ];
    protected $webserviceUrl;
    protected $apiKey;

    // Return the connector name
    public function getGatewayName() {
        return 'OmiCall';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->apiKey = $serverModel->get('api_key');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();

        $headers = [
            'Authorization: Bearer ' . $this->getAccessToken()
        ];

        $params = [
            'hotline' => PBXManager_Logic_Helper::getDefaultOutboundHotline(),
            'extension' => $user->phone_crm_extension,
            'phone_number' => $receiverNumber,
        ];

        $serviceUrl = $this->getServiceUrl('click2call');
        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[OmiCall] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->payload) {
            if ($response->payload->status == 'fail') {
                return ['success' => false, 'message' => $response->payload->message];
            }

            return ['success' => true];
        }

        return ['success' => false, 'message' => 'Unknown error'];
    }

    // Get tenant access token
    function getAccessToken() {
        $serviceUrl = $this->getServiceUrl('auth?apiKey=' . $this->apiKey);
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'GET', null, false, true);

        if ($response && !empty($response->payload)) {
            return $response->payload->access_token;
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
            'ringing' => 'ringing',
            'answered' => 'answered',
            'hangup' => 'hangup',
            'cdr' => 'completed',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        $callId = $data['call_uuid'];

        // New call
        if ($data['state'] == 'ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from_number'], $data['to_number'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $callId,
                'gateway' => 'OmiCall',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => $data['hotline'],
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call update
        if ($data['state'] == 'answered') {
            $params = [
                'callstatus' => 'answered'
            ];

            PBXManager_Data_Model::updateCallStatus($callId, $params);
        }

        // Call ended
        if ($data['state'] == 'hangup' || $data['state'] == 'cdr') {
            $params = [
                'callstatus' => 'hangup',
            ];

            if ($data['state'] == 'cdr') {
                $params['callstatus'] = 'completed';
                $params['endtime'] = date('Y-m-d H:i:s');
                $params['totalduration'] = $data['duration'];
                $params['billduration'] = $data['bill_sec'];
                $params['recordingurl'] = $data['recording_file'];
            }

            PBXManager_Data_Model::handleHangupCall($callId, $params);
        }
    }
}