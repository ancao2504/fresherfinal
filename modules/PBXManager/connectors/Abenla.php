<?php

/*
    Abenla Connector
    Author: Phu Vo
    Date: 2019-08.05
    Purpose: to handle communication with Abenla Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_Abenla_Connector extends PBXManager_Base_Connector {

    public $hasExternalReport = true;   // Indicate that there is an external report to show the call history

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text', 
        'service_name' => 'text',
        'auth_user' => 'text',
        'auth_key' => 'password',   // Modified by Hieu Nguyen on 2020-01-09 to secure this field
        'co_line_number' => 'text',
    ];

    protected $webserviceUrl;
    protected $serviceName;
    protected $authUser;
    protected $authKey;
    protected $coLineNumber;

    // Return the connector name
    public function getGatewayName() {
        return 'Abenla';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->serviceName = $serverModel->get('service_name');
        $this->authUser = $serverModel->get('auth_user');
        $this->authKey = $serverModel->get('auth_key');
        $this->coLineNumber = $serverModel->get('co_line_number');
    }
	
	// Return webservice url based on request action
	public function getServiceUrl($action) {
		return $this->webserviceUrl . 'api/' . $action;
	}

    // Make a phone call. Override this function to handle click-to-call logic
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $serviceUrl = $this->getServiceUrl('AutoCallV2');

        $headers = [];

        $params = [
            'ServiceName' => $this->serviceName,
            'AuthUser' => $this->authUser,
            'AuthKey' => $this->authKey,
            'Ext' => $user->phone_crm_extension,
            'PhoneNumber' => $this->coLineNumber . '/' . $receiverNumber,
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[Abenla] Make call request: '. $serviceUrl, $headers, $params, $response);

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
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['CallNumber'], $data['ReceiptNumber'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $data['KeyRinging'],
                'gateway' => 'Abenla',
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
                'endtime' => date('Y-m-d H:i:s'),
                'totalduration' => '',  // No data
                'billduration' => '',   // No data
                'recordingurl' => '',   // No data
            ];

            PBXManager_Data_Model::handleHangupCall($data['KeyRinging'], $params);
        }
    }
}