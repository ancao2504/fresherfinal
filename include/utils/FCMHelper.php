<?php

/*
	FCMHelper
	Author: Hieu Nguyen
	Date: 2019-03-20
	Purpose: handle sending notification using Firebase Cloud Messaging service
*/

class FCMHelper {

	static function sendNotification($title, $mesage, $userId, $data = null, $forPortalUser = false) {
		global $googleConfig;

		if (empty($userId)) {
			return false;
		}

		if ($forPortalUser) {
			$clientTokens = CustomerPortal_Notification_Helper::getFcmTokens($userId);
		}
		else {
			$clientTokens = CPNotifications_Data_Model::getClientTokens($userId);
		}

		if (empty($clientTokens)) {
			return false;
		}

		if ($forPortalUser) {
			$badgeCount = CustomerPortal_Notification_Helper::getNotificationCount($userId) + 1;
		}
		else {
			$badgeCount = CPNotifications_Data_Model::getNotificationCount($userId) + 1;
		}

		$params = array(
			'registration_ids' => array_values($clientTokens),  // Convert unordered array into ordered array which is required by Firebase
			'notification' => array(
				'title' => strip_tags($title),
				'body'  => strip_tags($mesage),
				'badge' => $badgeCount,
				'sound' => 'default',
				'show_in_foreground' => true
			),
			'data' => [],
		);

		if ($data) {
			$params['data'] = $data;
		}

		$params['data']['raw_title'] = $title;
		$params['data']['raw_message'] = $mesage;

		// Added by Phu Vo on 2019.12.11 to decode UTF8 before sending anything
		decodeUTF8($params);
		// End Phu Vo

		$headers = array(
			'Authorization: key='. $googleConfig['firebase']['fcm_server_key'],
			'Content-Type: application/json'
		);

		// Send Request To FireBase Server
		$client = curl_init();
		curl_setopt($client, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
		curl_setopt($client, CURLOPT_POST, true);
		curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($client, CURLOPT_TIMEOUT, 30);
		curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
		$result = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			saveLog('NOTIFICATIONS', 'Sent FCM Notification error', ['request' => $params, 'error' => $err]);
		}
		else {
			saveLog('NOTIFICATIONS', 'Sent FCM Notification success', ['request' => $params, 'response' => $result]);
		}

		return $result;
	}
}