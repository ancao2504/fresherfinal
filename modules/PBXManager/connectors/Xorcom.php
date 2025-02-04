<?php

/*
    Xorcom_Connector
    Author: Hieu Nguyen
    Date: 2021-05-17
    Purpose: to handle communication with Xorcom Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_Xorcom_Connector extends PBXManager_Base_Connector {

    public $isPhysicalDevice = true;    // Indicate that this is a physical device, not cloud call center
    public $hasDirectPlayRecordingApi = false;   // Indicate that this provider provides an api to play recording directly from call log
    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'ami_server_ip' => 'text',
        'ami_port' => 'text',
        'ami_username' => 'text',
        'ami_password' => 'password'
    ];
    protected $amiServerIP;
    protected $amiPort;
    protected $amiUsername;
    protected $amiPassword;

    // Return the connector name
    public function getGatewayName() {
        return 'Xorcom';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->amiServerIP = $serverModel->get('ami_server_ip');
        $this->amiPort = $serverModel->get('ami_port');
        $this->amiUsername = $serverModel->get('ami_username');
        $this->amiPassword = $serverModel->get('ami_password');
    }

    // Make a phone call
    function makeCall($receiverNumber, $parentId) {
        $user = Users_Record_Model::getCurrentUserModel();
        $agentExt = $user->get('phone_crm_extension');
        
        $socket = fsockopen($this->amiServerIP, $this->amiPort, $errCode, $errMsg, 10);
        fputs($socket, "Action: Login\r\n");
        fputs($socket, "UserName: {$this->amiUsername}\r\n");
        fputs($socket, "Secret: {$this->amiPassword}\r\n\r\n");

        $response = fgets($socket, 128);

        fputs($socket, "Action: Originate\r\n" );
        fputs($socket, "Channel: SIP/$agentExt\r\n" );
        fputs($socket, "Context: from-internal\r\n" );
        fputs($socket, "Exten: {$receiverNumber}\r\n" );
        fputs($socket, "Callerid: CRM->{$receiverNumber}\r\n" );
        fputs($socket, "Priority: 1\r\n" );
        fputs($socket, "Async: yes\r\n" );
        fputs($socket, "Timeout: 60000\r\n\r\n" );
        fputs($socket, "Action: Logoff\r\n\r\n");

        while (!feof($socket)) {
            $response .= fread($socket, 8192);
        }

        fclose($socket);
        CallCenterUtils::saveDebugLog("[Xorcom] Make call request: ", null, null, $response);

        if (strpos($response, 'Originate successfully queued') > 0) {
            return ['success' => true];
        }

        return ['success' => false];
    }

    // Get the right call direction from the request
    function getCallDirection($data) {
        if ($data['state'] == 'Hangup') {
            return PBXManager_Data_Model::getCallDirection($data['callid']);
        }

        return $data['type'];
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'Ringing' => 'ringing',
            'Up' => 'answered',
            'Hangup' => 'completed'
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Process event data from Xorcom call center
    static function processEventData($eventData) {
        $data = [
            'callid' => $eventData['Uniqueid'],
            'direction' => 'inbound',
            'state' => $eventData['ChannelStateDesc'],
        ];

        if ($eventData['ChannelStateDesc'] == 'Ring') {
            $data['state'] = 'Ringing';
        }

        if ($eventData['Event'] == 'Hangup') {
            $data['state'] = 'Hangup';
        }

        // All outbound ringing event has empty ConnectedLineNum
        if (empty($eventData['ConnectedLineNum'])) {
            $data['direction'] = 'outbound';
        }

        if ($data['direction'] == 'inbound' && $data['state'] == 'Ringing') {
            $data['state'] = 'Ringing';
            $data['caller'] = $eventData['ConnectedLineNum'];
            $data['callee'] = $eventData['CallerIDNum'];
        }
        else if ($data['direction'] == 'outbound' && $data['state'] == 'Ringing') {
            $calleeNumber = str_replace('CRM->', '', decodeUTF8($eventData['ConnectedLineName']));  // Make call using softphone will has this info empty

            $data['state'] = 'Ringing';
            $data['caller'] = $eventData['CallerIDNum'];
            $data['callee'] = $calleeNumber;
        }

        return $data;
    }

    // Handle all call events from webhook
    static function handleCallEvent($data) {
        // New call
        if ($data['state'] == 'Ringing') {
            $customerPhoneNumber = CallCenterUtils::getCustomerPhoneNumber($data['caller'], $data['callee'], $data['direction']);

            // Get prefetch data from global
            $agent = $GLOBALS['agent'];
            $customer = $GLOBALS['customer'];

            $params = [
                'direction' => $data['direction'],
                'callstatus' => 'ringing',
                'starttime' => date('Y-m-d H:i:s'),
                'sourceuuid' => $data['callid'],
                'gateway' => 'Xorcom',
                'user' => $agent['id'],
                'customer' => $customer['id'],
                'customernumber' => $customerPhoneNumber,
                'customertype' => $customer['type'],
                'hotline' => '',
                'assigned_user_id' => $agent['id'],
            ];
            
            PBXManager_Data_Model::handleStartupCall($params);
        }

        // Call answered
        if ($data['state'] == 'Up') {
            $params = [
                'callstatus' => 'answered'
            ];

            // When make call using softphone, customer number will exist in answered event
            if (!empty($data['customer'])) {
                $params['customer'] = $data['customer']['id'];
                $params['customertype'] = $data['customer']['type'];
                $params['customernumber'] = $data['customer_number'];
                PBXManager_Data_Model::updateCall($data['callid'], $params);
            }
            // Update call status
            else {
                PBXManager_Data_Model::updateCallStatus($data['callid'], $params);
            }
        }

        // Call hangup
        if ($data['state'] == 'Hangup') {
            $callData = PBXManager_Data_Model::getCallData($data['callid']);
            $startTime = $callData['starttime'];
            $endTime = date('Y-m-d H:i:s');
            $duration = strtotime($endTime) - strtotime($startTime);

            $params = [
                'callstatus' => 'completed',
                'endtime' => $endTime,
                'totalduration' => $duration,
                'billduration' => $duration,
            ];

            PBXManager_Data_Model::handleHangupCall($data['callid'], $params);
        }
    }
}