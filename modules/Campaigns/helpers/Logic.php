<?php

/*
	Logic_Helper
	Author: Hieu Nguyen
	Date: 2020-11-16
	Purpose: to provide util functions for campaign
*/

class Campaigns_Logic_Helper {

	// Implemented by Hieu Nguyen on 2020-11-13
	static function getMetadataForSMSAndOTTMessage($campaignId, $channel) {
		$channel = strtolower($channel);

		$metadata = [];
		$metadata['campaign_info'] = Campaigns_Data_Model::getCampaignInfo($campaignId);
		$metadata['target_lists'] = Campaigns_Data_Model::getLinkTargetListsWithCustomersCount($campaignId);
		$metadata['templates'] = Campaigns_Data_Model::getSMSAndOTTMessageTemplates($channel);

		if ($channel == 'SMS' && $metadata['campaign_info']['purpose'] == 'promotion') {
			$metadata['promotion_api_supported'] = true;
			$smsGateway = SMSNotifier_Provider_Model::getActiveGateway();
			
			if (empty($smsGateway) || !$smsGateway->canSendPromotionMsg()) {
				$metadata['promotion_api_supported'] = false;
				$metadata['partner_contacts'] = Campaigns_Data_Model::getPartnerContacts();
				$metadata['email_templates'] = Campaigns_Data_Model::getEmailTemplates();
			}
		}

		return $metadata;
	}

	// Put message to queue to send later in the background
	static function putToSMSOTTMessageQueue(array $data, $notifierId, array $targetLists) {
		$customerIdsMap = Campaigns_Data_Model::getCustomerIdsFromTargetLists($targetLists, true);

		foreach ($customerIdsMap as $customerType => $customerIds) {
			foreach ($customerIds as $customerId) {
				$message = populateTemplateWithRecordData($data['message'], $customerId);
				
				if ($data['channel'] == 'SMS') {
					$provider = SMSNotifier_Provider_Model::getActiveGateway();
					$providerInfo = $provider->getInfo();

					if (!$providerInfo['unicode_sms_supported']) {
						$message = unUnicode($message);
					}
				}

				$customerRecordModel = Vtiger_Record_Model::getInstanceById($customerId, $customerType);
				
				foreach ($data['phone_fields'] as $phoneField) {
					$phoneNumber = $customerRecordModel->get($phoneField);

					if (!empty($phoneNumber)) {
						// Added a new message log with status = queued to be processed in the background later
						$queueData = [
							'campaign_id' => $data['campaign_id'],
							'customer_id' => $customerId,
							'phone_number' => $phoneNumber,
							'sms_ott_notifier_id' => $notifierId,
							'message' => $message,  // Store populated content so that it is easy for the cronjob to process
							'message_type' => $data['channel'],
							'status' => 'queued',
							'scheduled_date' => $data['schedule_date'],
							'scheduled_time' => date('H:i', strtotime($data['schedule_time'])), // Convert to 24H format for database time field
						];

						CPSMSOTTMessageLog_Record_Model::addNew($queueData);
					}
				}
			}
		}
	}

	static function sendSMSPromotionRequestToTelco($campaignId, array $targetLists, $phoneFields, array $data) {
		require_once('include/ExcelHelper.php');
		require_once('include/Mailer.php');
		global $current_user;
		$userFullName = getFullNameFromArray('Users', ['first_name' => $current_user->first_name, 'last_name' => $current_user->last_name]);
		
		// Get data
		$campaignRecordModel = Vtiger_Record_Model::getInstanceById($campaignId, 'Campaigns');
		$customers = Campaigns_Data_Model::getCustomersWithPhoneNumber($targetLists, $phoneFields, true);

		// Prepare excel file
		$excelData = [['STT', 'Họ tên', 'Điện thoại']];

		foreach ($customers as $index => $info) {
			$rowNum = $index + 1;

			$excelData[] = [
				$rowNum,
				$info['full_name'],
				$info['phone_number']
			];
		}

		$fileName = unUnicode($campaignRecordModel->get('campaignname')) .' - '. date('Y-m-d H:i:s');
		$customersFile = ExcelHelper::exportToExcel($excelData, $fileName, true, true);
		
		// Prepare message
		$sender = ['name' => $userFullName, 'email' => $current_user->email1];
		$mainReceivers = $data['email_to'];
		$emailSubject = $data['email_subject'];
		$emailBody = $data['email_content'] . "<br /><br /><hr />Nội dung tin nhắn quảng cáo: " . $data['message'] .'<br />Danh sách khách hàng trong file đính kèm.';
		$attachments = [['name' => $fileName . '.xlsx', 'path' => $customersFile]];
		$relatedContactIds = [];

		foreach ($mainReceivers as $receiver) {
			$relatedContactIds[] = $receiver['id'];
		}

		$result = Mailer::sendEmail(true, $mainReceivers, $emailSubject, $emailBody, [], [], [$sender], $attachments, $relatedContactIds, $sender);

		// Link email log with campaign
		if (!empty($result['email_log_id'])) {
			Campaigns_Data_Model::linkEmailLog($campaignId, $result['email_log_id']);
		}

		return $result;
	}

	/** Implemented by Phu Vo on 2020.11.17 */
	static function getCustomerExcelFile($params) {
		require_once('include/ExcelHelper.php');

		$status = $params['status'];
		$campaignId = $params['campaign_id'];

		$campaignName = Vtiger_Functions::getCRMRecordLabel($campaignId);
		$customers = Campaigns_MessageStatisticsWidget_Model::getCustomerList($params);

		// Prepare excel file
		$data = [['STT', 'Họ tên', 'Điện thoại', 'Loại']];

		foreach ($customers as $index => $info) {
			$rowNum = $index + 1;

			$data[] = [
				$rowNum,
				$info['customer_name'],
				$info['phone_number'],
				$info['customer_type'],
			];
		}

		$statusString = '';
		if (!empty($status)) $statusString = ' - ' . $status . ' ';

		$fileName = unUnicode($campaignName) . $statusString .' - '. date('Y-m-d H:i:s');
		$customersFile = ExcelHelper::exportToExcel($data, $fileName, true, true);

		return $customersFile;
	}

	static function hasCampaignLog($campaignType) {
		$hasLogTypes = [
			'SMS Message',
			'Zalo ZNS Message'
		];

		return in_array($campaignType, $hasLogTypes);
	}
}