<?php

/*
	Class CMCCloudCameraUtils
	Author: Hieu Nguyen
	Date: 2021-06-08
	Purpose: To provide util functions for handling integration with CMC Cloud Camera
*/

require_once('include/utils/WebhookUtils.php');

class CMCCloudCameraUtils extends WebhookUtils {

	static $logger = 'AICAMERA_INTEGRATION';

	protected static function getClient($serviceUrl, $headers = []) {
		$defaultHeaders = [
			'accept: application/json',
			'cache-control: no-cache',
			'content-type: application/json'
		];

		$headers = array_merge($defaultHeaders, $headers);

		$client = curl_init();
		
		curl_setopt_array($client, [
			CURLOPT_URL => $serviceUrl,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
		]);

		return $client;
	}

	static function callApi($method = 'POST', $path, $headers, $params) {
		global $aiCameraConfig;
		$serviceUrl = $aiCameraConfig['cmc']['service_url'] . $path;
		if ($path == 'token') $serviceUrl = $aiCameraConfig['cmc']['auth_url'] . $path;

		$client = self::getClient($serviceUrl, $headers);

		if (!empty($method)) {
			curl_setopt($client, CURLOPT_CUSTOMREQUEST, $method);
		}

		curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));

		$response = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			self::saveApiLog('[CMCCloudCameraUtils::callApi] Call API Error: '. $serviceUrl, null, null, [$err]);
			return false;
		}

		$data = json_decode($response, true);
		self::saveApiLog('[CMCCloudCameraUtils::callApi] Call API Success: '. $serviceUrl, $headers, $params, $data);

		return $data;
	}

	static function handleEventEmployeeCheckin(array $data) {
		if (isForbiddenFeature('HumanResourceManagement')) return;
		list($_, $employeeId) = explode(':', $data['personId']);

		try {
			$employee = Vtiger_Record_Model::getInstanceById($employeeId, 'CPEmployee');
		}
		catch (Exception $ex) {
			$employeeId = '';
			$err = $ex->getMessage();
			saveLog('AICAMERA_INTEGRATION', '[CMCCloudCameraUtils::handleEventEmployeeCheckin] Error no matching employee found: '. $err, $ex->getTrace());
		}

		// Trigger auto open roller shutter for this employee
		CPAICameraIntegration_Logic_Helper::triggerAutoOpenRollerShutter($employee, $data['personId'], $data['personName'], $data['cameraName'], date('Y-m-d H:i:s', $data['actionTime']));

		// Save checkin log
		$imagePath = CPAICameraIntegration_Logic_Helper::saveCheckinImage(CPAICameraIntegration_Logic_Helper::$PERSON_TYPE_EMPLOYEE, $data['imageUrl'], $employeeId, date('Y-m-d H:i:s', $data['actionTime']));

		$checkinData = [
			'related_employee' => $employeeId,
			'detected_id' => $data['personId'],
			'detected_name' => $data['personName'],
			'detected_image' => $imagePath,
			'detected_time' => date('Y-m-d H:i:s', $data['actionTime']),
			'matching_percent' => $data['percentage'],
			'place_name' => $data['cameraLocation'],
			'device_id' => $data['cameraName'],
			'device_name' => $data['cameraName'],
			'tracking_id' => $data['recordId'],
		];

		// Send notification to employee owners if the employee record is matched
		if (!empty($employee)) {
			CPAICameraIntegration_Logic_Helper::sendEmployeeCheckinNotifications($employee, $checkinData);
		}

		// Save employee checkin log
		CPAICameraIntegration_Logic_Helper::saveEmployeeCheckinLog($checkinData);
	}

	static function saveApiLog(string $description, array $headers = null, array $input = null, array $response = null) {
		global $aiCameraConfig;

		if ($aiCameraConfig['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}
}