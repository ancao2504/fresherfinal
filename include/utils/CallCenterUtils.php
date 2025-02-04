<?php

/*
	Class CallCenterUtils
	Author: Hieu Nguyen
	Date: 2018-10-03
	Purpose: To provide util functions for handling logic with call center
*/

require_once('include/utils/WebhookUtils.php');

class CallCenterUtils extends WebhookUtils {

	static $logger = 'CALLCENTER';

	static function getCustomerPhoneNumber($callerNumber, $receiverNumber, $direction) {
		if (strtoupper($direction) == 'INBOUND') {
			return $callerNumber;
		}

		return $receiverNumber;
	}

	static function getAgentExtNumber($callerNumber, $receiverNumber, $direction) {
		if (strtoupper($direction) == 'INBOUND') {
			return $receiverNumber;
		}

		return $callerNumber;
	}

	static function checkConfig() {
		checkAccessForbiddenFeature('CallCenterIntegration');

		$gatewayName = str_replace('Connector', '', $_REQUEST['name']);
		$activeProviderInstance = PBXManager_Server_Model::getActiveConnector();

		if (empty($activeProviderInstance) || $gatewayName != $activeProviderInstance->getGatewayName()) {
			die('This provider is not enabled yet!');
		}

		return $activeProviderInstance;
	}

	static function fillMsgDataForRingingEvent(&$msg, $customerPhoneNumber, $customerInfo) {
		$msg['customer_number'] = $customerPhoneNumber;
		$msg['customer_id'] = $customerInfo['id'];
		$msg['customer_name'] = $customerInfo['name'];
		$msg['customer_type'] = $customerInfo['type'];
		$msg['customer_avatar'] = $customerInfo['avatar'];
		$msg['account_id'] = $customerInfo['account_id'];
		$msg['account_name'] = $customerInfo['account_name'];
		$msg['assigned_user_id'] = $customerInfo['assigned_user_id'];
		$msg['assigned_user_name'] = $customerInfo['assigned_user_name'];
		$msg['assigned_user_ext'] = $customerInfo['assigned_user_ext'];

		if (!empty($customerInfo['call_log_id'])) {
			$msg['call_log_id'] = $customerInfo['call_log_id'];
		}
	}

	/**
	 * Helper method to get alert receiver list
	 * @author Phu Vo (2019.08.01)
	 */
	static function getMissedCallAlertUsers($customerInfo) {
		// Processing begin
		$assignedUserId = false;
		$callCenterConfig = Settings_Vtiger_Config_Model::loadConfig('callcenter_config');

		// Process in case we couldn't find any customer in our system
		if (empty($customerInfo['id'])) {
			// Retrieve if any user was selected to receive missed call from new customer
			$assignedUserId = $callCenterConfig->new_customer_missed_call_alert;
		}
		else {
			// Get assigned user from customer info
			$assignedUserId = $customerInfo['assigned_user_id']; // Notice that it is main owner instead of assigned_user_id and it could empty in case assign to group
			$customer = Vtiger_Record_Model::getInstanceById($customerInfo['id'], $customerInfo['type']);
		}

		// In case we can't find any matched customer and any new customer missed call receiver => return
		if (empty($customerInfo) && empty($assignedUserId)) return [];

		// It empty because we have a customer assigned to group
		if (empty($assignedUserId)) {
			$withoutMainOwnerReceiver = $callCenterConfig->existing_customer_missed_call_alert_no_main_owner;

			// If we can't find customer without main owner receiver => return
			if (empty($withoutMainOwnerReceiver)) return [];

			// We have two options for now
			if ($withoutMainOwnerReceiver === 'specific_user') {
				// Send Alert to specific user
				$assignedUserId = $callCenterConfig->missed_call_alert_no_main_owner_specific_user;
			}
			elseif ($withoutMainOwnerReceiver === 'group_members') {
				// Send Notification to all users in assigned group
				$assignedUserId = $customer ? $customer->get('assigned_user_id') : ''; // Now we retrieve real assigned user id (groupid)
			}
		}

		$userIds = [];
		$ownerType = vtws_getOwnerType($assignedUserId);

		if ($ownerType === 'Users') {
			$userIds = [$assignedUserId];
		}
		elseif ($ownerType === 'Groups') {
			$userIds = getGroupMemberIds($assignedUserId) ?? [];
		}

		return $userIds;
	}

