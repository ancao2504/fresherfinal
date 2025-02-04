<?php

/*
	Class HanetAICameraUtils
	Author: Hieu Nguyen
	Date: 2021-04-01
	Purpose: To provide util functions for handling integration with Hanet AI Camera
*/

require_once('include/utils/WebhookUtils.php');

class HanetAICameraUtils extends WebhookUtils {

	static $logger = 'AICAMERA_INTEGRATION';

	protected static function getClient($serviceUrl, $requestAsJson = true) {
		$headers = [
			'accept: application/json',
			'cache-control: no-cache',
		];

		if ($requestAsJson) {
			$headers = array_merge($headers, ['content-type: application/json']);
		}
		
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

	static function callApi($method = 'POST', $path, $params, $requestAsJson = true) {
		global $aiCameraConfig;
		$serviceUrl = $aiCameraConfig['hanet']['service_url'] . $path;
		if ($path == 'token') $serviceUrl = $aiCameraConfig['hanet']['auth_url'] . $path;

		$client = self::getClient($serviceUrl, $requestAsJson);

		if (!empty($method)) {
			curl_setopt($client, CURLOPT_CUSTOMREQUEST, $method);
		}

		if (!$requestAsJson) {
			curl_setopt($client, CURLOPT_POSTFIELDS, $params);
		}
		else {
			curl_setopt($client, CURLOPT_POSTFIELDS, json_encode($params));
		}

		$response = curl_exec($client);
		$err = curl_error($client);
		curl_close($client);

		if ($err) {
			self::saveApiLog('[HanetAICameraUtils::callApi] Call API Error: '. $serviceUrl, null, null, [$err]);
			return false;
		}

		$data = json_decode($response, true);
		self::saveApiLog('[HanetAICameraUtils::callApi] Call API Success: '. $serviceUrl, [], $params, $data);

		return $data;
	}

	static function handleEventEmployeeCheckin(array $data) {
		if (isForbiddenFeature('HumanResourceManagement')) return;
		list($_, $employeeId) = explode(':', $data['aliasID']);

		try {
			$employee = Vtiger_Record_Model::getInstanceById($employeeId, 'CPEmployee');
		}
		catch (Exception $ex) {
			$employeeId = '';
			$err = $ex->getMessage();
			saveLog('AICAMERA_INTEGRATION', '[HanetAICameraUtils::handleEventEmployeeCheckin] Error no matching employee found: '. $err, $ex->getTrace());
		}

		// Trigger auto open roller shutter for this employee
		CPAICameraIntegration_Logic_Helper::triggerAutoOpenRollerShutter($employee, $data['aliasID'], $data['personName'], $data['deviceID'], $data['date']);

		// Save checkin log
		$imagePath = CPAICameraIntegration_Logic_Helper::saveCheckinImage(CPAICameraIntegration_Logic_Helper::$PERSON_TYPE_EMPLOYEE, $data['detected_image_url'], $employeeId, $data['date']);

		$checkinData = [
			'related_employee' => $employeeId,
			'detected_id' => $data['aliasID'],
			'detected_name' => $data['personName'],
			'detected_image' => $imagePath,
			'detected_time' => $data['date'],
			'place_name' => $data['placeName'],
			'device_id' => $data['deviceID'],
			'device_name' => $data['deviceName'],
			'tracking_id' => $data['id'],
		];

		// Send notification to employee owners if the employee record is matched
		if (!empty($employee)) {
			CPAICameraIntegration_Logic_Helper::sendEmployeeCheckinNotifications($employee, $checkinData);
		}

		// Save employee checkin log
		CPAICameraIntegration_Logic_Helper::saveEmployeeCheckinLog($checkinData);
	}

	static function handleEventCustomerCheckin(array $data) {
		// TODO
	}

	static function handleEventUnknownPersonCheckin(array $data) {
		// TODO
	}

	static function saveApiLog(string $description, array $headers = null, array $input = null, array $response = null) {
		global $aiCameraConfig;

		if ($aiCameraConfig['debug'] == true) {
			parent::saveLog($description, $headers, $input, $response);
		}
	}
}