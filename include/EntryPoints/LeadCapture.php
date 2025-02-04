<?php

/*
	EntryPoint LeadCapture
	Author: Hieu Nguyen
	Date: 2020-06-01
	Purpose: to capture lead submit from website / landing page or Mautic
	Usage:
		+ Get field list: (GET) entrypoint.php?name=LeadCapture
			Params:
				access_key: "<User-Access-Key>",
				language: 'vn_vn',
			Response:
				{
					"CPTargets": {"firstname", "Tên", "lastname": "Họ và tên đệm", "mobile": "Di động", ...}
					"Leads": {"firstname", "Tên", "lastname": "Họ và tên đệm", "mobile": "Di động", ...}
					"Contacts": {"firstname", "Tên", "lastname": "Họ và tên đệm", "mobile": "Di động", ...}
				}
		+ Submit data with mapping: (POST) entrypoint.php?name=LeadCapture
			Params:
				access_key: "<User-Access-Key>"
				input_source: "<Which-System-Create-This-Lead>"
				data: "<Field-Value-Mapping-Array>",
				mapping: {
					"CPTargets": {"Tên", "firstname", "Họ": "lastname", "Số di động": "mobile", ...}
					"Leads": {"Tên", "firstname", "Họ": "lastname", "Số di động": "mobile", ...}
					"Contacts": {"Tên", "firstname", "Họ": "lastname", "Số di động": "mobile", ...}
				}
			Response: 
				{
					"success": true,
					"message": Saved record ID: 123
				}
		+ Submit standard data: (POST) entrypoint.php?name=LeadCapture
			Params:
				access_key: "<User-Access-Key>"
				input_source: "<Which-System-Create-This-Lead>"
				data: "<Field-Value-Mapping-Array>"
			Response: 
				{
					"success": true,
					"message": Saved record ID: 123
				}
		+ Submit simple data: (POST) entrypoint.php?name=LeadCapture
			Params:
				access_key: "<User-Access-Key>"
				input_source: "<Which-System-Create-This-Lead>"
				simple_params: 1,
				firstname: "Hiếu",
				lastname: "Nguyễn",
				mobile: "0987654321",
				...
			Response: 
				{
					"success": true,
					"message": Saved record ID: 123
				}
*/

require_once('include/utils/SyncCustomerInfoUtils.php');

class LeadCapture extends Vtiger_EntryPoint {

	function getRequest() {
		require_once('include/utils/RestfulApiUtils.php');
		$request = RestfulApiUtils::getRequest();

		foreach ($_REQUEST as $key => $value) {
			$request->set($key, $value);
		}

		return $request;
	}

	function process (Vtiger_Request $request) {
		$request = $this->getRequest();
		$accessKey = $request->get('access_key');
		if (empty($accessKey)) $this->response('Access Key is not provided!', true);

		// Authenticate user with access key
		authenticateUserByAccessKey($accessKey);
		if (empty($_SESSION['authenticated_user_id'])) $this->response('Access Key is not matched!', true);

		// Handle request
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$fields = $this->getFields($request);
			echo json_encode($fields, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}
		else {
			$this->saveData($request);
		}
	}

	private function getFields(Vtiger_Request $request) {
		if (isForbiddenFeature('PluginLeadCapture')) return;
		$language = $request->get('language');
		$fields = [];
		$supportedModules = ['CPTarget', 'Leads', 'Contacts'];

		foreach ($supportedModules as $moduleName) {
			$fields[$moduleName] = $this->getModuleFields($moduleName, $language);
		}

		return $fields;
	}

	private function getModuleFields($moduleName, $language) {
		global $adb, $current_language;
		$current_language = $language;
		$moduleFields = [];
		$unsupportedFields = [
			'description',
			'assigned_user_id',
			'main_owner_id',
			'createdby',
			'tags',
			'lead_converted_id',
			'contact_converted_id',
			'account_converted_id',
			'potential_converted_id',
			'mautic_id',
			'last_synced_mautic_history_time',
			'synced_mautic_history_to_converted_lead',
		];

		$sql = "SELECT fieldname, fieldlabel
			FROM vtiger_field
			INNER JOIN vtiger_tab ON (vtiger_field.tabid = vtiger_tab.tabid)
			WHERE
				vtiger_field.fieldname NOT IN ('" . join("', '", $unsupportedFields) . "')
				AND vtiger_field.displaytype = 1
				AND vtiger_field.editview_presence IN (0, 2)
				AND vtiger_tab.name = ?";

		$result = $adb->pquery($sql, [$moduleName]);

		while ($row = $adb->fetchByAssoc($result)) {
			$moduleFields[$row['fieldname']] = vtranslate($row['fieldlabel'], $moduleName);
		}

		return $moduleFields;
	}

