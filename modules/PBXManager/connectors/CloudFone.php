<?php

/*
    CloudFone_Connector
    Author: Hieu Nguyen
    Date: 2019-04-09
    Purpose: to handle communication with CloudFone Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_CloudFone_Connector extends PBXManager_Base_Connector {

    public $hasExternalReport = true;           // Indicate that there is an external report to show the call history
    public $hasDirectPlayRecordingApi = true;   // Indicate that this provider provides an api to play recording directly from call log
    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text', 
        'service_name' => 'text',
        'auth_user' => 'text',
        'auth_key' => 'password',
    ];
    protected $webserviceUrl;
    protected $serviceName;
    protected $authUser;
    protected $authKey;

    // Return the connector name
    public function getGatewayName() {
        return 'CloudFone';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->serviceName = $serverModel->get('service_name');
        $this->authUser = $serverModel->get('auth_user');
        $this->authKey = $serverModel->get('auth_key');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $serviceUrl = $this->getServiceUrl('AutoCall');
        $headers = [];

        $params = [
            'ServiceName' => $this->serviceName,
            'AuthUser' => $this->authUser,
            'AuthKey' => $this->authKey,
            'Prefix' => '0',
            'Ext' => $user->phone_crm_extension,
            'PhoneName' => 'CloudFone',
            'PhoneNumber' => $receiverNumber
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[CloudFone] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->result == 'success') {
            return ['success' => true];
        }

        return ['success' => false];
    }

    // Fetch history report from vendor system
    function getHistoryReport($headers, $params) {
        $serviceUrl = $this->getServiceUrl('GetCallHistory');
        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);

        return $response;
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingData($callRecordModel) {
        $recordingUrl = $callRecordModel->get('recordingurl');
        $recordingData = getRemoteFile($recordingUrl);
        
        return $recordingData;
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'Ringing' => 'ringing',
            'Up' => 'answered',
            'Down' => 'hangup',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        $statusMapping = [
            'Ringing' => 'ringing',
            'Up' => 'answered',
            'Down' => 'completed',
        ];

        // New call
        if ($data['Status'] == 'Ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['CallNumber'], $data['ReceiptNumber'], $data['Direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['Direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $data['KeyRinging'],
                'gateway' => 'CloudFone',
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
        if ($data['Status'] != 'Ringing' && $data['Status'] != 'Down') {
            $params = [
                'callstatus' => $statusMapping[$data['Status']]
            ];

            PBXManager_Data_Model::updateCallStatus($data['KeyRinging'], $params);
        }

        // Call ended
        if ($data['Status'] == 'Down') {
            $params = [
                'callstatus' => $statusMapping[$data['Status']],
                'endtime' => date('Y-m-d H:i:s')
            ];

            if (!empty($data['Data'])) {
                $params['totalduration'] = $data['Data']['TotalTimeCall'];
                $params['billduration'] = $data['Data']['RealTimeCall'];
                $params['recordingurl'] = $data['Data']['LinkFile'];
            }

            PBXManager_Data_Model::handleHangupCall($data['KeyRinging'], $params);
        }
    }
}