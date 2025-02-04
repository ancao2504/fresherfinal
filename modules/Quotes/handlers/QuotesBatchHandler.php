<?php

/*
*	QuotesBatchHandler.php
*	Author: Phuc Lu
*	Date: 2020.03.05
*   Purpose: provide batch handler function for module Quotes
*/

class QuotesBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if ($entityDataList[0]->getModuleName() != 'Quotes') return;

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
	}
}