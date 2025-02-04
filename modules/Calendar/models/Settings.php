<?php

/*
	Settings_Model
	Author: Hieu Nguyen
	Date: 2020-02-21
	Purpose: to handle data manipulating for calendar settings
*/

class Calendar_Settings_Model {

    static function getUserSettings($userId = '') {
        $settings = [];
        $userModel = null;
        
        if (!empty($userId)) {
            $userModel = Users_Record_Model::getInstanceById($userId);
        }
        else {
            $userModel = Users_Record_Model::getCurrentUserModel();
        }

        $emptyUserModel = Vtiger_Record_Model::getCleanInstance('Users');   // Quick hack to fix bug current user data always exist in Users_EditRecordStructure_Model that causes new user create form full of current user data
		$userRecordStructure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($emptyUserModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$recordStructure = $userRecordStructure->getStructure();

        foreach ($recordStructure['LBL_CALENDAR_SETTINGS'] as $fieldName => $fieldModel) {
            $settings[$fieldName] = $userModel->get($fieldName);
        }

        $settings['default_call_reminder_time'] = json_decode(html_entity_decode(($settings['default_call_reminder_time'])), true);
        $settings['default_meeting_reminder_time'] = json_decode(html_entity_decode(($settings['default_meeting_reminder_time'])), true);
        $settings['default_task_reminder_time'] = json_decode(html_entity_decode(($settings['default_task_reminder_time'])), true);

        $sharedType = Calendar_Module_Model::getSharedType($userModel->id);
        $settings['sharedtype'] = $sharedType;

        if ($sharedType == 'selectedusers') {
            $settings['shared_users'] = Calendar_Module_Model::getCaledarSharedUsers($userModel->id);
        }
		
        return $settings;
    }
}