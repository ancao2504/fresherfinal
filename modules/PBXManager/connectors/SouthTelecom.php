<?php

/*
    SouthTelecom_Connector
    Author: Hieu Nguyen
    Date: 2018-10-30
    Purpose: to handle communication with South Telecom Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_SouthTelecom_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'webservice_url' => 'text', 
        'api_key' => 'password', 
        'auto_call_service_url' => 'text',
        'auto_call_api_key' => 'password',
        'auto_call_campaign_id' => 'text'
    ];
    protected $webserviceUrl;
    protected $apiKey;
    protected $autoCallServiceUrl;
    protected $autoCallApiKey;
    protected $autoCallCampaignId;

    // Return the connector name
    public function getGatewayName() {
        return 'SouthTelecom';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->webserviceUrl = $serverModel->get('webservice_url');
        $this->apiKey = $serverModel->get('api_key');
        $this->autoCallServiceUrl = $serverModel->get('auto_call_service_url');
        $this->autoCallApiKey = $serverModel->get('auto_call_api_key');
        $this->autoCallCampaignId = $serverModel->get('auto_call_campaign_id');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId, $hotlineNumber = '') {
        $user = Users_Record_Model::getCurrentUserModel();

        // Add hotline prefix to phone number to tell the provider make call using this hotline
        if (!empty($hotlineNumber)) {
            $receiverNumber = $hotlineNumber . $receiverNumber;
        }

        $params = [
            'callernum' => $user->phone_crm_extension,
            'destnum' => $receiverNumber,
            'secrect' => $this->apiKey,
            'version' => 3
        ];

        $params = http_build_query($params);
        $serviceUrl = $this->getServiceUrl("makecall2.php?{$params}");
        
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'GET', null, false, false);
        CallCenterUtils::saveDebugLog('[SouthTelecom] Make call request: '. $serviceUrl, null, $params, $response);

        if ($response && $response == '200') {
            return ['success' => true];
        }

        return ['success' => false];
    }

    // Make an auto call
    function makeAutoCall($receiverNumber, $textToCall, $parentId) {
        $serviceUrl = $this->autoCallServiceUrl;
        
        $params = [
            'secret' => $this->autoCallApiKey,
            'campaignid' => $this->autoCallCampaignId,
            'vez_customer_phone' => $receiverNumber,
            'text' => $textToCall
        ];

        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[SouthTelecom] Make auto call request: '. $serviceUrl, null, $params, $response);

        if ($response && $response->code == '1') {
            return ['success' => true, 'call_id' => $response->ua_uuid];
        }

        return ['success' => false];
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingData($callRecordModel) {
        $callId = $callRecordModel->get('sourceuuid');
        $parentCallId = $callRecordModel->get('parent_call_id');

        $params = [
            'calluuid' => !empty($parentCallId) ? $parentCallId : $callId,
            'secrect' => $this->apiKey,
            'version' => 3
        ];

        $params = http_build_query($params);
        $fileUrl = $this->getServiceUrl("playback2.php?{$params}");
        $recordingData = getRemoteFile($fileUrl);

        return $recordingData;
    }

    // Return the name of inbound call cache file
    static function getInboundCacheFile($customerNumber) {
        return "cache/{$customerNumber}_InboundCall.json";
    }

    // Save cache data for inbound call
    static function saveInboundCache($customerNumber, array $cacheData) {
        $cacheFile = self::getInboundCacheFile($customerNumber);
        file_put_contents($cacheFile, json_encode($cacheData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    // Get stored cache data of inbound call
    static function getInboundCache($customerNumber) {
        $cacheFile = self::getInboundCacheFile($customerNumber);
        $cacheContent = file_get_contents($cacheFile);
        if (empty($cacheContent)) return null;
        return json_decode($cacheContent, true);
    }

    // Temporary function to workarround remove hotline prefix from customer number in oubound call
    static function getCustomerPhoneNumber($callerNumber, $receiverNumber, $direction) {
        $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($callerNumber, $receiverNumber, $direction);

        if (strtoupper($direction) == 'OUTBOUND') {
            global $callCenterConfig;
            $outboundHotlines = $callCenterConfig['click2call_hotline_numbers'];

            if (!empty($outboundHotlines)) {
                foreach ($outboundHotlines as $hotlineNumber) {
                    $customerPhoneNumber = str_replace($hotlineNumber, '', $customerPhoneNumber);
                }
            }
        }

        return $customerPhoneNumber;
    }

    // Return the agent ext number based on the incomming customer number
    static function handleSkillBasedRouting($customerNumber, $hotline) {
        $extNumber = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);

        $response = ['extension' => $extNumber];
        return $response;
    }

    // Handle auto call event
    static function handleAutoCallEvent($data) {
        require_once('modules/PBXManager/workflow/VTAutoCallTask.php');
        $callId = $data['ua_uuid'];
        $callStatus = $data['acs_lastcall_status'] == 'Answer' ? 'ANSWERED' : 'BUSY';
        $answerDuration = $data['acs_call_duration'];
        $responseKey = '';
        
        if (!empty($data['useraction'])) {
            $responseKey = $data['useraction']['key'];
            VTAutoCallTask::handleResponse($callId, $responseKey);
        }

        PBXManager_Data_Model::updateAutoCallStatus($callId, $callStatus, $answerDuration, $responseKey);
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'Dialing' => 'ringing',
            'DialAnswer' => 'answered',
            'HangUp' => 'hangup',
            'CDR' => 'completed',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        $callId = $data['calluuid'];

        // New call
        if ($data['callstatus'] == 'Dialing') {
            $customerPhoneNumber = self::getCustomerPhoneNumber($data['callernumber'], $data['destinationnumber'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s', strtotime($data['starttime'])),
                'sourceuuid' => $callId,
                'gateway' => 'SouthTelecom',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => $data['dnis'],
                'parent_call_id' => ($data['direction'] == 'inbound') ? $data['parentcalluuid'] : '',   // Only inbound call has parent call id
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Subcall trimmed
        if ($data['callstatus'] == 'Trim') {
            if ($data['direction'] == 'inbound') {
                $params = [
                    'callstatus' => 'hangup'
                ];
    
                PBXManager_Data_Model::updateCallStatus($callId, $params);
            }
        }

        // Call update
        if ($data['callstatus'] == 'DialAnswer') {
            // Mark the latest subcall as answered for inbound call
            if ($data['direction'] == 'inbound') {
                $callId = PBXManager_Data_Model::getLatestSubCallId($callId);
            }

            $params = [
                'callstatus' => 'answered'
            ];

            PBXManager_Data_Model::updateCallStatus($callId, $params);
        }

        // Call ended
        if ($data['callstatus'] == 'HangUp' || $data['callstatus'] == 'CDR') {
            // Mark the latest subcall as hangup / completed for inbound call
            if ($data['direction'] == 'inbound') {
                $callId = PBXManager_Data_Model::getLatestSubCallId($callId);
            }

            $params = [
                'callstatus' => 'hangup',
            ];

            if (!empty($data['monitorfilename'])) {
                $params['callstatus'] = 'completed';
                $params['endtime'] = date('Y-m-d H:i:s', strtotime($data['endtime']));
                $params['totalduration'] = $data['totalduration'];
                $params['billduration'] = $data['billduration'];
                $params['recordingurl'] = $data['monitorfilename'];
            }

            PBXManager_Data_Model::handleHangupCall($callId, $params);
        }
    }

    // Added by Hieu Nguyen on 2021-12-22 to handle missed call event
    static function handleMissedCallEvent(array $data) {
        $customerNumber = $data['src'];
        $agentExtNumber = $data['dst'];
        $callId = $data['calluuid'];
        $callTime = date('Y-m-d H:i:s', $data['calldate']); // Call date format is in seconds
        $hotlineNumber = $data['did_number'];

        try {
            $agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
            $customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerNumber);

            // Retrieve missed call alert users on config
            $userIds = CallCenterUtils::getMissedCallAlertUsers($customer);

            // Save missed call PBX log
            $mappingData = [
                'starttime' => $callTime,
                'sourceuuid' => $callId,
                'gateway' => 'SouthTelecom',
                'customer' => $customer['id'],
                'customernumber' => $customerNumber,
                'customertype' => $customer['type'],
                'hotline' => $hotlineNumber,
            ];

            PBXManager_Data_Model::saveMissedCall($mappingData, $customer, $agent, $userIds);

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
}