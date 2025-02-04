<?php

/*
    Stringee_Connector
    Author: Hieu Nguyen
    Date: 2020-04-16
    Purpose: to handle communication with Stringee Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');
require_once('libraries/PHP-JWT/src/JWT.php');
use \Firebase\JWT\JWT;

class PBXManager_Stringee_Connector extends PBXManager_Base_Connector {

    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'rest_service_url' => 'text', 
        'icc_service_url' => 'text',
        'api_key' => 'password',
        'secret_key' => 'password',
    ];
    protected $restServiceUrl;
    protected $iccServiceUrl;
    protected $apiKey;
    protected $secretKey;

    // Return the connector name
    public function getGatewayName() {
        return 'Stringee';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->restServiceUrl = $serverModel->get('rest_service_url');
        $this->iccServiceUrl = $serverModel->get('icc_service_url');
        $this->apiKey = $serverModel->get('api_key');
        $this->secretKey = $serverModel->get('secret_key');
    }

    // To access non-static methods in static functions
    static function getInstance() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $instance = new PBXManager_Stringee_Connector();

        if ($serverModel->get('gateway') == $instance->getGatewayName()) {
            return $serverModel->getConnector();
        }
        
        return $instance;
    }

    public function getFreeCallToken($customerNumber) {
        if ($customerNumber == '') return;
        $customerNumber = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($customerNumber);
        $serviceUrl = "https://v1.stringee.com/dev_urls/access_token/access-token-for-btncall.php?phone_number={$customerNumber}";
        $client = $this->getRestClient($serviceUrl, []);
        $response = $this->callRestApi($client, 'GET', []);

        if (empty($response) || $response->message != 'Success') {
            return;
        }

        return $response->access_token;
    }

    // Return token for web phone
    public function getWebPhoneToken() {
        return $this->getAccessToken();
    }

    private function getAccessToken($type = 'icc_api') {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $agentUserId = $currentUserModel->get('phone_crm_extension');
        $isAgentUserId = !is_numeric($agentUserId);

        // Do nothing if the extension is SIP phone number
        if (!$isAgentUserId) return;

        // Generate token if it is the agent user id
        $now = time();
        $exp = $now + 3600;

        $header = ['cty' => 'stringee-api;v=1'];
        $payload = [
            'jti' => $this->apiKey . '-' . $now,
            'iss' => $this->apiKey,
            'exp' => $exp,
            'userId' => $agentUserId
        ];

        if ($type == 'icc_api') {
            $payload['icc_api'] = true;
        }

        if ($type == 'rest_api') {
            $payload['rest_api'] = true;
        }

        $token = JWT::encode($payload, $this->secretKey, 'HS256', null, $header);
        return $token;
    }

    // Make a phone call
    public function makeCall($receiverNumber, $parentId) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $agentUserId = $currentUserModel->get('phone_crm_extension');
        $isAgentUserId = !is_numeric($agentUserId);

        // Do nothing if the extension is SIP phone number
        if (!$isAgentUserId) return;

        $serviceUrl = $this->iccServiceUrl . 'call/callout';
        $authToken = $this->getAccessToken('rest_api');
        $hotline = PBXManager_Logic_Helper::getDefaultOutboundHotline();
        $hotline = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($hotline);
        $receiverNumber = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($receiverNumber);
        
        $headers = ['X-STRINGEE-AUTH: '. $authToken];

        $params = [
            'agentUserId' => $agentUserId,
            'toAgentFromNumberDisplay' => $hotline,
            'toAgentFromNumberDisplayAlias' => "callout_{$hotline}->{$receiverNumber}",
            'toCustomerFromNumber' => $hotline,
            'customerNumber' => $receiverNumber,
            'device' => 'ipphone'
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[Stringee] Make call request: '. $serviceUrl, $headers, $params, $response);

        // Return result
        if (empty($response)) {
            return ['success' => false];
        }

        if ($response->r !== 0) {
            if ($response->msg == 'AGENT_NOT_FOUND_OR_IN_ANOTHER_CALL') {
                return ['success' => false, 'message' => vtranslate('LBL_MAKE_CALL_IN_BREAK_TIME_ERROR_MSG', 'PBXManager')];
            }

            return ['success' => false];
        }

        return ['success' => true];
    }

    // Make auto call that convert text into speech, usefull for advertise call or OTP confirmation call
    public function autoCall($receiverNumbers = [], $textToSpeech) {
        $serviceUrl = $this->iccServiceUrl . 'call2/callout';
        $authToken = $this->getAccessToken('rest_api');

        $headers = ['X-STRINGEE-AUTH: '. $authToken];
        $hotline = PBXManager_Logic_Helper::getDefaultOutboundHotline();
        $hotline = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($hotline);
        $receivers = [];

        foreach ($receiverNumbers as $phoneNumber) {
            $phoneNumber = PBXManager_Logic_Helper::addVnCountryCodeToPhoneNumber($phoneNumber);
            $receivers[] = [
                'type' => 'external',
                'number' => $phoneNumber,
                'alias' => $phoneNumber
            ];
        }

        $params = [
            'from' => [
                'type' => 'external',
                'number' => $hotline,
                'alias' => $hotline
            ],
            'to' => $receiverNumbers,
            'answer_url' => 'http://v2.stringee.com:8282/answer_url',
            'actions' => [
                [
                    'action' => 'talk',
                    'text' => $textToSpeech
                ]
            ]
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[Stringee] Make auto call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->r === 0) {
            return true;
        }

        return false;
    }

    // Transfer a call to another agent
    public function transferCall($callId, $destAgentExt, $destAgentName) {
        $serviceUrl = $this->restServiceUrl . 'call2/transfer';
        $authToken = $this->getAccessToken('rest_api');

        $headers = ['X-STRINGEE-AUTH: '. $authToken];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $curAgentExt = $currentUserModel->get('phone_crm_extension');

        $params = [
            'callId' => $callId,
            'fromUserId' => $curAgentExt,
            'to' => [
                'type' => 'internal',
                'number' => $destAgentExt,
                'alias' => $destAgentName
            ]
        ];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'POST', $params);
        CallCenterUtils::saveDebugLog('[Stringee] Transfer call request: '. $serviceUrl, $headers, $params, $response);

        if ($response && $response->r === 0) {
            PBXManager_Data_Model::markCallAsTransferred($callId, $curAgentExt, $destAgentExt);

            // Notify the call popup to change to hangup state
            $msg = [
                'call_id' => $callId,                           // Required
                'receiver_id' => $currentUserModel->getId(),    // Required (CRM user id)
                'state' => 'TRANSFERRED',                       // Must be RINGING/ANSWERED/HANGUP/TRANSFERRED/COMPLETED/CUSTOMER_INFO
            ];

            self::forwardToCallCenterBridge($msg);
            CallCenterUtils::saveDebugLog('[Stringee] Data sent to call popup for ' . $curAgentExt, null, $msg);
            return true;
        }

        return false;
    }

    // Check stringee agent status
    public function checkAgentStatus($agentExt) {
        $statusMapping = [
            0 => 'BUSY',
            1 => 'FREE',
            2 => 'WRAPUP',
        ];

        $serviceUrl = $this->iccServiceUrl . 'agent?stringee_user_id=' . $agentExt;
        $authToken = $this->getAccessToken('rest_api');
        $headers = ['X-STRINGEE-AUTH: '. $authToken];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'GET', '');
        CallCenterUtils::saveDebugLog('[Stringee] Check agent status request: '. $serviceUrl, $headers, null, $response);

        if ($response && $response->r === 0) {
            if (!empty($response->data) && !empty($response->data->agents)) {
                $agentInfo = $response->data->agents[0];
                if ($agentInfo->online_status == 0) return 'OFFLINE';

                $agentStatus = $agentInfo->system_status;
                return $statusMapping[$agentStatus];
            }
        }

        return false;
    }

    // Get agent ext number from ringing event
    static function getAgentExtNumberFromRingingEvent(array $data, bool $isTransferredCall = false) {
        $agentExtNumber = CallCenterUtils::getAgentExtNumber($data['from']['number'], $data['to']['number'], $data['direction']);
        
        // Outbound call before transfer will have EXT number exactly in request_from_user_id property
        if ($data['direction'] == 'outbound' && !$isTransferredCall) {
            $agentExtNumber = $data['request_from_user_id'];
        }

        // Transferred call will have destination agent's EXT number exactly in TO property
        if ($isTransferredCall) {
            $agentExtNumber = $data['to']['number'];
        }

        return $agentExtNumber;
    }

    // Get agent ext number from make call api event
    static function getAgentExtNumberFromMakeCallApiEvent(array $from, array $to) {
        return $to['number'];
    }

    // Get customer phone number from make call api event
    static function getCustomerPhoneNumberFromMakeCallApiEvent(array $from, array $to) {
        return end(explode('->', html_entity_decode($from['alias'])));
    }

    // Get the right call direction from the request
    static function getCallDirection($data) {
        if (!empty($data['recording_url'])) {
            return PBXManager_Data_Model::getCallDirection($data['call_id']);
        }

        return $data['callCreatedReason'] == 'EXTERNAL_CALL_IN' ? 'inbound' : 'outbound';
    }

    // Return recording data only to prevent user to access file url out side the system
    public function getRecordingData($callRecordModel) {
        $accessToken = $this->getAccessToken('rest_api');
        $callId = $callRecordModel->get('sourceuuid');
        $callId = str_replace('_transferred', '', $callId); // Remove suffix for transferred call
        $playbackUrl = "{$this->restServiceUrl}call/play/{$callId}?access_token={$accessToken}";
        $recordingData = getRemoteFile($playbackUrl);
        
        return $recordingData;
    }

    // Return the key to reach next IVR node for incomming call
    static function getIvrRouting($customerNumber, $hotline) {
        $customerNumber = str_replace('btncall_', '', $customerNumber);
        $agentExt = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);

        if (!empty($agentExt)) {
            $connector = self::getInstance();
            $agentStatus = $connector->checkAgentStatus($agentExt);
            
            if ($agentStatus == 'FREE') {
                $response = ['key' => '1']; // Go to Routing Queue
            }
            else {
                $response = ['key' => '2']; // Go to Main Node
            }
        }
        else {
            $response = ['key' => '2']; // Go to Main Node
        }

        return $response;
    }

    // Return the agent ext number based on the incomming customer number
    static function getExtRouting(array $calls) {
        $response = ['version' => 2, 'calls' => []];

        foreach ($calls as $call) {
            $customerNumber = str_replace('btncall_', '', $call['from']);
            $hotline = $call['to'];
            $agentExt = PBXManager_Data_Model::getRoutingByCustomerNumber($customerNumber, $hotline);

            $response['calls'][] = [
                'callId' => $call['callId'],
                'agents' => [
                    [
                        'stringee_user_id' => $agentExt,
                        'phone_number' => $hotline,
                        'routing_type' => 1,    // 1: client, 2: mobile
                        'answer_timeout' => 60
                    ]
                ]
            ];
        }
        
        return $response;
    }
    
    static function isExists($callId, $status) {
        $statusMapping = [
            'ringing' => 'ringing',
            'answered' => 'answered',
            'ended' => 'hangup',
            'recorded' => 'completed',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    static function getEndCallEventCacheFile(array $data, bool $isTransferredCall = false) {
        $agentUserId = '';

        // For resetting cache content when call starts
        if (in_array($data['call_status'], ['started', 'ringing'])) {
            $agentExtNumber = PBXManager_Stringee_Connector::getAgentExtNumberFromRingingEvent($data, $isTransferredCall);
            $agent = PBXManager_Data_Model::findAgentByExtNumber($agentExtNumber);
            if (!empty($agent)) $agentUserId = $agent['id'];
        }
        // For saving cache data when call ends
        else {
            $callId = $data['call_id'];

            if ($isTransferredCall && strpos($data['call_id'], '_transferred') === false) {
                $callId = $data['call_id'] . '_transferred';
            }

            $agentUserId = PBXManager_Data_Model::getAgentUserIdFromCall($callId);
        }

        return "cache/{$agentUserId}_EndCallEvent.json";
    }

    static function writeEndCallEventCache(array $data, bool $isTransferredCall = false) {
        $cacheFile = self::getEndCallEventCacheFile($data, $isTransferredCall);
        $cacheData = self::loadEndCallEventCache($cacheFile);
        $newData = $data;

        if (!empty($cacheData)) {
            $newData = array_merge_recursive($cacheData, $data);
        }

        file_put_contents($cacheFile, json_encode($newData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    static function loadEndCallEventCache($cacheFile) {
        if (!file_exists($cacheFile)) return [];
        $cacheContent = file_get_contents($cacheFile);
        $cacheData = json_decode($cacheContent, true) ?? [];
        return $cacheData;
    }

    static function resetEndCallEventCache($cacheFile) {
        if (!file_exists($cacheFile)) return;
        file_put_contents($cacheFile, '');
    }

    // Handle all call events from webhook
    static function handleCallEvent(array $data, bool $isTransferredCall = false) {
        // New call
        if ($data['state'] == 'ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['from']['number'], $data['to']['number'], $data['direction']);
            $hotline = $data['direction'] == 'inbound' ? $data['to']['alias'] : $data['from']['number'];

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $data['call_id'],
                'gateway' => 'Stringee',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => $hotline,
                'assigned_user_id' => $agent['id'],
            ];

            if (strpos($customerPhoneNumber, 'btncall_') === 0) {
                $params['customernumber'] = str_replace('btncall_', '', $params['customernumber']);
                $params['description'] = 'Call from Free Call Button at public website!';
            }
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call answered
        if ($data['state'] == 'answered') {
            $params = [
                'callstatus' => 'answered'
            ];

            PBXManager_Data_Model::handleHangupCall($data['call_id'], $params);
        }

        // Call ended or recorded
        if ($data['state'] == 'ended' || $data['state'] == 'recorded') {
            $cacheFile = self::getEndCallEventCacheFile($data, $isTransferredCall);
            $cacheData = self::loadEndCallEventCache($cacheFile);

            // Call ended
            if ($data['state'] == 'ended') {
                if (PBXManager_Data_Model::getCallStatus($data['call_id']) != 'completed') {
                    $params['callstatus'] = 'hangup';
                }
                
                $params['endtime'] = date('Y-m-d H:i:s');
                $params['totalduration'] = $data['duration'];
                $params['billduration'] = $data['answerDuration'];

                // Call status should be completed when the recording_url is available
                if (!empty($cacheData['recording_url'])) {
                    $params['callstatus'] = 'completed';
                    $params['recordingurl'] = $cacheData['recording_url'];
                    $params['endtime'] = date('Y-m-d H:i:s', $cacheData['end_time'] / 1000);
                }

                PBXManager_Data_Model::updateCall($data['call_id'], $params, false);
            }
            
            // Call recorded
            if ($data['state'] == 'recorded') {
                $params['callstatus'] = 'completed';
                $params['recordingurl'] = $data['recording_url'];
                $params['endtime'] = date('Y-m-d H:i:s', $data['end_time'] / 1000);

                // When CDR event arrive, get durations in cache file if they are available
                if (!empty($cacheData['duration'])) {
                    $params['totalduration'] = $cacheData['duration'];
                    $params['billduration'] = $cacheData['answerDuration'];
                }

                PBXManager_Data_Model::updateCall($data['call_id'], $params, false);
            }
        }
    }

    // Implemented by Phu Vo on 2020.05.09
    static function handleMissedCallEvent(array $data) {
        $customerPhoneNumber = $data['from']['number'];
        $callId = $data['call_id'];
        $callTime = date('Y-m-d H:i:s');
        $hotlineNumber = $data['to']['alias'];
        
        try {
            if (strpos($customerPhoneNumber, 'btncall_') === 0) {
                $customerPhoneNumber = str_replace('btncall_', '', $customerPhoneNumber);
            }
            
            $customer = PBXManager_Data_Model::findCustomerByPhoneNumber($customerPhoneNumber);
    
            // Retrieve missed call alert users on config
            $userIds = CallCenterUtils::getMissedCallAlertUsers($customer);
    
            // Mark as hang up
            PBXManager_Data_Model::updateCallStatus($callId, ['callstatus' => 'hangup']);
    
            // Save Missed Call Events
            CallCenterUtils::saveMissedCallLog($customer, $customerPhoneNumber, $callId, $callTime, $userIds);
    
            // Send push notification
            CallCenterUtils::sendMissedCallNotification($customer, $customerPhoneNumber, $userIds);
    
            // Send alert email
            CallCenterUtils::sendMissedAlertEmail($customer, $customerPhoneNumber, $hotlineNumber, $callTime, $userIds);
        }
        catch (Exception $e) {
            CallCenterUtils::saveDebugLog('[PBXManager_Stringee_Connector::handleMissedCallEvent] Error: '. $e->getMessage(), null, $e->getTrace());
        }
    }
}