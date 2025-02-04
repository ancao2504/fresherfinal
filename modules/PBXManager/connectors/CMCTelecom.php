<?php

/*
    CMCTelecom_Connector
    Author: Hieu Nguyen
    Date: 2018-10-05
    Purpose: to handle communication with CMC Telecom Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_CMCTelecom_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text',
        'domain' => 'text',
        'api_key' => 'password',
    ];
    protected $webserviceUrl;
    protected $domain;
    protected $apiKey;

    // Return the connector name
    public function getGatewayName() {
        return 'CMCTelecom';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->domain = $serverModel->get('domain');
        $this->apiKey = $serverModel->get('api_key');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $serviceUrl = $this->getServiceUrl('calls/non-block');
        $headers = [
            'Authorization: basic ' . $this->apiKey,
            'Domain: ' . $this->domain,
            'Role: user',
        ];

        $params = [
            'type' => 'external',
            'caller' => $user->phone_crm_extension,
            'callee' => $receiverNumber
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[CMCTelecom] Make call request: '. $serviceUrl, $headers, $params, $response);

        if ($response) {
            if ($response->message == 'CREATE SUCCESS') {
                return ['success' => true];
            }

            if ($response->message == 'Extension is offline') {
                return ['success' => false, 'message' => vtranslate('LBL_MAKE_CALL_DEVICE_OFFLINE_ERROR_MSG', 'PBXManager')];
            }
        }

        return ['success' => false];
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
        if ($data['state'] == 'ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['destination'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s', $data['startedAt']),
                'sourceuuid' => $data['uuid'],
                'gateway' => 'CMCTelecom',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => $data['pbxnumber'],
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call update
        if ($data['state'] != 'ringing' && $data['state'] != 'hangup') {
            $params = [
                'callstatus' => $data['state']
            ];

            PBXManager_Data_Model::updateCallStatus($data['uuid'], $params);
        }

        // Call ended
        if ($data['state'] == 'hangup') {
            $params = [
                'callstatus' => $data['state'],
            ];

            if (!empty($data['recordUrl'])) {
                $params['callstatus'] = 'completed';
                $params['endtime'] = date('Y-m-d H:i:s', $data['endedAt']);
                $params['totalduration'] = $data['duration'];
                $params['billduration'] = $data['duration'];
                $params['recordingurl'] = $data['recordUrl'];
            }

            PBXManager_Data_Model::handleHangupCall($data['uuid'], $params);
        }
    }
}