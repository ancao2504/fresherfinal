<?php

/*
*	LeadBatchHandler.php
*	Author: Phuc Lu
*	Date: 2019.07.29
*   Purpose: provide batch handler function for module Leads
*/

class LeadBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if($entityDataList[0]->getModuleName() != 'Leads') return;

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
		$leadConvertFields = [
			'account_converted_id',
			'contact_converted_id',
			'potential_converted_id',
		];
		
		foreach ($leadConvertFields as $fieldName) {
			if (!empty($recordModel->getRaw($fieldName))) {
				$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $recordModel->getModule());
				$displayValue = $fieldModel->getDisplayValue($recordModel->getRaw($fieldName), $recordModel);
				$recordModel->set($fieldName, $displayValue);
			}
		}
	}
}