	/**
	 * Helper method to send missed call alert notification
	 * @author Phu Vo (2019.07.19)
	 */
	static function sendMissedCallNotification($customerInfo, $customerNumber, $userIds) {
		require_once('include/utils/NotificationHelper.php');
		require_once('modules/Users/models/Preferences.php');

		// Stop when we can
		if (empty($userIds)) return;

		// Check if it is a new customer or not
		if (empty($customerInfo)) $newCustomer = true;
		
		foreach ($userIds as $userId) {
			// Check assigned User notification config to decide send notification or not
			$userNotificationConfig = Users_Preferences_Model::loadPreferences($userId, 'notification_config');
	
			if ($userNotificationConfig != null && $userNotificationConfig->receive_notifications == 1) {
				// Peform send notification action
				$userLanguage = getUserData('language', $userId);
				$userTimezone = getUserData('time_zone', $userId);
	
				$extraData = [
					'action' => 'missed_call',
					'new_customer' => $newCustomer,
					'number' => $customerNumber,
				];
	
				$data = [
					'receiver_id' => $userId,
					'type' => 'notification',
					'related_record_id' => $customerInfo['id'],
					'related_record_name' => $customerInfo['name'],
					'related_module_name' => $customerInfo['type'],
					'extra_data' => $extraData,
				];
	
				$data['message'] = translateNotificationMessage($data, $userLanguage, $userTimezone);
	
				NotificationHelper::sendNotification($data);
			}
		}
	}

