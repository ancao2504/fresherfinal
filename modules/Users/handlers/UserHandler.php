<?php

/*
	Class UserHandler
	Author: Hieu Nguyen
	Date: 2019-11-08
*/

class UserHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if($entityData->getModuleName() != 'Users') return;
		
		if($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
			$this->setNewUserDefaultPreferences($entityData);
		}

		if($eventName === 'vtiger.entity.aftersave') {
			$this->insertSharedCustomViewsForNewUser($entityData);
			$this->applyDashboardFromDashboardTemplate($entityData);
			$this->massUpdateRecordsDepartment($entityData);    // Added by Hieu Nguyen on 2020-09-25

			// Added by Phu Vo on 2020.05.01 to handle default notification configuration
			$this->setNewUserNotificationConfiguration($entityData);
			// End Phu Vo

			// Added by Phu Vo on 2021.03.24 to update user cookies
			$this->updateUserCookies($entityData);
			// End Phu Vo
		}

		if($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}

	private function setNewUserDefaultPreferences($entityData) {
		global $defaultUserPreferences;

		if ($entityData->isNew()) {
			$entityData->set('language', $defaultUserPreferences['language']);
			$entityData->set('time_zone', $defaultUserPreferences['timezone']);
			$entityData->set('date_format', $defaultUserPreferences['date_format']);
			$entityData->set('currency_id', $defaultUserPreferences['currency_id']);
			$entityData->set('currency_symbol_placement', $defaultUserPreferences['currency_symbol_placement']);
			$entityData->set('shared_calendar_activity_types', 'Call,Meeting'); // For Shared Calendar
			$entityData->set('default_call_reminder_time', '{"days":"0", "hours":"0", "mins":"30"}');
			$entityData->set('default_meeting_reminder_time', '{"days":"1", "hours":"0", "mins":"0"}');
			$entityData->set('default_task_reminder_time', '{"days":"1", "hours":"0", "mins":"0"}');
		}
	}

	private function insertSharedCustomViewsForNewUser($entityData) {
		global $adb;
		if (!$entityData->isNew()) return;
		$sql = "INSERT INTO vtiger_cv2users(cvid, userid)
			SELECT cv.cvid, ?
			FROM vtiger_customview AS cv
			WHERE shared_type = 'all_users'";
		$adb->pquery($sql, [$entityData->getId()]);
	}

	private function applyDashboardFromDashboardTemplate($entityData) {
		if (isForbiddenFeature('DashboardManagement')) return;

		try {
			$userId = $entityData->getId();
			$roleId = $entityData->get('roleid');

			if ($entityData->isNew()) {
				// Apply dashboard template to new user
				Home_DashboardLogic_Helper::applyTemplateToSpecificUser($userId, $roleId);
			}
			else {
				$vtEntityDelta = new VTEntityDelta();
				$delta = $vtEntityDelta->getEntityDelta('Users', $entityData->getId());

				// Apply dashboard template to a user when his role is changed
				if (isset($delta['roleid']) && $delta['roleid']['oldValue'] != $delta['roleid']['currentValue']) {
					Home_DashboardLogic_Helper::applyTemplateToSpecificUser($userId, $roleId);
				}
			}
		}
		catch (Exception $ex) {
			$err = $ex->getMessage();   // To debug
		}
	}

	// Implemented by Hieu Nguyen on 2020-09-25 to mass update records department as selected user department
	private function massUpdateRecordsDepartment($entityData) {
		global $adb;
		$vtEntityDelta = new VTEntityDelta();
		$delta = $vtEntityDelta->getEntityDelta('Users', $entityData->getId());

		// Do nothing when the user is newly created or the user department field is not changed
		if ($entityData->isNew() || !isset($delta['users_department']) || $delta['users_department']['oldValue'] == $delta['users_department']['currentValue']) {
			return;
		}

		$ownerId = $entityData->getId();
		$recordDepartment = $entityData->get('users_department');

		// Find records assigned to selected user
		$sqlGetAffectedModules = "SELECT DISTINCT setype AS module_name FROM vtiger_crmentity WHERE deleted = 0 AND main_owner_id = ?";
		$params = [$ownerId];
		$affectedModulesResult = $adb->pquery($sqlGetAffectedModules, $params);

		// Set selected user's department as record department in all affected modules if records are assigned to the selected user as main owner
		while ($affectedModule = $adb->fetchByAssoc($affectedModulesResult)) {
			$moduleName = $affectedModule['module_name'];
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			if (!$moduleModel->getField('users_department')) continue;
			
			$tableName = $moduleModel->basetable;
			$primaryKey = $moduleModel->basetableid;

			$sqlUpdateRecordsDepartment = "UPDATE {$tableName}
				INNER JOIN vtiger_crmentity ON (crmid = {$primaryKey} AND setype = ? AND deleted = 0)
				SET users_department = ?
				WHERE main_owner_id = ?";
			$params = [$moduleName, $recordDepartment, $ownerId];
			$adb->pquery($sqlUpdateRecordsDepartment, $params);
		}
	}

	// Added by Phu Vo on 2020.05.01 to handle default notification configuration
	private function setNewUserNotificationConfiguration($entityData) {
		global $adb;

		if ($entityData->isNew()) {
			$defaultConfig = [
				'receive_notifications' => '1',
				'receive_notifications_method' => ['popup', 'app'],
				'receive_assignment_notifications' => '1',
				'receive_record_update_notifications' => '1',
				'receive_following_record_update_notifications' => '1',
				'show_activity_reminders' => '1',
				'show_customer_birthday_reminders' => '1'
			];

			$query = "INSERT INTO vtiger_user_preferences VALUES (? , 'notification_config', '" . json_encode($defaultConfig) . "')";
			$queryParams = [$entityData->getId()];

			$adb->pquery($query, $queryParams);
		}
	}

	/** Added by Phu Vo on 2021.03.24  to handle update user cookies */
	private function updateUserCookies($entityData) {
		// Update browser login language
		$_SESSION['login_language'] = $entityData->get('language');
		setcookie('login_language', $entityData->get('language'));
	}
}