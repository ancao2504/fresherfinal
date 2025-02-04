<?php

/*
    NotificationHelper
    Author: Hieu Nguyen
    Date: 2019-03-20
    Purpose: handle sending notification using Firebase Cloud Messaging service
*/

class NotificationHelper {

    static function sendNotification($data, $store = true) {
        require_once('include/utils/FCMHelper.php');

        // Ignore this message if the user is not exists
        if (!Vtiger_Functions::isUserExist($data['receiver_id'])) {
            return;
        }

        $id = '';

        // Save a notification log
        if ($store) {
            $id = CPNotifications_Data_Model::saveNotification($data);
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

        if (!$store) {
            $data['extra_data']['is_flash_msg'] = true;
        }

        if (!empty($id)) {
            $data['id'] = $id;
        }

        FCMHelper::sendNotification($title, $message, $userId, $data);
    }
}