	/**
	 * Helper method to send missed call alert mail
	 * @author Phu Vo (2019.07.19)
	 * INSERT INTO `vtiger_emailtemplates` (`foldername`, `templatename`, `templatepath`, `subject`, `description`, `body`, `deleted`, `templateid`, `systemtemplate`, `module`) VALUES (NULL, 'Missed call alert', NULL, 'Missed call from %customer_name (%call_time)', '', '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\r\n<html>\r\n<head>\r\n	<title></title>\r\n</head>\r\n<body class=\"scayt-enabled\">K&iacute;nh gửi %username,<br />\r\n&nbsp;<br />\r\nHệ thống đ&atilde; ghi nhận bạn c&oacute; một cuộc gọi nhỡ từ %customer_type_vn_vn <strong>%customer_name</strong>.<br />\r\n&nbsp;\r\n<table style=\"text-align: left; width: 100%; border-collapse: collapse\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc; width: 20%\">Hotline</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%hotline</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Thời gian</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%call_time</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Email</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%email</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Địa chỉ</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%address</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">C&ocirc;ng ty</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%company_name</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n<br />\r\nClick v&agrave;o đường dẫn n&agrave;y để xem th&ocirc;ng tin chi tiết kh&aacute;ch h&agrave;ng: <a href=\"%link\">%customer_type_vn_vn: %customer_name</a><br />\r\n<br />\r\nTr&acirc;n trọng cảm ơn.<br />\r\n<br />\r\n<span style=\"color:#B22222;\">------------ </span><br />\r\n<span style=\"color:#B22222;\">Email n&agrave;y được gửi tự động từ hệ thống CRM. Vui l&ograve;ng kh&ocirc;ng phản hồi!</span><br />\r\n&nbsp;\r\n<hr /><br />\r\nDear %username,<br />\r\n<br />\r\nWe recognize that you have a missed call from %customer_type_en_us <strong>%customer_name</strong>.<br />\r\n&nbsp;\r\n<table style=\"text-align: left; width: 100%; border-collapse: collapse\">\r\n	<tbody>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc; width: 20%\">Hotline</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%hotline</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Time</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%call_time</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Email</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%email</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Address</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%address</td>\r\n		</tr>\r\n		<tr>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">Company name</td>\r\n			<td style=\"padding: 3px; border: 1px solid #ccc\">%company_name</td>\r\n		</tr>\r\n	</tbody>\r\n</table>\r\n<br />\r\nClick to the follow link to see customer details: <a href=\"%link\">%customer_type_en_us: %customer_name</a><br />\r\n<br />\r\nThanks and Regards.<br />\r\n<br />\r\n<span style=\"color:#B22222;\">------------ </span><br />\r\n<span style=\"color:#B22222;\">This email is sent automatically from CRM system. Please do not reply!</span></body>\r\n</html>\r\n', '0', '17', '0', 'Contacts');
	 */
	static function sendMissedAlertEmail($customerInfo, $customerNumber, $hotline, $callTime, array $userIds) {
		require_once('include/Mailer.php');

		$callCenterConfig = Settings_Vtiger_Config_Model::loadConfig('callcenter_config');

		// Email template id
		$templateId = $callCenterConfig->missed_call_alert_email_template;

		// If we don't have template id to send email, just return
		if (empty($templateId)) return;

		// Stop when we can
		if (empty($userIds)) return;

		// Init some useful data
		$newCustomer = false;
		$groupName = false;
		$userName = false;

		if (!empty($customerInfo['id'])) {
			$customer = Vtiger_Record_Model::getInstanceById($customerInfo['id']);
		}

		// Check if it is a new customer or not to process message
		if (empty($customer)) $newCustomer = true;
		
		if (count($userIds) > 1) { // It mean customer is assign to a group
			$groupName = trim(getOwnerName($customer->get('assigned_user_id')));
		}
		else {
			$userName = trim(getOwnerName($userIds[0]));
		}

		// Main email receivers
		$mainReceivers = [];

		foreach ($userIds as $userId) {
			$user = CRMEntity::getInstance('Users');
			$user->retrieve_entity_info($userId, 'Users');

			if (!empty($user->email1)) $mainReceivers[] = [
				'name' => decodeUTF8(trim(getOwnerName($user->id))), 
				'email' => $user->email1
			];
		}

		// When main receivers empty, also return
		if (empty($mainReceivers)) return;

		// Init date time
		$dateTime = new DateTimeField($callTime);

		// Link to details
		$customerTypeLabel = getTranslatedString('SINGLE_' . $customerInfo['type'], $customerInfo['type'], 'vn_vn');

		$receiverName = !empty($groupName) ? $groupName : $userName;

		// Email variables
		$variables = [
			'username' => $receiverName,
			'customer_type_en_us' => $newCustomer ? getTranslatedString('LBL_PHONE_NUMBER', 'Vtiger', 'en_us') : getTranslatedString('SINGLE_' . $customerInfo['type'], $customerInfo['type'], 'en_us'),
			'customer_type_vn_vn' => $newCustomer ? getTranslatedString('LBL_PHONE_NUMBER', 'Vtiger', 'vn_vn') : $customerTypeLabel,
			'customer_name' => $newCustomer ? $customerNumber : html_entity_decode(trim($customerInfo['name'])),
			'hotline' => $hotline,
			'call_time' => !empty($groupName) ? $dateTime->getDisplayDateTimeValue(CRMEntity::getInstance('Users')) : $dateTime->getDisplayDateTimeValue($user),
		];

		// Process customer specific infomation base on type
		if (empty($customerInfo['id'])) {
			$variables['email'] = '';
			$variables['address'] = '';
			$variables['company_name'] = '';
		}
		elseif ($customerInfo['type'] === 'Contacts') {
			$variables['email'] = $customer->get('email');
			$variables['address'] = $customer->get('mailingstreet');
			$variables['company_name'] = getAccountName($customer->get('account_id'));
		}
		else if ($customerInfo['type'] === 'Leads') {
			$variables['email'] = $customer->get('email');
			$variables['address'] = $customer->get('lane');
			$variables['company_name'] = $customer->get('company');
		}

		// Trigger send email action
		$result = Mailer::send(true, $mainReceivers, $templateId, $variables);
	}

