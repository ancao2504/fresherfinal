<?php

/*
    PBXManagerBatchHandler.php
    Author: Hieu Nguyen
    Date: 2020-09-08
    Purpose: handle batch events for PBXManager
*/

class PBXManagerBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if ($entityDataList[0]->getModuleName() != 'PBXManager') return;

		if ($eventName === 'vtiger.batchevent.save') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.afterdelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.beforerestore') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.afterrestore') {
			// Add handler functions here
		}
	}

    // Handle process_records event
    static function processRecords(&$recordModel) {
        $connector = PBXManager_Server_Model::getActiveConnector();

        // Display recording field as HTML5 player
        if ($connector && $connector->hasDirectPlayRecordingApi) {
            $recordingPlayer = '<audio controls="controls" preload="none" style="width: 200px; height: 35px"><source src="index.php?module=PBXManager&action=GetRecording&record='. $recordModel->getId() .'" type="audio/mp3"></audio>';
            $recordModel->set('recordingurl', $recordingPlayer);
        }
        else {
            $recordModel->set('recordingurl', 'N/A');
        }
	}
}