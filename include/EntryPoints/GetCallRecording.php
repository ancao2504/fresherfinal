<?php

require_once('include/Webservice/SalesAppApiHandler.php');

session_start();

// Begin: Allow CORS requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']}");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit;
}
// End: Allow CORS requests

class GetCallRecording extends Vtiger_EntryPoint {

    function process (Vtiger_Request $request) {
        $token = $request->get('token');
        $pbxCallId = $request->get('pbx_call_id');
        $recordingData = null;
        $fileName = null;

        SalesAppApiHandler::checkSession($token);

        // Validate input request
        if (empty($pbxCallId)) exit;

        // Retrieve Serverinfo
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if (empty($connector)) exit;

        if (method_exists($connector, 'getRecordingData')) {
            $pbxRecordModel = Vtiger_Record_Model::getInstanceByConditions('PBXManager', ['sourceuuid' => $pbxCallId]);

            if (empty($pbxRecordModel)) exit;

            $recordingData = $connector->getRecordingData($pbxRecordModel);
            $fileName = $pbxRecordModel->get('recordingurl');
            $fileName = basename($fileName);
        }
        else {
            exit;
        }

        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="'. $fileName .'.mp3"');
        
        echo $recordingData;

        exit;
    }
}