	static function saveDebugLog($description, $headers = null, $input = null, $response = null) {
		global $callCenterConfig;

		if ($callCenterConfig['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}

	/**
	 * Implement by Phu Vo on 2020.02.20 to process save missed call log
	 */
	static function saveMissedCallLog(array $customerInfo, $customerNumber, $callId, $callTime, array $userIds) {
		// Init date time
		list($startDate, $startTime) = explode(' ', $callTime);

		// Make it duration 60s
		$endDateTime = date('Y-m-d H:i:s', strtotime($callTime) + 60);
		list($endDate, $endTime) = explode(' ', $endDateTime);

		if (!empty($customerInfo['id'])) {
			$customer = Vtiger_Record_Model::getInstanceById($customerInfo['id']);
		}

		// Create event
		$callLog = Vtiger_Record_Model::getCleanInstance('Events');
		$callLog->set('activitytype', 'Call');
		$callLog->set('eventstatus', 'Held');
		$callLog->set('events_call_direction', 'Inbound');
		$callLog->set('missed_call', 1);
		$callLog->set('pbx_call_id', $callId);
		$callLog->set('visibility', 'Public');
		$callLog->set('date_start', DateTimeField::convertToUserFormat($startDate));
		$callLog->set('time_start', $startTime);
		$callLog->set('due_date', DateTimeField::convertToUserFormat($endDate));
		$callLog->set('time_end', $endTime);

		// Requirements for dashboard
		$callLog->set('events_call_result', ' ');
		$callLog->set('events_call_purpose', ' ');
		$callLog->set('events_inbound_call_purpose', ' ');

		// Generate customer related infomations
		if (!empty($customer)) {
			setActivityRelatedCustomerId($callLog, $customer->getId(), $customer->getModuleName());
			$callLog->set('assigned_user_id', $customer->get('assigned_user_id'));
			$callLog->set('main_owner_id', $customer->get('main_owner_id'));
		}
		else if (count($userIds) === 1) {
			$callLog->set('assigned_user_id', $userIds[0]);
			$callLog->set('main_owner_id', $userIds[0]);
		}

		// Convert to user display value
		$assignedUserModel = Users_Record_Model::getCurrentUserModel();

		if (!empty($callLog->get('main_owner_id'))) {
			$assignedUserModel = Users_Record_Model::getInstanceById($callLog->get('main_owner_id'), 'Users');
		}
		
		$date = new DateTimeField($callTime);
		$dateTimeValue = $date->getDisplayDateTimeValue($assignedUserModel);
		list($startDate, $startTime) = explode(' ', $dateTimeValue);
		if ($assignedUserModel->get('hour_format') == '12') $startTime = Vtiger_Time_UIType::getTimeValueInAMorPM($startTime);

		$formatedStartDateTime = "$startDate $startTime";
		
		// Genereate missed call name
		if (!empty($customer)) {
			$replaceParams = [
				'%customer_type' => vtranslate('SINGLE_' . $customerInfo['type'], $customerInfo['type']),
				'%customer_name' => trim($customerInfo['name']),
				'%date_time' => $formatedStartDateTime,
			];
			$subject = vtranslate('LBL_MISSED_CALL_FROM_CUSTOMER_TITLE', 'PBXManager', $replaceParams);
		}
		else {
			$replaceParams = [
				'%customer_number' => $customerNumber,
				'%date_time' => $formatedStartDateTime,
			];
			$subject = vtranslate('LBL_MISSED_CALL_FROM_NEW_CUSTOMER_TITLE', 'PBXManager', $replaceParams);
		}

		// Assign subject (with handled time)
		$callLog->set('subject', $subject);

		$callLog->save();
	}
}