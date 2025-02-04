<?php

/*
	Class SMSNotifier_Service_Model
	Author: Hieu Nguyen
	Date: 2020-11-17
	Purpose: process SMS and OTT message queue in the background
*/

class SMSNotifier_Service_Model extends Vtiger_Base_Model {

	static function processMessageQueue() {
		global $adb, $smsQueueConfig;
		$log = LoggerManager::getLogger('SMS');
		$log->info('[CRON] Started processMessageQueue');

		try {
			$batchLimit = $smsQueueConfig['batch_limit'];
			$maxAttempts = $smsQueueConfig['max_attempts'];

			// Get messages from queue to process
			$sql = "SELECT q.cpsmsottmessagelogid AS queue_id, c.campaignid, c.campaigns_purpose, q.sms_ott_message_type AS message_type,
						q.related_customer, q.phone_number, q.content, q.attempt_count
				FROM vtiger_cpsmsottmessagelog AS q
				INNER JOIN vtiger_campaign AS c ON (c.campaignid = q.related_campaign)
				WHERE CONCAT(q.scheduled_send_date, ' ', q.scheduled_send_time) <= NOW()
					AND q.queue_status NOT IN ('dispatched', 'success')
					AND q.attempt_count < ?
				ORDER BY CONCAT(q.scheduled_send_date, ' ', q.scheduled_send_time)
				LIMIT ?";
			$params = [$maxAttempts, $batchLimit];
			$result = $adb->pquery($sql, $params);

			// Process queue
			while ($row = $adb->fetchByAssoc($result)) {
				$channel = $row['message_type'];
				$queueId = $row['queue_id'];

				if ($channel == 'SMS') {
					$smsGateway = SMSNotifier_Provider_Model::getActiveGateway();

					if (!$smsGateway) {
						$log->info('[CRON] No active gateway for SMS. Skip message queue ' . $queueId);
						continue;
					}
				}
				else {
					$ottGateway = CPOTTIntegration_Gateway_Model::getActiveGateway($channel);

					if (!$ottGateway) {
						$log->info('[CRON] No active gateway for channel: ' . $channel . '. Skip message queue ' . $queueId);
						continue;
					}
				}

				$status = 'failed';
				$logData = ['tracking_id' => '', 'error_message' => 'Unknown error'];

				// Call API to send message
				$message = html_entity_decode($row['content']);
				$receiver = ["{$row['phone_number']}" => $row['related_customer']];

				// Send SMS
				if ($channel == 'SMS') {
					if ($row['campaign_purpose'] == 'promotion') {
						// TODO: Check this later when integrating promotion API
						if (!method_exists($smsGateway, 'sendPromotionMsg')) {
							$log->info('[CRON] Provider not support send promotion API');
							continue;
						}
						
						// Send promotion message
						$sendResult = $smsGateway->sendPromotionMsg($message, $receiver);
					}
					else {
						$sendResult = $smsGateway->send($message, $receiver);
					}
				}
				// Send OTT
				else {
					$method = $ottGateway->channelMethodsMapping[$channel];
					$sendResult = $ottGateway->$method($message, $receiver);
				}

				// Update queue status
				if (empty($sendResult) || $sendResult[0]['error'] == true) {
					$logData['error_message'] = $sendResult[0]['statusmessage'];
				}
				else {
					$status = 'success';
					$logData = ['tracking_id' => $sendResult[0]['id']]; // Save tracking id of this message so that we can track it later
				}

				Campaigns_Data_Model::updateSMSOTTMessageQueueStatus($queueId, $status, $logData);
			}

			$log->info('[CRON] Finished processMessageQueue');
		}
		catch (Exception $ex) {
			$log->info('[CRON] processMessageQueue error: '. $ex->getMessage());
		}
	}
}