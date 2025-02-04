<?php

/*
	GlobalEventHandler
	Author: Hieu Nguyen
	Date: 2020-09-28
	Purpose: to handle events that occur in all entity modules
*/

class GlobalEventHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() == 'Users') return;
		
		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			$this->saveRecordDepartment($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}

	// Handle logic when a new relation between source record and related record has been added
	function relationLinked($relationModel, $sourceModuleName, $sourceRecordId, $relatedModuleName, $relatedRecordId) {
		global $adb;

		// Remove converted Targets from MKT List
		if ((
			$sourceModuleName == 'CPTargetList' && $relatedModuleName == 'CPTarget') ||
			($relatedModuleName == 'CPTarget' && $relatedModuleName = 'CPTargetList')
		) {
			$mktListId = ($sourceModuleName == 'CPTargetList') ? $sourceRecordId : $relatedRecordId;

			$sql = "DELETE vtiger_crmentityrel FROM vtiger_crmentityrel INNER JOIN vtiger_cptarget ON (crmid = ? AND relcrmid = cptargetid) WHERE cptarget_status = 'Converted'";
			$adb->pquery($sql, [$mktListId]);

			$sql = "DELETE vtiger_crmentityrel FROM vtiger_crmentityrel INNER JOIN vtiger_cptarget ON (relcrmid = ? AND crmid = cptargetid) WHERE cptarget_status = 'Converted'";
			$adb->pquery($sql, [$mktListId]);
		}

		// Remove converted Leads from MKT List
		if ((
			$sourceModuleName == 'CPTargetList' && $relatedModuleName == 'Leads') ||
			($relatedModuleName == 'Leads' && $relatedModuleName = 'CPTargetList')
		) {
			$mktListId = ($sourceModuleName == 'CPTargetList') ? $sourceRecordId : $relatedRecordId;

			$sql = "DELETE vtiger_crmentityrel FROM vtiger_crmentityrel INNER JOIN vtiger_leaddetails ON (crmid = ? AND relcrmid = leadid) WHERE leadstatus = 'Converted'";
			$adb->pquery($sql, [$mktListId]);

			$sql = "DELETE vtiger_crmentityrel FROM vtiger_crmentityrel INNER JOIN vtiger_leaddetails ON (relcrmid = ? AND crmid = leadid) WHERE leadstatus = 'Converted'";
			$adb->pquery($sql, [$mktListId]);
		}
	}

	// Handle logic when a existing relation between source record and related record has been removed
	function relationUnlinked($relationModel, $sourceModuleName, $sourceRecordId, $relatedModuleName, $relatedRecordId) {
		// Logic here
	}

	// Save record department every time the record is saved
	private function saveRecordDepartment(&$entityData) {
		global $adb;
		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'Events') $moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if (!$moduleModel->getField('users_department')) return;

		// Process
		$recordId = $entityData->getId();
		$mainOwnerId = $entityData->get('main_owner_id');
		$recordDepartment = '';

		if ($mainOwnerId != -1) {
			// In case event was triggered by vtws apis
			if (strpos($mainOwnerId, 'x') !== false) {
				$mainOwnerId = end(explode('x', $mainOwnerId));
			}

			$vtEntityDelta = new VTEntityDelta();
			$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId);

			// Do nothing when the record is edited but the main owner is not changed
			if (!$entityData->isNew() && !isset($delta['main_owner_id']) && $delta['main_owner_id']['oldValue'] == $delta['main_owner_id']['currentValue']) {
				return;
			}

			$userModel = Users_Record_Model::getInstanceById($mainOwnerId, 'Users');
			$recordDepartment = decodeUTF8($userModel->get('users_department'));
		}

		$tableName = $moduleModel->basetable;
		$primaryKey = $moduleModel->basetableid;

		$sql = "UPDATE {$tableName} SET users_department = ? WHERE {$primaryKey} = ?";
		$adb->pquery($sql, [$recordDepartment, $recordId]);
	}
}