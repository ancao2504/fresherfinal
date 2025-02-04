<?php

/*
	CallLog_Model
	Author: Hieu Nguyen
	Date: 2020-02-18
	Purpose: to handle data manipulating for customer's call log
*/

class PBXManager_CallLog_Model {

	// For planned calls
	static function canMakeCall($customerId, $plannedCallId) {
		global $current_user;
		$plannedCallModel = Vtiger_Record_Model::getInstanceById($plannedCallId, 'Calendar');

		if ($plannedCallModel->get('activitytype') != 'Call') return false;
		if ($plannedCallModel->get('eventstatus') != 'Planned') return false;
		if ($plannedCallModel->get('events_call_direction') != 'Outbound') return false;
		if ($plannedCallModel->get('main_owner_id') != $current_user->id) return false;
		if (empty(getCustomerPhoneNumbers($customerId))) return false;

		// Added by Phu Vo on 2020.07.27 to disable make call button on auto call record
		if ($plannedCallModel->get('is_auto_call') == 1) return false;
		// End Phu Vo

		return true;
	}

	static function getCallInfoToMakeCall($customerId, $plannedCallId) {
		$plannedCallModel = Vtiger_Record_Model::getInstanceById($plannedCallId, 'Calendar');
		
		$callInfo = [];
		$callInfo['activity_id'] = $plannedCallId;
		$callInfo['customer_id'] = $customerId;
		$callInfo['customer_name'] = $plannedCallModel->getField('parent_id')->getEditViewDisplayValue($customerId);
		$callInfo['phone_numbers'] = getCustomerPhoneNumbers($customerId);
		
		return $callInfo;
	}

	// For held calls
	static function canPlayRecording($callLogId) {
		$callLogModel = Vtiger_Record_Model::getInstanceById($callLogId, 'Calendar');

		if ($callLogModel->get('activitytype') != 'Call') return false;
		if ($callLogModel->get('eventstatus') != 'Held') return false;
		if (empty($callLogModel->get('pbx_call_id'))) return false;

		$serverModel = PBXManager_Server_Model::getInstance();
		$connector = $serverModel->getConnector();
		if (!$connector->hasDirectPlayRecordingApi) return false;    // This provider allows to play recording in external report only

		try {
			$pbxCallModel = Vtiger_Record_Model::getInstanceByConditions('PBXManager', ['sourceuuid' => $callLogModel->get('pbx_call_id')]);
			if (empty($pbxCallModel) || empty($pbxCallModel->get('recordingurl'))) return false;    // No recording file
		}
		catch (Exception $e) {
			return false;
		}

		return true;
	}
}