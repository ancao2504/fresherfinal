<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ***********************************************************************************/

require_once 'include/events/VTEventHandler.inc';
require_once 'modules/Users/models/Preferences.php';
class FollowRecordHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($eventName == 'vtiger.entity.aftersave') {
			global $site_URL;
			$db = PearDatabase::getInstance();

			//current user details
			$currentUserModel = Users_Record_Model::getCurrentUserModel();
			$currentUserId = $currentUserModel->getId();

			//record details
			$recordId = $entityData->getId();
			$moduleName = $entityData->getModuleName();

			$restrictedModules = array('CustomerPortal', 'Dashboard', 'Emails', 'EmailTemplates', 'ExtensionStore', 'Google', 'Home',
										'Import', 'MailManager', 'Mobile', 'ModComments', 'ModTracker', 'PBXManager', 'Portal',
										'RecycleBin', 'Reports', 'Rss', 'SMSNotifier', 'Users', 'Webforms', 'Webmails', 'WSAPP');

			if (!in_array($moduleName, $restrictedModules)) {
				$tableName = Vtiger_Functions::getUserSpecificTableName($moduleName);

				//following users
				$userIdsList = array();
				$result = $db->pquery("SELECT userid FROM $tableName WHERE recordid = ? AND starred = ? AND userid != ?", array($recordId, '1', $currentUserId));
				if ($result && $db->num_rows($result)) {
					while ($rowData = $db->fetch_row($result)) {
						$userIdsList[] = $rowData['userid'];
					}
				}

				if ($userIdsList) {
					//changed fields data
					$vtEntityDelta = new VTEntityDelta();
					$delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

					if ($delta) {
						$newEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
						$label = decode_html(trim($newEntity->get('label')));

						$fieldModels = array();
						$changedValues = array();
						$skipFields = array('modifiedtime', 'modifiedby', 'label');
						$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

						foreach ($delta as $fieldName => $fieldInfo) {
							if (!in_array($fieldName, $skipFields)) {
								$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
								if ($fieldModel) {
									$fieldModels[$fieldName] = $fieldModel;
									$changedValues[$fieldName] = $fieldInfo;
								}
							}
						}

						if ($fieldModels) {
							// Modified by Phu Vo on 2019.11.07 to Send email and notifications for following user (in the loop)
							require_once('include/Mailer.php');

							// Prepair some useful variables
							$templateId = getSystemEmailTemplateByName('Following Record Update Notify');
							$userModuleModel = Users_Module_Model::getInstance('Users');
							$detailViewLink = "$site_URL/index.php?module=$moduleName&view=Detail&record=$recordId";
							$recordDetailViewLink = '<a style="text-decoration:none;" target="_blank" href="'.$detailViewLink.'">'.$label.'</a>';

							foreach ($userIdsList as $userId) {
								$userNotificationConfig = Users_Preferences_Model::loadPreferences($userId, 'notification_config');
								if ($userNotificationConfig != null && $userNotificationConfig->receive_following_record_update_notifications == 1) continue;

								$userRecordModel = Users_Record_Model::getInstanceById($userId, $userModuleModel);

								if ($userRecordModel && $userRecordModel->get('status') == 'Active') {

									// Genereate changed fields html
									$recordUpdateDetailsVn = $this->getRecordUpdateDetails($fieldModels, $changedValues, $userRecordModel, 'vn_vn');
									$recordUpdateDetailsEn = $this->getRecordUpdateDetails($fieldModels, $changedValues, $userRecordModel, 'en_us');

									$modifiedTime = $entityData->get('modifiedtime');
									$modifiedTimeField = new DateTimeField($modifiedTime);

									$mainReceivers = [
										['name' => getUserFullName($userId), 'email' => $userRecordModel->get('email1')]
									];
									
									$variables = [
										'user_fullname' => getUserFullName($userId),
										'module_name_vn' => vtranslate('SINGLE_' . $moduleName, $moduleName, 'vn_vn'),
										'module_name_en' => vtranslate('SINGLE_' . $moduleName, $moduleName, 'en_us'),
										'record_name' => $label,
										'updater_name' => getUserFullName($currentUserId),
										'record_link' => $recordDetailViewLink,
										'record_updated_details_vn' => $recordUpdateDetailsVn,
										'record_updated_details_en' => $recordUpdateDetailsEn,
										'modified_time' => $modifiedTimeField->getDisplayDateTimeValue($userRecordModel),
									];
									
									Mailer::send(true, $mainReceivers, $templateId, $variables);
									
                                    $this->sendNotification($entityData, $changedValues , $userId);
								}
							}
							// End Phu Vo
						}
					}
				}
			}
		}
	}

	// Implemented by Phu Vo on 2019.11.07 to generate change values html
	private function getRecordUpdateDetails($fieldModels, $changedValues, $userRecordModel, $language) {
		$changedFieldString = '<table style="text-align: left; width: 100%; border-collapse: collapse"><tbody>';
		$userEntity = $userRecordModel->entity;

		// Cache and Change language that user a using
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$cacheLanguage = $currentUserModel->get('language');
		$currentUserModel->set('language', $language);
		
		foreach ($fieldModels as $fieldName => $fieldModel) {
			$fieldDisplayValue = '';
			$moduleName = $fieldModel->getModule()->getName();
			$fieldCurrentValue = $changedValues[$fieldName]['currentValue'];

			if ($fieldModel->isReferenceField()) {
				$fieldDisplayValue = Vtiger_Util_Helper::getRecordName($fieldCurrentValue);
			}
			else if ($fieldModel->isOwnerField()) {
				$fieldDisplayValue = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($fieldCurrentValue, false);
			}
			else if ($fieldModel->get('uitype') == 117 && $fieldCurrentValue) {
				$fieldDisplayValue = getCurrencyName($fieldCurrentValue, FALSE);
			}
			else {
				$fieldDataType = $fieldModel->getFieldDataType();
				switch ($fieldDataType) {
					case 'boolean' :
					case 'multipicklist' :
						$fieldDisplayValue = $fieldModel->getDisplayValue($fieldCurrentValue);
						break;
					case 'picklist' :
						$fieldDisplayValue = getTranslatedString($fieldCurrentValue, $moduleName, $language);
						break;
					case 'date' :
						$fieldDisplayValue = DateTimeField::convertToUserFormat($fieldCurrentValue, $userEntity);
						break;
					case 'double' :
						$fieldDisplayValue = CurrencyField::convertToUserFormat(decimalFormat($fieldCurrentValue), $userEntity, true);
						break;
					case 'time' :
						if ($userRecordModel->get('hour_format') == '12') {
							$fieldDisplayValue = Vtiger_Time_UIType::getTimeValueInAMorPM($fieldCurrentValue);
						}
						else {
							$fieldDisplayValue = $fieldModel->getEditViewDisplayValue($fieldCurrentValue);
						}
						break;
					case 'currency' :
						$skipConversion = false;
						if ($fieldModel->get('uitype') == 72) $skipConversion = true;
						$fieldDisplayValue = CurrencyField::convertToUserFormat($fieldCurrentValue, $userEntity, $skipConversion);
						break;
					default:
						$fieldDisplayValue = $fieldModel->getEditViewDisplayValue($fieldCurrentValue);
						break;
				}
			}

			$fieldLabel = vtranslate($fieldModel->get('label'), $moduleName, $language);
			
			$changedFieldString .= '<tr>';
			$changedFieldString .= "<td style='padding: 3px; border: 1px solid #ccc; width: 20%'><strong>" . $fieldLabel . "</strong></td>";
			$changedFieldString .= "<td style='border: 1px solid #ccc; padding: 3px;'>{$fieldDisplayValue}</td>";
			$changedFieldString .= '</tr>';
		}

		$changedFieldString .= '</tbody></table>';

		// Revert language that user are using before return result
		$currentUserModel->set('language', $cacheLanguage);

		return $changedFieldString;
	}

	public function getChangedFieldString($fieldModels, $changedValues, $userRecordModel) {
		$userEntity = $userRecordModel->entity;

		$changedFieldString = '';
		foreach ($fieldModels as $fieldName => $fieldModel) {
			$moduleName = $fieldModel->getModule()->getName();
			$fieldCurrentValue = $changedValues[$fieldName]['currentValue'];

			if ($fieldModel->isReferenceField()) {
				$fieldDisplayValue = Vtiger_Util_Helper::getRecordName($fieldCurrentValue);
			} else if ($fieldModel->isOwnerField()) {
				$fieldDisplayValue = getOwnerName($fieldCurrentValue);
			} else if ($fieldModel->get('uitype') == 117 && $fieldCurrentValue) {
				$fieldDisplayValue = getCurrencyName($fieldCurrentValue, FALSE);
			} else {
				$fieldDataType = $fieldModel->getFieldDataType();
				switch ($fieldDataType) {
					case 'boolean'		:
					case 'multipicklist':	$fieldDisplayValue = $fieldModel->getDisplayValue($fieldCurrentValue);break;
					case 'date'			:	$fieldDisplayValue = DateTimeField::convertToUserFormat($fieldCurrentValue, $userEntity);break;
					case 'double'		:	$fieldDisplayValue = CurrencyField::convertToUserFormat(decimalFormat($fieldCurrentValue), $userEntity, true);break;
					case 'time'			:	if ($userRecordModel->get('hour_format') == '12') {
												$fieldDisplayValue = Vtiger_Time_UIType::getTimeValueInAMorPM($fieldCurrentValue);
											} else {
												$fieldDisplayValue = $fieldModel->getEditViewDisplayValue($fieldCurrentValue);
											}
											break;
					case 'currency'		:	$skipConversion = false;
											if ($fieldModel->get('uitype') == 72) {
												$skipConversion = true;
											}
											$fieldDisplayValue = CurrencyField::convertToUserFormat($fieldCurrentValue, $userEntity, $skipConversion);
											break;

					default				:	$fieldDisplayValue = $fieldModel->getEditViewDisplayValue($fieldCurrentValue);break;
				}
			}
			$changedFieldString .= '<br/>'.vtranslate('LBL_STARRED_RECORD_TO', $moduleName, vtranslate($fieldModel->get('label'), $moduleName), $fieldDisplayValue);
		}
		return $changedFieldString;
	}

	public function sendEmail($toEmailId, $subject, $body, $recordId) {
		//It will not show in CRM
		$generatedMessageId = Emails_Mailer_Model::generateMessageID();
		Emails_Mailer_Model::updateMessageIdByCrmId($generatedMessageId, $recordId);

		$mailer = new Emails_Mailer_Model();
		$mailer->reinitialize();
		$mailer->Body = $body;
		$mailer->Subject = decode_html($subject);

		$activeUserModel = $this->getActiveUserModel();
		$replyTo = decode_html($activeUserModel->email1);
		$replyToName = decode_html($activeUserModel->first_name.' '.$activeUserModel->last_name);
		$fromEmail = decode_html($activeUserModel->email1);

		$mailer->ConfigSenderInfo($fromEmail, $replyTo, $replyToName);
		$mailer->IsHTML();
		$mailer->AddCustomHeader("In-Reply-To", $generatedMessageId);
		$mailer->AddAddress($toEmailId);

		$response = $mailer->Send(true);
	}

    // Implemented by Phu Vo on 2019.04.03 to send record update notification to followers
    private function sendNotification($entityData, $changedValues , $userId) {
        require_once('include/utils/NotificationHelper.php');

		// Don't send notification when import
		if($entityData->focus->isBulkSaveMode()) return; // Modified by Phu Vo on 2019.09.22 use entity isBulkSaveMode instead
	
		// Don't send notification when mass update
		if(strtoupper($_REQUEST['action']) === 'MASSSAVE') return;

        $assignedUserId = $entityData->get('assigned_user_id');
        
        // Dont send notify if assigned user follow this record
        if($assignedUserId == $userId) return;

        // get user language
        $userLanguage = getUserData('language', $userId);
        $timezone = getUserData('time_zone', $userId);
        if(empty($userLanguage)) $userLanguage = vglobal('default_language');
        
        $recordId = $entityData->getId();
        $moduleName = $entityData->focus->moduleName;
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        if(sizeof($changedValues) > 0) {
            // process first changed field
			$fieldName = array_keys($changedValues)[0];
			
			// Modified by Phu Vo on 2019.07.10 to recorrect due to custom owner field process logic
			$currentValue = $changedValues[$fieldName]['currentValue'];

            // send notification
            $extraData = [
                'action' => 'update',
                'updater' => $entityData->get('modifiedby'),
                'updated_field' => $fieldName,
				'updated_value' => $currentValue,
				'following' => true,
			];
			
			// Translate updated value in custom group case
			if ($fieldName === 'assigned_user_id') {
				$extraData['updated_label'] = Vtiger_Owner_UIType::getCurrentOwnersForDisplay($currentValue, false);
			};
			// End recorrect due to custom owner field process logic

            if($moduleName == 'Calendar') $extraData['activity_type'] = $entityData->get('activitytype');

            $data = [
                'receiver_id' => $userId,
                'type' => 'notification',
                'related_record_id' => $recordId,
                'related_record_name' => $entityData->get('label'),
                'related_module_name' => $moduleName,
                'extra_data' => $extraData,
            ];

            $data['message'] = translateNotificationMessage($data, $userLanguage, $timezone);

            NotificationHelper::sendNotification($data);
        }
    }

	var $activeAdmin = '';
	public function getActiveUserModel() {
		if (!$this->activeAdmin) {
			$activeUserModel = new Users();
			$activeUserModel->retrieveCurrentUserInfoFromFile(Users::getRootAdminId());
			$this->activeAdmin = $activeUserModel;
		}
		return $this->activeAdmin;
	}
}
