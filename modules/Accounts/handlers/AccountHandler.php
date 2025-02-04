<?php

/*
*	Account Handler
*	Author: Vu Mai
*	Date: 2022-10-18
*/

require_once('modules/PBXManager/BaseConnector.php');

class AccountHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $current_user, $callCenterConfig;
		if ($entityData->getModuleName() != 'Accounts') return;
		
		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			// Notify state data changed with type is customer info to client
			if ($entityData->focus->isBulkSaveMode()) return;
			if ($callCenterConfig['enable'] == false) return;

			$recordModel = Vtiger_Record_Model::getInstanceById($entityData->getId(), $entityData->getModuleName());
        	$data = $recordModel->getData();

			// Format data to matching with orther customer module
			$data['info_name'] = $data['accountname'];
			$data['customer_number'] = $data['phone'];
			$data['main_owner_id'] = $data['main_owner_id'];
			$data['assigned_user_name'] = decodeUTF8(getUserName($data['main_owner_id']));
			$data['assigned_user_ext'] = getUserData('phone_crm_extension', $data['main_owner_id']);
			$data['assigned_user_type'] = vtws_getOwnerType($data['main_owner_id']);

			// Notify msg to client 
			$msg = [
				'state' => 'DATA_CHANGED',
				'receiver_id' => $current_user->id,
				'data_type' => 'CUSTOMER_INFO',
				'customer_id' => $entityData->get('id'),
				'extra_data' => $data,
			];
	
			PBXManager_Base_Connector::forwardToCallCenterBridge($msg);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}

	static function relationLinked($relationModel, $sourceRecordId, $relatedRecordId, $relatedModuleName) {
		// Logic here
	}

	static function relationUnlinked($relationModel, $sourceRecordId, $relatedRecordId, $relatedModuleName) {
		// Logic here
	}

	public static function tagsChanged($parentId, $action, $eventInfo) {
		// Notify state data changed with type is linked tag to client
		Vtiger_TagHandler_Helper::handleCustomerTagsChanged($parentId);
	}
}