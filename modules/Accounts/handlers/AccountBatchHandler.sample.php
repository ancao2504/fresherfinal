<?php

/*
*	SampleBatchHandler structure
*	Author: Hieu Nguyen
*	Date: 2018-07-19
*	Usage: Copy this file into a new file named AccountBatchHandler.php to handle events for Account. 
		After that add a function to handle the corresponding event to handle the business logic.
		Do that the same for other modules (DON'T FORGET to rename the file name and class name to match with that module. Ex: ContactBatchHandler)
	Note: REMOVE this comment on the file you create youself to avoid messing comments
*/

class AccountBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if ($entityDataList[0]->getModuleName() != 'Accounts') return;

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
		// $recordModel->set('accountname', 'Foo bar');
	}
}