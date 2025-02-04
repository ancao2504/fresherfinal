<?php

/*
	Webhook MauticConnector
	Author: Hieu Nguyen
	Date: 2021-10-29
	Purpose: to handle HTTP call back from Mautic
*/

require_once('include/utils/WebhookUtils.php');

class MauticConnector extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
		if (!CPMauticIntegration_Config_Helper::isMauticEnabled()) return;

		// Get data from webhook
		$request = WebhookUtils::getRequest();
		$data = $request->getAll();

		saveLog('MAUTIC_WEBHOOK', 'Webhook data', $data);

		// Handle event
		$eventName = '';
		$eventData = [];
		$mauticContactInfo = [];
		
		if (!empty($data['mautic.email_on_open'])) {
			$eventName = 'mautic.email_on_open';
			$eventData = $data['mautic.email_on_open'][0]['stat'];
			$mauticContactInfo = $eventData['lead'];
		}

		if (!empty($data['mautic.page_on_hit'])) {
			$eventName = 'mautic.page_on_hit';
			$eventData = $data['mautic.page_on_hit'][0]['hit'];
			$mauticContactInfo = $eventData['lead'];
		}

		if (!empty($data['mautic.form_on_submit'])) {
			$eventName = 'mautic.form_on_submit';
			$eventData = $data['mautic.form_on_submit'][0]['submission'];
			$mauticContactInfo = $eventData['lead'];
		}

		if (!empty($data['mautic.lead_channel_subscription_changed'])) {
			$eventName = 'mautic.lead_channel_subscription_changed';
			$eventData = $data['mautic.lead_channel_subscription_changed'][0];
			$mauticContactInfo = $eventData['contact'];
		}

		if (!empty($data['mautic.lead_points_change'])) {
			$eventName = 'mautic.lead_points_change';
			$eventData = $data['mautic.lead_points_change'][0];
			$mauticContactInfo = $eventData['contact'];
		}

		if (!empty($data['mautic.lead_post_save_new'])) {
			$eventName = 'mautic.lead_post_save_new';
			$eventData = $data['mautic.lead_post_save_new'][0];
			$mauticContactInfo = $eventData['contact'];
		}

		if (!empty($data['mautic.lead_post_save_update'])) {
			$eventName = 'mautic.lead_post_save_update';
			$eventData = $data['mautic.lead_post_save_update'][0];
			$mauticContactInfo = $eventData['contact'];
		}

		if (!empty($data['mautic.lead_post_delete'])) {
			$eventName = 'mautic.lead_post_delete';
			$eventData = $data['mautic.lead_post_delete'][0];
			$mauticContactInfo = $eventData['contact'];
		}

		// Sync customer info from Mautic
		if ($this->shouldSyncCustomerInfo($eventName, $mauticContactInfo)) {
			try {
				$customerRecordModel = CPMauticIntegration_Data_Helper::syncCustomerFromMautic($eventName, $mauticContactInfo);

				if ($customerRecordModel) {
					saveLog('MAUTIC_WEBHOOK', 'Synced customer info', $customerRecordModel->getData());
				}
			}
			catch (Exception $e) {
				saveLog('MAUTIC_WEBHOOK', 'Error syncing customer info: ' . $e->getMessage(), $e->getTrace());
			}
		}

		// Save Mautic history for customer (skip for now as event info in webhook does not matching with activitiy info from Mautic api)
		// if (!empty($eventData) && !empty($customerRecordModel)) {
		// 	try {
		// 		$historyRecordModel = CPMauticIntegration_MauticHistory_Helper::saveMauticHistoryForWebhook($eventName, $eventData, $customerRecordModel);
		// 		saveLog('MAUTIC_WEBHOOK', 'Saved Mautic history record', $historyRecordModel->getData());
		// 	}
		// 	catch (Exception $e) {
		// 		saveLog('MAUTIC_WEBHOOK', 'Error saving Mautic history record: ' . $e->getMessage(), $e->getTrace());
		// 	}
		// }

		// Dev team can handle custom logic here

		echo 'Listening!';
	}

	private function shouldSyncCustomerInfo($eventName, array $mauticContactInfo) {
		global $mauticConfig;

		if (!empty($mauticContactInfo) && $mauticContactInfo['points'] >= $mauticConfig['min_points_to_sync_data']) {
			return true;
		}

		return false;
	}
}