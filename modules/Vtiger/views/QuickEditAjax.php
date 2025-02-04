<?php
/*
	File QuickEdit.php
	Author: Vu Mai
	Date: 2022-09-19
	Purpose: to render quick edit form for any module
*/

require_once('include/utils/InventoryUtils.php');
class Vtiger_QuickEditAjax_View extends Vtiger_IndexAjax_View {

	// Load field list in edit mode instead of detail mode
	function __construct() {
        parent::__construct();

        $GLOBALS['current_view'] = 'edit';  
    }

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		checkAccessForbiddenFeature("Module{$moduleName}");
		$record = $request->get('record');

		if (!Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		if (isReadonlyModule($moduleName)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function  process(Vtiger_Request $request) {
		global $inventoryModules;
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if (empty($moduleName) || empty($recordId)) return;

		if (in_array($moduleName, $inventoryModules)) {
			$this->processForInventory($request);
			return;
		}

		$quickEditContents = [];
		$info = [];
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_QUICKCREATE);
		$info['recordStructureModel'] = $recordStructureInstance;
		$info['recordStructure'] = $recordStructureInstance->getStructure();
		$info['moduleModel'] = $moduleModel;
		$quickEditContents[$moduleName] = $info;
		$picklistDependencyDatasource[$moduleName] = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$fieldsInfo = [];

		foreach ($fieldList as $name => $model) {
			$fieldsInfo[$name] = $model->getFieldInfo();
		}

		if ($moduleName == 'Documents') {
			$documentType = $recordModel->get('filelocationtype');
			$fields = $this->getFields($documentType);

			foreach ($fields as $specificFieldName) {
				$specificFieldModels[$specificFieldName] = $fieldList[$specificFieldName];

				if ($specificFieldName == 'notecontent') {
					$specificFieldModels[$specificFieldName]->set('fieldvalue', $recordModel->get($specificFieldName));
				}
			}


			$info['recordStructure'] = $specificFieldModels;
			$quickEditContents[$moduleName] = $info;
		}

		if (empty($info['recordStructure'])) {
			throw new AppException('This module does not support Quick Edit');
		}

		$viewer = $this->getViewer($request);

		if (file_exists('modules/'. $moduleName .'/custom/QuickCreate.php')) {
			require('modules/'. $moduleName .'/custom/QuickCreate.php');
			$viewer->assign('DISPLAY_PARAMS', $displayParams);
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SINGLE_MODULE', 'SINGLE_'.$moduleName);
		$viewer->assign('QUICK_EDIT_CONTENTS', $quickEditContents);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
		$viewer->assign('FIELD_MODELS', $fieldList);
		$viewer->assign('RECORD_ID', $recordId);

		if ($moduleName == 'Products' || $moduleName == 'Services') {
			$baseCurrenctDetails = $recordModel->getBaseCurrencyDetails();

			$viewer->assign('BASE_CURRENCY_NAME', 'curname' . $baseCurrenctDetails['currencyid']);
			$viewer->assign('BASE_CURRENCY_SYMBOL', $baseCurrenctDetails['symbol']);
			$viewer->assign('TAXCLASS_DETAILS', $recordModel->getTaxClassDetails());
		}

		if ($moduleName == 'Documents') {
			$viewer->assign('DOCUMENTS_TYPE', $documentType);
		}

		$viewer->assign('MAX_UPLOAD_LIMIT_MB', Vtiger_Util_Helper::getMaxUploadSize());
		$viewer->assign('MAX_UPLOAD_LIMIT_BYTES', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		
		if (!empty($recordModel)) {
			$salutationModel = getSalutationModel($recordModel, $request->get('salutationtype'));
			$viewer->assign('SALUTATION_FIELD_MODEL', $salutationModel);
		}
		
		$result = $viewer->fetch('modules/Vtiger/tpls/BaseQuickEdit.tpl');
		echo $result;
	}

	public function processForInventory (Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$customerId = $request->get('customer_id');
		$customerType = $request->get('customer_type');
		$statusFieldName = getStatusFieldName($moduleName);
		// Added By Vu Mai on 2022-11-04 to support create inventory from telesales campaign feature
		$mode = $request->get('mode');

		if ($mode && $mode == 'CreateFromTelesalesCampaign') {
			$this->processCreateFromTelesalesCampaign($request);
			return;
		}
		// End Vu Mai

		if (empty($recordId)) return;

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$inventoryRecordModel = Inventory_Record_Model::getInstanceById($recordId);
		$data = $this->getInventoryDataFromRequest($request);
		$data['line_items'] = $inventoryRecordModel->getProducts();
		$moduleModel = $recordModel->getModule();
		$lineItems = [];
		$index = 1; 

		foreach ($data['line_items'] as $item) {
			if ($item['hdnProductId' . $index]) {
				$lineItem = [
					'id' => $item['hdnProductId' . $index],
					'item_type' => $moduleName,
					'item_no' => $item['hdnProductcode' . $index],
					'label' => $item['productName' . $index],
					'quantity' => $item['qty' . $index],
					'price' => $item['listPrice' . $index] ?? 0,
					'total' => $item['productTotal' . $index] ?? 0,
					'discount_type' => $item['discount_type' . $index] ?? 'zero',
					'discount_percent' => $item['discount_percent' . $index] ?? 0,
					'discount_amount' => $item['discount_amount' . $index] ?? 0,
					'discount_total' => $item['discountTotal' . $index] ?? 0,
					'total_after_discount' => $item['totalAfterDiscount' . $index] ?? 0,
					'taxes' => $item['taxes'],
					'tax_total' => $item['taxTotal' . $index] ?? 0,
					'net_price' => $item['netPrice' . $index] ?? 0,
					'purchase_cost' => $item['purchaseCost' . $index] ?? 0,
				];
				
				array_push($lineItems, $lineItem);
				$index++;
			} 
		}

		// Define taxes array
		$taxes = [];

		foreach ($data['final_details']['taxes'] as $key => $value) {
			array_push($taxes, $value);
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('CUSTOMER_ID', $customerId);
		$viewer->assign('CUSTOMER_TYPE', $customerType);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('DATA', $data);
		$viewer->assign('LINE_ITEMS', $lineItems);
		$viewer->assign('TAXES', $taxes);
		$viewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
		$viewer->assign('STATUS_FIELD_NAME', $statusFieldName);
		$result = $viewer->fetch('modules/Vtiger/tpls/InventoryQuickEdit.tpl');
		echo $result;
	}

	private function getInventoryDataFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
        $recordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
        $data = $recordModel->getData();
        $moduleModel = $recordModel->getModule();
        $fieldModels = $moduleModel->getFields();
		$statusFieldName = getStatusFieldName($moduleName);

		// Get value instead of label of status field
		$status = $data[$statusFieldName];

        // We need display able data
        foreach (array_keys($data) as $fieldName) {
            $fieldModel = $fieldModels[$fieldName];

            if ($fieldModel) {
                $fieldType = $fieldModel->getFieldDataType();

                // Ignore for currency, we will use js to handle format
                if ($fieldType == 'currency') {
                    $data[$fieldName] = intval($data[$fieldName]);
                    continue;
                }
            }

            $data[$fieldName] = trim(strip_tags($recordModel->getDisplayValue($fieldName))) ?? $data[$fieldName];
        }

        // Process line items
        $lineItems = [];
        $relatedProducts = $recordModel->getProducts();
        $finalDetails = $relatedProducts[1]['final_details']; // Metadata for total amount section

        foreach ($relatedProducts as $index => $details) {
            $itemDetails = [];

            foreach ($details as $fieldName => $value) {
                $processedFieldName = str_replace($index, '', $fieldName);
                if (!is_array($value)) $itemDetails[$processedFieldName] = $value;
            }

            $lineItems[] = $itemDetails;
        }

        $data['line_items'] = $lineItems;
        $data['final_details'] = $finalDetails;
        $data['id'] = $data['record_id'] = $recordId;

		// Get status picklist by module name
		$data['status_field_name'] = $statusFieldName;
		$data['status_options'] = getPicklistsByModule($moduleName)[$statusFieldName];
		$data[$statusFieldName] = $status;

		// Support taxs
		$data['taxes_option'] = [];
		$taxs = getAllTaxes();
        array_unshift($taxs, []);

        foreach ($taxs as $index => $tax) {
            $data['taxes_option'][] = [
                'value' => $tax['taxname'] ?? 0,
                'text' => $tax['percentage'] ?? 0,
                'color' => '',
            ];
        }

        return decodeUTF8($data);
    }

	public function getFields($documentType){
		$fields = array();
		switch ($documentType) {
			case 'I' :
				$fields = array('notes_title', 'filename', 'assigned_user_id', 'folderid', 'notecontent');
				break;
			case 'E' :
				$fields = array('notes_title', 'filename', 'assigned_user_id', 'folderid');
				break;
		}
		return $fields;
	}
	
	// Added By Vu Mai on 2022-11-04 to support create inventory from telesales campaign feature
	public function processCreateFromTelesalesCampaign (Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$customerId = $request->get('customer_id');
		$customerType = $request->get('customer_type');
		$campaignId = $request->get('record');
		$userModel = Users_Privileges_Model::getCurrentUserModel();
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$relatedProducts = $recordModel->getProducts();
		$data = $recordModel->getData();
		$data['final_details'] = $relatedProducts[1]['final_details'];
		$moduleModel = $recordModel->getModule();
		$lineItems = [];
		$taxes = [];
		$taxs = getAllTaxes();
		array_unshift($taxs, []);

		foreach ($taxs as $index => $tax) {
			$data['taxes_option'][] = [
				'value' => $tax['taxname'] ?? 0,
				'text' => $tax['percentage'] ?? 0,
				'color' => '',
			];
		}

		$statusFieldName = getStatusFieldName($moduleName);

		// Get value instead of label of status field
		$status = $data[$statusFieldName];

		// Get status picklist by module name
		$data['status_field_name'] = $statusFieldName;
		$data['status_options'] = getPicklistsByModule($moduleName)[$statusFieldName];
		$data[$statusFieldName] = $status;

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('CUSTOMER_ID', $customerId);
		$viewer->assign('CUSTOMER_TYPE', $customerType);
		$viewer->assign('CAMPAIGN_ID', $campaignId);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('LINE_ITEMS', $lineItems);
		$viewer->assign('DATA', $data);
		$viewer->assign('USER_MODEL', $userModel);
		$viewer->assign('TAXES', $taxes);
		$viewer->assign('INVENTORY_CHARGES', Inventory_Charges_Model::getInventoryCharges());
		$viewer->assign('STATUS_FIELD_NAME', $statusFieldName);
		$result = $viewer->fetch('modules/Vtiger/tpls/InventoryQuickEdit.tpl');
		echo $result;
	}

	public function getHeaderScripts(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		
		$jsFileNames = array (
			"modules.$moduleName.resources.Edit"
		);

		if ($moduleName == 'Events') {
			$jsFileNames = array (
				"modules.Calendar.resources.Edit",
				"modules.$moduleName.resources.Edit"
			);
		}

		if ($moduleName == 'Documents') {
			$jsFileNames = array (
				"modules.Vtiger.resources.CkEditor"
			);
		}

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		if ($moduleName == 'Services') {
			$moduleEditFile = 'modules.' . $moduleName . '.resources.Edit';
			unset($jsScriptInstances[$moduleEditFile]);

			$jsFileNames = array (
				'modules.Products.resources.Edit',
			);

			$jsFileNames[] = $moduleEditFile;
			$jsScriptServiceInstances = $this->checkAndConvertJsScripts($jsFileNames);
			$headerScriptInstances = array_merge($jsScriptInstances, $jsScriptServiceInstances);
			$jsScriptInstances = $headerScriptInstances;
		}
		
		return $jsScriptInstances;
	}
}