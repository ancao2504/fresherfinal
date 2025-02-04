<?php

/*
    Abenla Connector
    Author: Phu Vo
    Date: 2020-06-15
    Purpose: to handle communication with Abenla Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_VoiceCloud_Connector extends PBXManager_Base_Connector {

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
        return 'VoiceCloud';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->apiKey = $serverModel->get('api_key');
        $this->domain = $serverModel->get('domain');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $agentExt = $user->get('phone_crm_extension');
        $apiKey = $this->apiKey;
        $domain = $this->domain;
        $serviceUrl = $this->getServiceUrl('api/CallControl/dial/');
        $serviceUrl .= "from_number/{$agentExt}/to_number/{$receiverNumber}/key/{$apiKey}/domain/{$domain}";

        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'GET', [], false, false);

        CallCenterUtils::saveDebugLog('[VoiceCloud] Make call request: ' . $serviceUrl, [], [], $response);

        if ($response && $response == 'Success') {
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
        $recordingUrl = $callRecordModel->get('recordingurl');
        $recordingData = getRemoteFile($recordingUrl);  // Modified by Hieu Nguyen on 2022-01-17 to use function getRemoteFile()

        return $recordingData;
    }

    // Return the agent ext number based on the incomming customer number
    static function handleSkillBasedRouting($customerNumber, $hotline) {
        $extNumber = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);

        $response = ['extension' => $extNumber];
        return $response;
    }

    // Added by Hieu Nguyen on 2021-12-22 to handle missed call event
    static function handleMissedCallEvent(array $data) {
        $customerNumber = $data['phone_number'];
        $callId = $data['call_id'];
        $callTime = $data['timestamp'];
        $hotlineNumber = $data['hotline_number'];

        try {
            $customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerNumber);

            // Retrieve missed call alert users on config
            $userIds = CallCenterUtils::getMissedCallAlertUsers($customer);

            // Save missed call PBX log
            $mappingData = [
                'starttime' => $callTime,
                'sourceuuid' => $callId,
                'gateway' => 'VoiceCloud',
                'customer' => $customer['id'],
                'customernumber' => $customerNumber,
                'customertype' => $customer['type'],
                'hotline' => $hotlineNumber,
            ];

            PBXManager_Data_Model::saveMissedCall($mappingData, $customer, null, $userIds);

            // Save missed call activity
            CallCenterUtils::saveMissedCallLog($customer, $customerNumber, $callId, $callTime, $userIds);

            // Send push notification
            CallCenterUtils::sendMissedCallNotification($customer, $customerNumber, $userIds);

            // Send alert email
            CallCenterUtils::sendMissedAlertEmail($customer, $customerNumber, $hotlineNumber, $callTime, $userIds);
        }
        catch (Exception $e) {
            CallCenterUtils::saveDebugLog('[PBXManager_VoIP24H_Connector::handleMissedCallEvent] Error: '. $e->getMessage(), null, $e->getTrace());
        }
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
                'gateway' => 'VoiceCloud',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => '',
                'assigned_user_id' => $agent['id'],
            ];

            // Handle call ring to next agent in queue
            $pbxRecord = Vtiger_Record_Model::getInstanceByConditions('PBXManager', ['sourceuuid' => $data['call_id']]);

            if (!empty($pbxRecord) && !empty($pbxRecord->getId())) {
                PBXManager_Data_Model::updateCallStatus($data['call_id'], $params);
            }
            else {
                PBXManager_Data_Model::handleStartupCall($params);
            }
        }

        // Call update
        if ($data['state'] != 'RINGING' && $data['state'] != 'HANGUP') {
            $params = [
                'callstatus' => $statusMapping[$data['state']]
            ];

            PBXManager_Data_Model::updateCallStatus($data['call_id'], $params);
        }

        // Call ended
        if ($data['state'] == 'HANGUP') {
            if (PBXManager_Data_Model::getCallStatus($data['call_id']) == 'completed') {
                return; // Sometimes they send event CDR before HANGUP. In that case the call status is 'completed' and we don't need to update it anymore
            }

            $params = [
                'callstatus' => $statusMapping[$data['state']],
                'endtime' => date('Y-m-d H:i:s')
            ];

            PBXManager_Data_Model::handleHangupCall($data['call_id'], $params);
        }

        if ($data['state'] == 'CDR') {
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
