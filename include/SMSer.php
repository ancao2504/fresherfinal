<?php

class SMSer {
	
	/*
	*   Function send()
	*   Author: Hieu Nguyen
	*   Date: 2018-12-03
	*   Functions: send sms to specific number with message from the given template
	*   Params structure:
	*       + $toNumber: phone number of the receiver
	*       + $templateId: id of the SMS Template
	*       + $variables = array('name' => 'Hieu Nguyen', 'email' => 'hieu.nguyen@onlinecrm.vn', 'phone' => '0984147940', ...)
	*       + $relatedRecordId: id of the related record
	*       + $relatedModuleName: name of the related module
	*       + $senderId: id of the sender user. Admin will be the sender if nothing given
	*/

	static function send($toNumber, $templateId, $variables = [], $relatedRecordId, $relatedModuleName, $senderId = '') {
		global $current_user;
		$GLOBALS['sms_ott_channel'] = 'SMS';

		if ($current_user == null) {
			$current_user = Users::getRootAdminUser();    // Bypass permission check
		}

		// Check sender id
		if (empty($senderId)) {
			$senderId = $current_user->id;
		}

		// Get message content from template
		$smsTemplateRecord = Vtiger_Record_Model::getInstanceById($templateId, 'CPSMSTemplate');
		$message = html_entity_decode($smsTemplateRecord->get('description'));
		
		// Replace defined variables in the content
		if (!empty($variables)) {
			foreach ($variables as $key => $value) {
				$message = str_replace("%{$key}%", $value, $message);
			}
		}

		try {
			$result = SMSNotifier_Record_Model::SendSMS($message, [$toNumber => $relatedRecordId], $senderId, [$relatedRecordId], $relatedModuleName);
			return $result;
		}
		catch (Exception $ex) {
			return false;
		}
	}
}