	private function saveData(Vtiger_Request $request) {
		global $current_user;
		$this->saveLog('Request params', $request->getAll());

		$inputSource = $request->get('input_source', 'EXTERNAL');
		$simpleParams = $request->get('simple_params', 0);
		$data = $request->get('data');
		$mapping = $request->get('mapping');
		
		// To support simple client that cannot send nested array
		if ($simpleParams === 0 && empty($data)) $this->response('Data is empty!', true);
		if ($simpleParams == 1) $data = $request->getAllPurified();
		
		try {
			$current_user = Users_Record_Model::getInstanceById($_SESSION['authenticated_user_id'], 'Users')->entity;

			// Default values
			$data['source'] = $inputSource;
			$data['assigned_user_id'] = $current_user->id;
			$data['main_owner_id'] = $current_user->id;
			$data['owner_populated'] = true;

			// Modified by Phu Vo on 2021.12.29 to use Sync Customer Info Util logic
			$customerId = null;
			
			// When client submit data with mapping (Wordpress Plugin)
			if (is_array($mapping) && !empty($mapping)) {
				$syncedRecordModel = SyncCustomerInfoUtils::syncCustomerInfo($data, null, $mapping);
				
				// Call custom save logic
				$this->saveFormData($data, $mapping);
			}
			// When client submit simple data
			else {
				foreach ($data as $fieldName => $value) {
					// Handle multipicklist field value
					if (strpos($value, '|') > 0) {
						$value = Vtiger_Multipicklist_UIType::encodeValues(explode('|', $value));
						$data[$fieldName] = $value;
					}
				}

				$syncedRecordModel = SyncCustomerInfoUtils::syncCustomerInfo($data);

				// Call custom save logic
				$this->saveFormData($data);
			}
			
			if (!empty($syncedRecordModel)) $customerId = $syncedRecordModel->getId();

			if (empty($customerId)) {
				$this->saveLog('Error saving data');
				$this->response('Error saving data!', true);
			}

			// Added by Phu Vo on 2022.03.14 to create event registration
			if (!empty($syncedRecordModel) && !empty($data['campaign_id']) && !empty($data['event_id'])) {
				CPEventRegistration_Data_Helper::createRegistration($syncedRecordModel, $data['campaign_id'], $data['event_id'], $data['source']);
			}
			// End Phu Vo

			// Create Ticket base on Sync Customer Info Config
			$syncCustomerInfoConfigs = SyncCustomerInfoUtils::getConfigs();

			if (!empty($customerId) && $syncCustomerInfoConfigs['auto_create_ticket_for_lead_capture'] == 1) {
				$extraData = [];

				if (!empty($data['leadsource'])) $extraData['leadsource'] = $data['leadsource'];
				if (!empty($data['leadsource_level_2'])) $extraData['leadsource_level_2'] = $data['leadsource_level_2'];

				if (empty($mapping)) $mapping = [];
				
				SyncCustomerInfoUtils::saveTicket($inputSource, $syncedRecordModel, $data, $extraData, $mapping);
			}

			$this->saveLog('Success. Record ID: '. $customerId);
			$this->response('Saved record ID: '. $customerId);
			// End Phu Vo
		}
		catch (Exception $ex) {
			$this->saveLog('Error: ' . $ex->getMessage(), $ex->getTrace());
			$this->response($ex->getMessage(), true);
		}
	}

	// Save extra data that customer submit from the form (request / requirement / order / booking)
	private function saveFormData(array $data, array $mapping = []) {
		// TODO: implement logic here to save form data when needed
	}

	private function response($message, $isError = false) {
		$response = [
			'success' => !$isError, 
			'message' => $message
		];
		
		echo json_encode($response);
		exit;
	}

	private function saveLog($description, $data = null) {
		$logger = LoggerManager::getLogger('WEBSERVICE');

		// Save log
		$log = 'Lead Capture Log: ' . $description . " - [IP: {$_SERVER['REMOTE_ADDR']}]" . "\r\n";
		if ($data !== null) $log .= 'Data: ' . json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) . "\r\n";
		$log .= '==============================';

		$logger->info($log);
	}
}