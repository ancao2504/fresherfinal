<?php

/*
	SouthTelecomREST_Provider
	Author: Hieu Nguyen
	Date: 2018-06-27
	Purpose: to provide helper class for sending SMS with SouthTelecom REST APIs
*/

require_once('modules/SMSNotifier/BaseProvider.php');
require_once('libraries/UUID/UUID.php');

class SMSNotifier_SouthTelecom_Provider extends SMSNotifier_Base_Provider {

	protected $isUnicodeSMSSupported = true;	// Determine if the provider support unicode SMS

	function __construct() {
		$this->serviceURI = 'https://api-01.worldsms.vn/webapi';
		$this->requiredParams = [
			['name' => 'from', 'label' => 'Brandname', 'type' => 'text'],
			['name' => 'api_key', 'label' => 'API Key', 'type' => 'password'],
		];
	}

	public function getName() {
		return 'SouthTelecom';
	}

	public function getServiceURL($type = false) {
		switch (strtoupper($type)) {
			case self::SERVICE_SEND: return $this->serviceURI . '/sendSMS';
			default: return false;
		}
	}

	public function prepareParameters($phone, $content) {
		$params = [
			'from' => $this->parameters['from'],
			'to' => $phone,
			'text' => $content,
			'unicode' => 0,			// Temporary disable unicode till next update
			'dlr' => 1,
			'smsid' => UUID::v4(),	// To trace the message status when DLR event callback
		];

		return $params;
	}
	
	public function send($message, $toNumbers) {
		$serviceURL = $this->getServiceURL(self::SERVICE_SEND);
		$client = $this->getRestClient($serviceURL, ['authorization: Basic ' . $this->parameters['api_key']]);
		$results = [];

		foreach ($toNumbers as $number => $customerId) {
			$number = $this->correctPhoneNumber($number);
			$populatedMsg = populateTemplateWithRecordData($message, $customerId);	// Replace variables
			
			$params = $this->prepareParameters($number, $populatedMsg);
			$response = $this->callRestAPI($client, $params);

			$result = [
				'to' => $number,
				'id' => $params['smsid'], 	// This provider does not return message id. Use uuid as message id
				'message' => $populatedMsg,	// Return populated message
				'status' => $response->status == 1 ? self::MSG_STATUS_DISPATCHED : self::MSG_STATUS_FAILED,
				'statusmessage' => $response->description,
				'customer_id' => $customerId,
				'error' => $response == false || $response->status != 1
			];

			$results[] = $result;
		}

		return $results;
	}

	public function handleCallback(array $data) {
		if (empty($data) || empty($data['smsid'])) return;
		$smsId = $data['smsid'];
		$status = self::MSG_STATUS_DELIVERED;
		$errorMsg = '';
			
		if ($data['status'] != '1') {
			$errorMapping = [
				1 => 'Tin nhắn trùng, không gửi sang nhà mạng.',
				2 => 'Brandname chưa đăng ký',
				3 => 'Lỗi service của nhà mạng',
				4 => 'Độ dài tin nhắn vượt quy định của nhà mạng',
				5 => 'Nội dung chưa đăng ký',
				6 => 'Nội dung có chứa từ khoá bị chặn',
				13 => 'Thuê bao đăng ký MNP chưa hoàn tất',
				98 => 'Tin nhắn QC bị chặn',
				99 => 'Lỗi không xác định',
			];

			$status = self::MSG_STATUS_FAILED;
			$errorMsg = $errorMapping[$data['errorcode']];
		}

		CPSMSOTTMessageLog_Record_Model::updateStatusByTrackingId($smsId, $status, $errorMsg);
		return true;
	}
	
	public function getProviderEditFieldTemplateName() {
		return 'BaseProviderEditFields.tpl';	// This template will not show username and password field
	}
}