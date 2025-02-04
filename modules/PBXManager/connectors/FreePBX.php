<?php

/*
    FreePBX_Connector
    Author: Hieu Nguyen
    Date: 2019-06-03
    Purpose: to handle communication with FreePBX based Call Center (like Sangoma)
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_FreePBX_Connector extends PBXManager_Base_Connector {

    public $isPhysicalDevice = true;    // Indicate that this is a physical device, not cloud call center
    public $hasExternalReport = true;   // Indicate that there is an external report to show the call history
    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'device_brand' => 'text',
        'ami_server_ip' => 'text',
        'ami_port' => 'text',
        'ami_username' => 'text',
        'ami_password' => 'password',
        'rest_api_url' => 'text',
        'rest_api_key' => 'password',
    ];
    protected $amiServerIP;
    protected $amiPort;
    protected $amiUsername;
    protected $amiPassword;
    protected $cdrApiServerUrl;
    protected $cdrApiUsername;
    protected $cdrApiPassword;

    // Return the connector name
    public function getGatewayName() {
        return 'FreePBX';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->deviceBrand = $serverModel->get('device_brand');
        $this->amiServerIP = $serverModel->get('ami_server_ip');
        $this->amiPort = $serverModel->get('ami_port');
        $this->amiUsername = $serverModel->get('ami_username');
        $this->amiPassword = $serverModel->get('ami_password');
        $this->restApiUrl = $serverModel->get('rest_api_url');
        $this->restApiKey = $serverModel->get('rest_api_key');
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
        fputs($socket, "Priority: 1\r\n" );
        fputs($socket, "Async: yes\r\n" );
        fputs($socket, "Timeout: 60000\r\n\r\n" );
        fputs($socket, "Action: Logoff\r\n\r\n");

        while (!feof($socket)) {
            $response .= fread($socket, 8192);
        }

        fclose($socket);
        CallCenterUtils::saveDebugLog("[FreePBX - {$this->deviceBrand}] Make call request: ", null, null, $response);

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

    // Fetch history report from vendor system
    function getHistoryReport($params) {
        $serviceUrl = $this->restApiUrl . 'calls/list/' . $params;
        $headers = ["Token: {$this->restApiKey}"];

        $client = $this->getRestClient($serviceUrl, $headers);
        $response = $this->callRestApi($client, 'GET', []);

        return $response;
    }

    // Return alias url (public folder) from the given private file path
    function getRecordingUrl($filePath) {
        $publicFolder = str_replace('admin/api/sangomacrm/rest/', 'recording/', $this->restApiUrl);
        $filePath = str_replace('/var/spool/asterisk/monitor/', '', $filePath);
        $fileUrl = $publicFolder . $filePath;
        
        return $fileUrl;
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'Ringing' => 'ringing',
            'Up' => 'answered',
            'Hangup' => 'hangup'
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Process event data from FreePBX call center
    static function processEventData($eventData) {
        $data = [
            'callid' => $eventData['Linkedid'],
            'direction' => 'inbound',
            'state' => $eventData['ChannelStateDesc'],
        ];

        if ($eventData['Context'] == 'from-pstn') {
            $data['direction'] = 'outbound';
        }

        if ($data['direction'] == 'inbound' && $eventData['ChannelStateDesc'] == 'Ringing') {
            $data['state'] = 'Ringing';
            $data['caller'] = $eventData['ConnectedLineNum'];
            $data['callee'] = $eventData['CallerIDNum'];
        }
        else if ($data['direction'] == 'outbound' && in_array($eventData['ChannelStateDesc'], ['Ring', 'Ringing'])) {
            $data['state'] = 'Ringing';
            $data['caller'] = $eventData['ConnectedLineNum'];
            $data['callee'] = $eventData['Exten'];
        }

        if ($eventData['Event'] == 'Hangup') {
            $data['state'] = 'Hangup';
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
                'gateway' => 'FreePBX',
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

            // Update call status
            PBXManager_Data_Model::updateCallStatus($data['callid'], $params);
        }

        // Call hangup
        if ($data['state'] == 'Hangup') {
            $params = [
                'callstatus' => 'hangup'
            ];

            PBXManager_Data_Model::updateCallStatus($data['callid'], $params);
        }

        // Call ended
        if ($data['state'] == 'CDR') {
            $params = [
                'callstatus' => 'completed',
                'endtime' => date('Y-m-d H:i:s'),
                'totalduration' => $data['duration'],
                'billduration' => $data['billsecs'],
                'recordingurl' => '',   // No data
            ];

            PBXManager_Data_Model::handleHangupCall($data['callid'], $params);
        }
    }
}