<?php

/*
	Notification_Helper
	Author: Hieu Nguyen
	Date: 2020-06-25
	Purpose: to provide util functions for sending notifications to customer
*/

class CustomerPortal_Notification_Helper {

    static function getFcmTokens($customerId) {
        global $adb;
        if (empty($customerId)) return;

        $sql = "SELECT notify_tokens FROM vtiger_portalinfo WHERE id = ?";
        $notifyTokens = $adb->getOne($sql, [$customerId]);

        $notifyTokens = json_decode($notifyTokens, true) ?? [];
        return $notifyTokens;
    }

    static function saveFcmToken($customerId, $token) {
        global $adb;
        if (empty($customerId) || empty($token)) return;
        $notifyTokens = self::getFcmTokens($customerId);

        if (!in_array($token, $notifyTokens)) { // Modified by Phu Vo on 2021.03.28 to fix could not save notify token for portal
            $notifyTokens[] = $token;
        }

        $sql = "UPDATE vtiger_portalinfo SET notify_tokens = ? WHERE id = ?";
        $notifyTokens = $adb->getOne($sql, [json_encode($notifyTokens), $customerId]);
    }

    static function removeFcmToken($customerId, $tokenToRemove) {
        global $adb;
        if (empty($customerId) || empty($tokenToRemove)) return;
        $notifyTokens = self::getFcmTokens($customerId);

        if (empty($notifyTokens)) return;
        $remainingTokens = [];

        foreach ($notifyTokens as $token) {
            if ($token != $tokenToRemove) {
                $remainingTokens[] = $token;
            }
        }

        $sql = "UPDATE vtiger_portalinfo SET notify_tokens = ? WHERE id = ?";
        $notifyTokens = $adb->getOne($sql, [json_encode($remainingTokens), $customerId]);
    }

    static function sendNotification($data, $store = true) {
        require_once('include/utils/FCMHelper.php');

        // Ignore this message if the customer is not exists
        if (!isRecordExists($data['receiver_id'])) {
            return;
        }

        $id = '';

        // Save a notification log
        if ($store) {
            $id = self::saveNotification($data);
        }

        // Send real-time notification
        $title = 'New notification!';
        $message = $data['message'];
        $userId = $data['receiver_id'];
        $data = [
            'image' => $data['image'],
            'type' => $data['type'] ?? 'notification',
            'related_record_id' => $data['related_record_id'], 
            'related_record_name' => $data['related_record_name'], 
            'related_module_name' => $data['related_module_name'], 
            'extra_data' => $data['extra_data']
        ];

        if (!empty($id)) {
            $data['id'] = $id;
        }

        FCMHelper::sendNotification($title, $message, $userId, $data, true);
    }

    private static function saveNotification($data) {
        global $adb;

        $sql = "INSERT INTO portal_notifications(
                receiver_id, category, image, related_record_id, 
                related_record_name, related_module_name, `read`, extra_data
            ) 
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['receiver_id'], $data['category'], $data['image'], $data['related_record_id'], 
            $data['related_record_name'], $data['related_module_name'], 0, json_encode($data['extra_data'])
        ];

        $adb->pquery($sql, $params);

        return $adb->getLastInsertID();
    }

    static function getNotificationCount($customerId) {
        global $adb;
        if (empty($customerId)) return;

        $sql = "SELECT COUNT(id) FROM portal_notifications WHERE receiver_id = ? AND `read` = 0";
        $count = $adb->getOne($sql, [$customerId]);

        return $count;
    }
}