<?php

/*
    Abenla Connector
    Author: Phu Vo
    Date: 2020-06-15
    Purpose: to handle communication with Abenla Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_SunOcean_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text',
        'api_key' => 'password',
        'recording_server_url' => 'text',
    ];
    
    protected $webserviceUrl;
    protected $apiKey;
    protected $recordingServerUrl;

    // Return the connector name
    public function getGatewayName() {
        return 'SunOcean';
    }

    public function getServiceUrl($action) {
		return $this->webserviceUrl . $action . '.php';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->apiKey = $serverModel->get('api_key');
        $this->recordingServerUrl = $serverModel->get('recording_server_url');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $agentExt = $user->get('phone_crm_extension');
        $serviceUrl = $this->getServiceUrl('makecall');
        $headers = [
			'accept: application/json',
			'cache-control: no-cache',
            'content-type: multipart/form-data'
        ];

        $params = [
            'key' => $this->apiKey . date('mdH'),
            'exten' => $agentExt,
            'num' => $receiverNumber
        ];
        
        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params, false);
        
        CallCenterUtils::saveDebugLog('[SunOcean] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->result == 'true') {
            return ['success' => true];
        }

        return ['success' => false];
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'RINGING' => 'ringing',
            'ANSWERED' => 'answered',
            'HANGUP' => 'hangup',
            'CDR' => 'completed',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingData($callRecordModel) {
        $recordingUrl = $this->recordingServerUrl . $callRecordModel->get('recordingurl');
        $recordingData = getRemoteFile($recordingUrl);
        
        return $recordingData;
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        $statusMapping = [
            'RINGING' => 'ringing',
            'ANSWERED' => 'answered',
            'HANGUP' => 'hangup',
            'CDR' => 'completed',
        ];

        // New call
        if ($data['state'] == 'RINGING') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from_number'], $data['to_number'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $data['call_id'],
                'gateway' => 'SunOcean',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => '',
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call update
        if ($data['state'] != 'RINGING' && $data['state'] != 'HANGUP') {
            $params = [
                'callstatus' => $statusMapping[$data['state']]
            ];

            PBXManager_Data_Model::updateCallStatus($data['call_id'], $params);
        }

        // Call ended
        if ($data['state'] == 'HANGUP'|| $data['state'] == 'CDR') {
            $params = [
                'callstatus' => $statusMapping[$data['state']],
                'endtime' => date('Y-m-d H:i:s'),
                'totalduration' => $data['duration'],
                'billduration' => $data['duration'],
                'recordingurl' => $data['recording_url'],
            ];

            PBXManager_Data_Model::handleHangupCall($data['call_id'], $params);
        }
    }
}