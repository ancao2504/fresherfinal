<?php

/*
	Class UserBatchHandler
	Author: Hieu Nguyen
	Date: 2019-11-08
*/

class UserBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if($entityDataList[0]->getModuleName() != 'Users') return;

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
		// Display Account Owner indicator
		if ($recordModel->isAccountOwner()) {
			$recordModel->set('user_name', '<i class="fa fa-crown"></i>&nbsp;' . $recordModel->get('user_name'));
		}
	}
}