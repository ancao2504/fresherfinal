<?php

/*
*	DocumentsBatchHandle.php
*	Author: Hieu Nguyen
*	Date: 2020-06-26
*   Purpose: provide batch handler function for module Documents
*/

class DocumentsBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if($entityDataList[0]->getModuleName() != 'Documents') return;

		if($eventName === 'vtiger.batchevent.save') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.afterdelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.beforerestore') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.afterrestore') {
			// Add handler functions here
		}
	}

    // Handle process_records event
    static function processRecords(&$recordModel) {
        $fileName = $recordModel->getRaw('filename');
        $fileDetails = $recordModel->getFileDetails();
        
        if (!empty($fileDetails)) {
            $downloadUrl = 'index.php?module=Documents&action=DownloadFile&record='. $recordModel->getId() .'&fileid='. $fileDetails['attachmentsid'];
            $fileName = '<a target="_blank" href="'. $downloadUrl .'">'. $fileName .'</a>';
        }
        else {
            if (strpos($fileName, 'http') === 0 || strpos($fileName, 'www') === 0) {
                $fileName = '<a target="_blank" href="'. $fileName .'">'. $fileName .'</a>';
            }
        }

        $recordModel->set('filename', $fileName);
	}
}