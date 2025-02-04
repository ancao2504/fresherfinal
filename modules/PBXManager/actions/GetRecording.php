<?php

/*
	Action GetRecording
	Author: Hieu Nguyen
	Date: 2018-11-01
	Purpose: to get recording file from the call center gateway
*/

class PBXManager_GetRecording_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
        $fileName = $request->get('filename');

        if (!empty($fileName)) {
            $this->getRecordingByFileName($fileName);
            return;
        }

        $filePath = $request->get('filepath');

        if (!empty($filePath)) {
            $this->getRecordingByFilePath($filePath);
            return;
        }

        $callId = $request->get('callid');

        if (!empty($callId)) {
            $this->getRecordingByCallId($callId);
            return;
        }

        $recordId = $request->get('record');

        if (empty($recordId)) {
            return;
        }

        $recordModel = PBXManager_Record_Model::getInstanceById($recordId);
        $recordId = $recordModel->get('pbxmanagerid');

        if (empty($recordId)) {
            return;
        }

        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();
        $recordingData = $connector->getRecordingData($recordModel);
        $fileName = $recordModel->get('recordingurl');
        $fileName = basename($fileName);

        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="'. $fileName .'.mp3"');
        
        echo $recordingData;
    }

    function getRecordingByCallId($callId) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if (!method_exists($connector, 'getRecordingDataByCallId')) {
            die("{$connector->getGatewayName()} has no method to get recording by call ID!");
        }
        
        $recordingData = $connector->getRecordingDataByCallId($callId);
        
        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename=recording.mp3');
        
        echo $recordingData;
    }

    function getRecordingByFileName($fileName) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if (!method_exists($connector, 'getRecordingDataByFileName')) {
            die("{$connector->getGatewayName()} has no method to get recording by file name!");
        }
        
        $recordingData = $connector->getRecordingDataByFileName($fileName);
        $fileName = basename($fileName);

        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="'. $fileName .'"');
        
        echo $recordingData;
    }

    function getRecordingByFilePath($filePath) {
        $serverModel = PBXManager_Server_Model::getInstance();
        $connector = $serverModel->getConnector();

        if (!method_exists($connector, 'getRecordingDataByFilePath')) {
            die("{$connector->getGatewayName()} has no method to get recording by file name!");
        }
        
        $recordingData = $connector->getRecordingDataByFilePath($filePath);
        $filePath = basename($filePath);

        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="'. basename($filePath) .'"');
        
        echo $recordingData;
    }
}