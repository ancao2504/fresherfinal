<?php

/*
*	ContactBatchHandle.php
*	Author: Phuc Lu
*	Date: 2019.07.29
*   Purpose: provide batch handler function for module Contacts
*/

class ContactBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if($entityDataList[0]->getModuleName() != 'Contacts') return;

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
	}
}