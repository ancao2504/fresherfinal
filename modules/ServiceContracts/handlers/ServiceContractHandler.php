<?php

/*
*	ServiceContractsHandler.php
*	Author: Phuc Lu
*	Date: 2020.03.05
*   Purpose: provide handler events for ServiceContracts
*/

class ServiceContractHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'ServiceContracts') return;

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
		// if (CPMauticIntegration_Config_Helper::hasConfig('Contacts') && !empty($entityData->get('sc_related_to'))) {
		// 	$record = Vtiger_Record_Model::getInstanceById($entityData->get('sc_related_to'));

		// 	if ($record->getModule()->name == 'Contacts') {
		// 		CPMauticIntegration_Data_Helper::updateContactStageSegmentByStatus('ServiceContracts', $entityData, $entityData->get('sc_related_to'));
		// 	}
		// }
	}
	// Ended by Phuc
}

