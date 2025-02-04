<?php

/*
	EntryPoint PushNotificationTest
	Author: Hieu Nguyen
	Date: 2019-02-20
	Purpose: test sending push notification
*/

class PushNotificationTest extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		require_once('include/utils/FCMHelper.php');
		global $googleConfig;

		$defaultPayload = '
		{
			"message": "Cuộc gọi: Call 1 còn 29m nữa là bắt đầu",
			"type": "popup",
			"related_record_id": "16",
			"related_record_name": "Call 1",
			"related_module_name": "Calendar",
			"extra_data": {
				"description": "Ghi chú: gọi check tái ký hợp đồng"
			}
		}';

		$senderId = $_POST['sender_id'];
		$serverKey = $_POST['server_key'];
		$userId = $_POST['user_id'];
		$payload = $_POST['payload'];
		$isPortalUser = ($_POST['is_portal_user'] == 'on');

		$viewer = new Vtiger_Viewer();
		$viewer->assign('SENDER_ID', $senderId);
		$viewer->assign('SERVER_KEY', $serverKey);
		$viewer->assign('USER_ID', $userId);
		$viewer->assign('PAYLOAD', $payload);
		$viewer->assign('IS_PORTAL_USER', $isPortalUser);

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$viewer->assign('PAYLOAD', $defaultPayload);
		}
		else if ($senderId != $googleConfig['firebase']['fcm_sender_id']) {
			die('Provided Sender ID does not match with config_override.php!');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($senderId) && !empty($serverKey) && !empty($userId) && !empty($payload)) {
			$googleConfig['firebase']['fcm_server_key'] = $serverKey; // By pass config
			$payload = json_decode($payload, true);

			if ($payload) {
				if ($isPortalUser) {
					$clientTokens = CustomerPortal_Notification_Helper::getFcmTokens($userId);
				}
				else {
					$clientTokens = CPNotifications_Data_Model::getClientTokens($userId);
				}

				$payload = $this->preparePayload($payload);

				$result = [
					'client_tokens' => $clientTokens,
					'send_result' => FCMHelper::sendNotification($payload['title'], $payload['body'], $userId, $payload['data'], $isPortalUser)
				];

				$viewer->assign('RESULT', $result);
			}
		}

		$viewer->display('modules/CPNotifications/tpls/PushNotificationsTest.tpl');
	}

	function preparePayload($data) {
		// Format the payload like what we've done in NotificationHelper::sendNotification()
		$payload = [
			'title' => 'New notification!',
			'body' => $data['message'],
			'data' => [
				'image' => $data['image'],
				'type' => $data['type'] ?? 'notification',
				'related_record_id' => $data['related_record_id'], 
				'related_record_name' => $data['related_record_name'], 
				'related_module_name' => $data['related_module_name'], 
				'extra_data' => $data['extra_data']
			]
		];
		
		return $payload;
	}
}