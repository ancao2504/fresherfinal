<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// Modified by Hieu Nguyen on 2022-06-15
class Vtiger_BasicAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
        $this->exposeMethod('getAddress');
	}

    function checkPermission(Vtiger_Request $request) {
		global $current_user;
		$mode = $request->getMode();
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if ($mode == 'getAddress') {
			$hasRecordViewAccess = (is_admin($current_user)) || (isPermitted($moduleName, 'DetailView', $recordId) == 'yes');

			if (!$hasRecordViewAccess) {
				throw new AppException(vtranslate('LBL_NOT_ACCESSIBLE'));
			}
		}
	}

	// New process logic
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}

		$this->_process($request);
	}

	// Original process logic for ajax autocomplete in relate fields
	private function _process(Vtiger_Request $request) {
		$searchValue = $request->get('search_value');
		$searchModule = $request->get('search_module');

		$parentRecordId = $request->get('parent_id');
		$parentModuleName = $request->get('parent_module');
		$relatedModule = $request->get('module');

		$searchModuleModel = Vtiger_Module_Model::getInstance($searchModule);
		$records = $searchModuleModel->searchRecord($searchValue, $parentRecordId, $parentModuleName, $relatedModule);

		$baseRecordId = $request->get('base_record');
		$result = [];

		foreach ($records as $moduleName => $recordModels) {
			foreach ($recordModels as $recordModel) {
				if ($recordModel->getId() != $baseRecordId) {
					$result[] = [
						'label' => decode_html($recordModel->getName()),
						'value' => decode_html($recordModel->getName()),
						'id' => $recordModel->getId()
					];
				}
			}
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	// Support getting address of any module
	function getAddress(Vtiger_Request $request) {
		global $addressConfig;
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$addressFields = $addressConfig['address_fields_map'][$moduleName];
		$address = '';

		if (!empty($addressFields)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

			// Find non-empty address
			foreach ($addressFields as $fieldName) {
				$address = trim(decodeUTF8($recordModel->get($fieldName)));
				if (!empty($address)) break;
			}
		}

		$respose = new Vtiger_Response();
		$respose->setResult(['address' => $address]);
		$respose->emit();
	}
}
