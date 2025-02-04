<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_ProcessDuplicates_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$module = $request->getModule();
		$records = $request->get('records');
		if($records) {
			foreach($records as $record) {
				$recordPermission = Users_Privileges_Model::isPermitted($module, 'EditView', $record);
				if(!$recordPermission) {
					throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
				}
			}
		}
	}

	function process (Vtiger_Request $request) {
		global $skipDuplicateCheck;
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->get('records');
		$primaryRecord = $request->get('primaryRecord');
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

		$response = new Vtiger_Response();
		try {
			$skipDuplicateCheckOldValue = $skipDuplicateCheck;
			$skipDuplicateCheck = true;

			$fields = $moduleModel->getFields();
			foreach($fields as $field) {
				$fieldValue = $request->get($field->getName());
				if($field->isEditable()) {
					if($field->uitype == 71) {
						$fieldValue = CurrencyField::convertToUserFormat($fieldValue);
					}
					$primaryRecordModel->set($field->getName(), $fieldValue);
				}
			}

			// Added by Hieu Nguyen on 2021-10-01 to save the selected owner
			$primaryRecordModel->set('assigned_user_id', $request->get('assigned_user_id'));
			$primaryRecordModel->set('main_owner_id', $request->get('main_owner_id'));
			$primaryRecordModel->set('owner_populated', true);
			// End Hieu Nguyen

			$primaryRecordModel->set('mode', 'edit');

			// Modified by Phu Vo on 2020.11.11 to add addition event
			$deleteRecords = array_diff($records, array($primaryRecord));
			$entity = $primaryRecordModel->getEntity();
			$entityData = VTEntityData::fromCRMEntity($entity);
			$entityData->set('fromRecordIds', $deleteRecords);
			$eventManager = new VTEventsManager($entity->db);
			$eventManager->initTriggerCache();
			$eventManager->triggerEvent('vtiger.entity.beforemerge', $entityData);
			
			$primaryRecordModel->save();

			foreach($deleteRecords as $deleteRecord) {
				$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Delete', $deleteRecord);
				if($recordPermission) {
					$primaryRecordModel->transferRelationInfoOfRecords(array($deleteRecord));
					$record = Vtiger_Record_Model::getInstanceById($deleteRecord);
					$record->delete();
				}
			}

			$skipDuplicateCheck = $skipDuplicateCheckOldValue;

			$eventManager->triggerEvent('vtiger.entity.aftermerge', $entityData);
			// End Phu Vo

			$response->setResult(true);
		} catch (DuplicateException $e) {
			$response->setError($e->getMessage(), $e->getDuplicationMessage(), $e->getMessage());
		} catch (Exception $e) {
			$response->setError($e->getMessage());
		}
		$response->emit();
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}
}