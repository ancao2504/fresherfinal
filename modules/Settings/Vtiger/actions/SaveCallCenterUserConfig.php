<?php

/*
	File: SaveCallCenterUserConfig.php
	Author: PhuVo
	Date: 2019.07.30
	Purpose: CallCenter config ajax handler
*/

class Settings_Vtiger_SaveCallCenterUserConfig_Action extends Settings_Vtiger_Basic_Action {

	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}

	function process(Vtiger_Request $request) {
		global $current_user;

		$moduleName = $request->getModule(false);
		$requestData = $request->getAll();

		// Fetch useful data
		$currentUserRecordModel = Users_Record_Model::getCurrentUserModel();
		$settings = $requestData['settings'] ?? [];
		$callCenterUserConfig = $settings['vendor'] ?? [];

		// Process upload file
		$customRingtoneFileName = "upload/webphone_ringtone/ringtone_{$current_user->id}";

		if (!empty($_FILES['custom_ringtone'] && $_FILES['custom_ringtone']['size'] > 0 && !$requestData['ringtone_removed'])) {
			$customRingtone = $_FILES['custom_ringtone'];
			$callCenterUserConfig['custom_ringtone'] = $customRingtone['name'];

			// Validate uploaded file process
			$uploadedFileSizeInKb = $customRingtone['size'] / 1024;
			if ($uploadedFileSizeInKb > 1024) {
				$replaceParams = [
					'%max_size' => '1M',
				];

				$errorMessage = vtranslate('LBL_CALLCENTER_USER_CONFIG_UPLOAD_FILE_SIZE_TOO_LARGE_ERROR_MSG', $moduleName, $replaceParams);

				$response = new Vtiger_Response();
				$response->setError(400, $errorMessage);
				$response->emit();
			}

			// Check folder and save uploaded file
			if (!file_exists('upload/webphone_ringtone')) {
				mkdir('upload/webphone_ringtone', '0777');
			}

			move_uploaded_file($customRingtone['tmp_name'], $customRingtoneFileName);
		}

		// Handle delete file logic
		if ($requestData['ringtone_removed']) {
			unset($callCenterUserConfig['custom_ringtone']);
			unlink($customRingtoneFileName);
		}

		// Handle save general config
		if (isset($settings['general'])) {
			$generalSettings = $settings['general'] ?? [];

			if (isset($generalSettings['phone_crm_extension'])) {
				$currentUserRecordModel->set('phone_crm_extension', $generalSettings['phone_crm_extension']);
				$currentUserRecordModel->set('mode', 'edit');
				$currentUserRecordModel->save();
			}
		}

		Users_Preferences_Model::savePreferences($current_user->id, 'callcenter_config', $callCenterUserConfig);

		// Respond
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}