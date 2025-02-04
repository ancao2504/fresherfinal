<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Users_SaveCalendarSettings_Action extends Users_Save_Action {


	public function process(Vtiger_Request $request) {
        // Added by Hieu Nguyen on 2019-11-21 to save shared calendar activity types filter
        $request->set('shared_calendar_activity_types', join(',', $request->get('shared_calendar_activity_types')));
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2020-02-20 to save default email reminder time for Event and Task
        $defaultCallReminderTime = [
            'days' => $request->get('call_remdays'),
            'hours' => $request->get('call_remhrs'),
            'mins' => $request->get('call_remmin'),
        ];

        $defaultMeetingReminderTime = [
            'days' => $request->get('meeting_remdays'),
            'hours' => $request->get('meeting_remhrs'),
            'mins' => $request->get('meeting_remmin'),
        ];

        $defaultTaskReminderTime = [
            'days' => $request->get('task_remdays'),
            'hours' => $request->get('task_remhrs'),
            'mins' => $request->get('task_remmin'),
        ];

        $request->set('default_call_reminder_time', json_encode($defaultCallReminderTime));
        $request->set('default_meeting_reminder_time', json_encode($defaultMeetingReminderTime));
        $request->set('default_task_reminder_time', json_encode($defaultTaskReminderTime));
        // End Hieu Nguyen

        // Added by Hieu Nguyen on 2022-01-18 to save advanced options
        $request->set('auto_fill_customer_address_into_activity_location', $request->get('auto_fill_customer_address_into_activity_location') == 'on' ? 1 : 0);
        // End Hieu Nguyen

		$recordModel = $this->getRecordModelFromRequest($request);
		
		$recordModel->save();
		$this->saveCalendarSharing($request);
		header("Location: index.php?module=Calendar&view=Calendar");
	}

	/**
	 * Function to update Calendar Sharing information
	 * @params - Vtiger_Request $request
	 */
    // Modified by Hieu Nguyen on 2020-02-26 to boost calendar settings performance
	public function saveCalendarSharing(Vtiger_Request $request) {
        global $current_user;
		$sharedType = $request->get('sharedtype');
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');

        // Delete all previous shared users to cleanup the db
        $calendarModuleModel->deleteSharedUsers($current_user->id);

        // Save selected users from select2
        if ($sharedType == 'selectedusers') {
            $selectedUserIdsString = $request->get('selected_users');
            
			if (!empty($selectedUserIdsString)) {
                $selectedUserIds = Vtiger_CustomOwnerField_Helper::getOwnerIdsFromRequest($selectedUserIdsString);
				$calendarModuleModel->insertSharedUsers($current_user->id, $selectedUserIds);
			}
		}
	}
}
