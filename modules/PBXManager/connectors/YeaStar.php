<?php

/*
    YeaStar_Connector
    Author: Hieu Nguyen
    Date: 2019-06-27
    Purpose: to handle communication with YeaStar Call Center
*/

require_once('modules/PBXManager/BaseConnector.php');
require_once('include/utils/CallCenterUtils.php');

class PBXManager_YeaStar_Connector extends PBXManager_Base_Connector {

    public $isPhysicalDevice = true;    // Indicate that this is a physical device, not cloud call center
    public $hasExternalReport = true;   // Indicate that there is an external report to show the call history
    protected static $SETTINGS_REQUIRED_PARAMETERS = [
        'ami_server_ip' => 'text',
        'ami_port' => 'text',
        'ami_username' => 'text',
        'ami_password' => 'password',
        'api_server_url' => 'text',
        'api_username' => 'text',
        'api_password' => 'password',
    ];
    protected $amiServerIP;
    protected $amiPort;
    protected $amiUsername;
    protected $amiPassword;
    protected $apiServerUrl;
    protected $apiUsername;
    protected $apiPassword;

    // Return the connector name
    public function getGatewayName() {
        return 'YeaStar';
    }

    // Set server parameters for this provider
    public function setServerParameters($serverModel) {
        $this->amiServerIP = $serverModel->get('ami_server_ip');
        $this->amiPort = $serverModel->get('ami_port');
        $this->amiUsername = $serverModel->get('ami_username');
        $this->amiPassword = $serverModel->get('ami_password');
        $this->apiServerUrl = $serverModel->get('api_server_url');
        $this->apiUsername = $serverModel->get('api_username');
        $this->apiPassword = $serverModel->get('api_password');
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
        fputs($socket, "Channel: PJSIP/{$agentExt}\r\n" );
        fputs($socket, "Context: DLPN_DialPlan{$agentExt}\r\n" );
        fputs($socket, "CallerID: {$agentExt} -> {$receiverNumber}\r\n" );
        fputs($socket, "Exten: {$receiverNumber}\r\n" );
        fputs($socket, "Priority: 1\r\n" );
        fputs($socket, "Async: yes\r\n" );
        fputs($socket, "Timeout: 60000\r\n\r\n" );
        fputs($socket, "Action: Logoff\r\n\r\n");

        while (!feof($socket)) {
            $response .= fread($socket, 8192);
        }

        fclose($socket);
        CallCenterUtils::saveDebugLog('[YeaStar] Make call request: ', null, null, $response);

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
        $token = $this->getToken();

        // Prepare params
        $params['token'] = $token;
        $params['random'] = $this->getRandom('cdr', $token, $params);

        // Get CDR report
        $serviceUrl = $this->apiServerUrl . 'cdr/download';
        $serviceUrl .= '?'. urldecode(http_build_query($params));
        $serviceUrl = str_replace(' ', '%20', $serviceUrl); // This provider needs a raw url with space encoded only
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'GET', [], false, false);

        // Handle CSV format from the response
        if ($response) {
            return $this->getDataFromCSV($response);
        }

        return null;
    }

    // Return recording data only to prevent user to access file url out side the system
    function getRecordingDataByFileName($fileName) {
        $token = $this->getToken();

        // Prepare params
        $params = [
            'recording' => $fileName,
            'token' => $token,
            'random' => $this->getRandom('recording', $token, ['recording' => $fileName]),
        ];

        // Get CDR report
        $serviceUrl = $this->apiServerUrl . 'recording/download';
        $serviceUrl .= '?'. http_build_query($params);
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'GET', [], false, false);

        return $response;
    }

    // Get array data from CSV format
    function getDataFromCSV($csvString) {
        $data = [];

        $csv = str_getcsv($csvString, "\n");
        $header = str_getcsv($csv[0]);
        array_shift($csv);

        foreach($csv as &$line) {
            $data[] = array_combine($header, str_getcsv($line, ','));
        }

        return $data;
    }

    // Get token from REST api
    function getToken() {
        $serviceUrl = $this->apiServerUrl . 'login';
        $params = [
            'username' => $this->apiUsername,
            'password' => md5($this->apiPassword),
            'port' => '8260',
            'version' => '1.0.2',
        ];

        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'POST', $params);

        if ($response && $response->status == 'Success') {
            return $response->token;
        }

        return null;
    }

    // Get random token for cdr and recording. Type = cdr/recording
    function getRandom($type, $token, $params) {
        $serviceUrl = $this->apiServerUrl . $type .'/get_random';
        $serviceUrl .= '?token='. $token;
        $client = $this->getRestClient($serviceUrl);
        $response = $this->callRestApi($client, 'POST', $params);

        if ($response && $response->status == 'Success') {
            return $response->random;
        }

        return null;
    }

    static function isExists($callId, $status) {
        $statusMapping = [
            'Ringing' => 'ringing',
            'Up' => 'answered',
            'Hangup' => 'hangup',
            'CDR' => 'completed',
        ];

        return PBXManager_Data_Model::isExists($callId, $statusMapping[$status]);
    }

    // Process event data from YeaStar call center
    static function processEventData($eventData) {
        $data = [
            'callid' => $eventData['Linkedid'],
            'direction' => 'inbound',
            'state' => $eventData['ChannelStateDesc'],
        ];

        if ($eventData['UserField'] == 'Outbound' || in_array($eventData['Exten'], ['h', '1-dial']) || ($eventData['Event'] == 'Newstate' && strpos($eventData['Context'], 'callin_trunk') !== false)) {
            $data['direction'] = 'outbound';
        }

        if ($data['direction'] == 'inbound' && $eventData['Event'] == 'DialBegin') {
            $data['state'] = 'Ringing';
            $data['callid'] = $eventData['DestLinkedid'];
            $data['caller'] = $eventData['CallerIDNum'];
            $data['callee'] = $eventData['DestCallerIDNum'];
        }
        else if ($data['direction'] == 'outbound' && $eventData['Event'] == 'DialBegin') {
            preg_match('/\d+/', $eventData['Channel'], $caller);

            $data['state'] = 'Ringing';
            $data['callid'] = $eventData['DestLinkedid'];
            $data['caller'] = $caller[0];
            $data['callee'] = $eventData['DestCallerIDNum'];
        }

        if ($eventData['Event'] == 'Hangup') {
            $data['state'] = 'Hangup';
        }

        if ($eventData['Event'] == 'Cdr') {
            $data['callid'] = $eventData['LinkedID'];
            $data['state'] = 'CDR';
            $data['duration'] = $eventData['Duration'];
            $data['billsecs'] = $eventData['BillableSeconds'];
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
                'gateway' => 'YeaStar',
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

            PBXManager_Data_Model::updateCall($data['callid'], $params);
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