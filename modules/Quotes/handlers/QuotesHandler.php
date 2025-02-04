<?php

/*
*	QuotesHandler.php
*	Author: Phuc Lu
*	Date: 2020.03.05
*   Purpose: provide handler events for quotes
*/

class QuotesHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'Quotes') return;

		if ($eventName === 'vtiger.entity.beforesave') {
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			$this->updateMauticContactStage($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
		}

		if ($eventName === 'vtiger.entity.afterdelete') {	
		}
	}

	private function updateMauticContactStage($entityData) {
		// Commented out to disable unused logic by Hieu Nguyen on 2021-11-02
		// if (CPMauticIntegration_Config_Helper::hasConfig('Contacts') && !empty($entityData->get('contact_id'))) {
		// 	CPMauticIntegration_Data_Helper::updateContactStageSegmentByStatus('Quotes', $entityData, $entityData->get('contact_id'));
		// }
	}
	// Ended by Phuc
}

