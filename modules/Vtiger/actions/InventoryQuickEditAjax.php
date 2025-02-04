<?php
/*
	Action InventoryQuickEditAjax
	Author: Vu Mai
	Date: 2022-09-07
	Purpose: handle logic for select product and service and save inventory
*/

require_once('include/utils/InventoryUtils.php');
require_once('include/utils/SyncCustomerInfoUtils.php');

class Vtiger_InventoryQuickEditAjax_Action extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('searchItems');
		$this->exposeMethod('saveAjax');
	}

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());

		if(!$permission) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function searchItems(Vtiger_Request $request) {
		$keyword = $request->get('keyword');
		$ignoreIds = $request->get('ignore_ids');
		if (empty($keyword)) return;
		$resultProduct = searchInventoryItemByKeyword('Products', $keyword, $ignoreIds);
		$resultServices = searchInventoryItemByKeyword('Services', $keyword, $ignoreIds);
		$result = array_merge($resultProduct, $resultServices);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
    }

    // TODO
	function saveAjax(Vtiger_Request $request) {       
		global $adb;
		
		$moduleName = $request->get('module');
		$data = $request->getAllPurified();

 		if (empty($data['items']) && !is_array($data['items'])) {
			return;
		}

		// Validate each item
		foreach ($data['items'] as $item) {
			if (empty($item['id']) || empty($item['quantity']) || empty($item['quantity'])) {
				return;
			}
		}

		// Default values
		$data['tax_type'] = !empty($data['tax_type']) ? $data['tax_type'] : 'group';

		$response = $this->saveRecord($moduleName, $data);

		if ($response['success'] === 1) {
			$inventoryId = $response['id'];

			// Set up adjustment total value follow adjustment type
			if ($data['adjustment_type'] == '-') {
				$data['adjustment_total'] = - $data['adjustment_total'];
			}

			// Set up discount amount and discount percent value follow discount type
			if ($data['discount_type'] == 'percentage') {
				$data['discount_amount'] = null;
			} 
			elseif ($data['discount_type'] == 'amount') {
				$data['discount_percent'] = null;
			}
			else {
				$data['discount_percent'] = null;
				$data['discount_amount'] = null;
			} 

			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
			$sql = "UPDATE {$moduleModel->basetable}
				SET taxtype = ?, subtotal = ?, discount_percent = ?, discount_amount = ?, s_h_amount = ?, s_h_percent = ?, pre_tax_total = ?, adjustment = ?, total = ? 
				WHERE {$moduleModel->basetableid} = ?";
			$params = [$data['tax_type'], $data['total'], $data['discount_percent'], $data['discount_amount'], $data['charge_total'], $data['charge_total'], $data['pre_tax_total'], $data['adjustment_total'], $data['grand_total'], $inventoryId];
			$adb->pquery($sql, $params);

			// Delete final charge link to this inventory
			$adb->pquery("DELETE FROM vtiger_inventorychargesrel WHERE recordid = ?", [$inventoryId]);
			
			// Format inventory charges info
			$chargeInfo = [];

			foreach ($data['charge_info'] as $key => $value) {
				foreach ($value['taxes'] as $key => $tax) {
					$chargeInfo[$value['charge_id']]['taxes'][$tax['tax_id']] = $tax['value'];
				}

				$chargeInfo[$value['charge_id']]['value'] = $value['value'];

				if (!empty($value['percent'])) {
					$chargeInfo[$value['charge_id']]['percent'] = $value['percent'];
				}
			}

			$params = [
				'recordid' => $inventoryId,
				'charges' => json_encode($chargeInfo)
			];
			$sql = $adb->sql_insert_data('vtiger_inventorychargesrel', $params);
			$adb->query($sql);

			 // Set up Some Data to save inventory details;
			 $sql = "SELECT pt.productid, pt.taxpercentage, it.taxname, p.purchase_cost
			 FROM vtiger_inventorytaxinfo AS it 
			 INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
			 INNER JOIN vtiger_products AS p ON (p.productid = pt.productid)
			 UNION ALL
			 SELECT pt.productid, pt.taxpercentage, it.taxname, s.purchase_cost
			 FROM vtiger_inventorytaxinfo AS it 
			 INNER JOIN vtiger_producttaxrel AS pt ON (it.taxid = pt.taxid)
			 INNER JOIN vtiger_service AS s ON (s.serviceid = pt.productid)";

			$result = $adb->pquery($sql);
			$dataForInventory = [];
			
			// Store here to use later
			$dataForInventory['item_tax'] = [];
			$dataForInventory['purchase_cost'] = [];

			while ($row = $adb->fetchByAssoc($result)) {
				if (!isset($dataForInventory['item_tax'][$row['productid']])) $dataForInventory['item_tax'][$row['productid']] = [];

				$dataForInventory['item_tax'][$row['productid']][$row['taxname']] = $row['taxpercentage'];
				$dataForInventory['purchase_cost'][$row['productid']] = $row['purchase_cost'];
			}

			// Get default taxes
			$result = $adb->pquery("SELECT * FROM vtiger_inventorytaxinfo", []);
			$defaultTaxes = [];

			while ($row = $adb->fetchByAssoc($result)) {
				array_push($defaultTaxes, [
					'id' => $row['taxid'],
					'percentage' => $row['percentage'],
					'regions' => $row['regions'],
				]);
			}

			// Delete all product link to this inventory
			$adb->pquery("DELETE FROM vtiger_inventoryproductrel WHERE id = ?", [$inventoryId]);

			$count = 1;

			foreach ($data['items'] as $item) {
				if ($item['quantity'] <= 0) continue;

				$taxes = [];

				// Set up item tax data
				if ($data['tax_type'] == 'group') {
					foreach ($data['taxes'] as $tax) {
						$taxes[$tax['taxname']] = $tax['percentage'];
					}
				}
				else {
					foreach ($item['taxes'] as $tax) {
						$taxes[$tax['taxname']] = $tax['percentage'];
					}
				}

				// Store item tax to table vtiger_producttaxrel if item not exist in this table
				if (!array_key_exists($item['id'], $dataForInventory['item_tax'])) {
					foreach ($defaultTaxes as $tax) {
						$params = [
							'productid' => $item['id'],
							'taxid' => $tax['id'],
							'taxpercentage' => $tax['percentage'],
							'regions' => $tax['regions'],
						];
		
						// Generate inset sql
						$sql = $adb->sql_insert_data('vtiger_producttaxrel', $params);
						$adb->query($sql);
					}
				}

				// Calculate purchase cost
                $purchaseCost = $dataForInventory['purchase_cost'][$item['id']] * $item['quantity'];

				$params = array_merge (
					[
						'id' => $inventoryId,
						'productid' => $item['id'],
						'sequence_no' => $count,
						'section_num' => 1,
						'section_name' => '',
						'quantity' => $item['quantity'],
						'listprice' => $item['price'],
						'discount_percent' => $item['discount_percent'],
						'discount_amount' => $item['discount_amount'],
						'comment' => $item['comment'],
						'description' => $item['description'],
						'purchase_cost' => $purchaseCost,
						'margin' => $item['net_price'],
					],
					$taxes
				);

				// Generate inset sql
				$sql = $adb->sql_insert_data('vtiger_inventoryproductrel', $params);
				$adb->query($sql); // Maybe being transaction or something i dunno

				$count++;
			}

			$adb->query('commit;'); // Commit apply change
		}

		$result = $response['id'];
		$response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
	}

	function saveRecord($moduleName, $data) {
		global $current_user, $adb;
		$id = $data['id'];

		// Validate request
		if (empty($data)) {
			return false;
		}

		$customerId = $data['customer_id'];
		$customerType = $data['customer_type'];

		// Check & repare to convert lead
		if ($customerType == 'Leads') {
			$leadRecordModel = Vtiger_Record_Model::getInstanceById($customerId);

			// Check if lead exist
			if (!$leadRecordModel->getId()) {
				return false;
			}

			// Auto convert that lead to contact if not
			if ($leadRecordModel->get('leadstatus') != 'Converted') {
				$entityExtraValues = [];
				$entityExtraValues['Contacts'] = [];
				$entityExtraValues['Accounts'] = [];

				$entityIds = convertLead($leadRecordModel, $entityExtraValues);
				list($moduleId, $customerId) = explode('x', $entityIds['Contacts']);

				// Sync linked tag to new record
				$existingTags = [];
				$allAccessibleTags = getCustomerTags($leadRecordModel->getId(), 'Leads');

				foreach ($allAccessibleTags as $tag) {
					$existingTags [] = $tag['id'];
				}

				Vtiger_Tag_Model::saveForRecord($customerId, $existingTags, $current_user->id, 'Contacts');

				// Notify to call popup
				notifyMsgToClientAfterConverted($leadRecordModel->getId(), $customerId, 'Contacts');
			}
		}

		// Check & repare to convert target
		if ($customerType == 'CPTarget') {
			$targetRecordModel = Vtiger_Record_Model::getInstanceById($customerId);

			// Check if target exist
			if (!$targetRecordModel->getId()) {
				return false;
			}

			// Auto convert that target to contact if not
			if ($targetRecordModel->get('cptarget_status') != 'Converted' && empty($targetRecordModel->get('contact_converted_id'))) {
				checkRecordLimitWhenConvertData('CPTarget');
				$toModule = ['Contacts'];
				$recordModels = SyncCustomerInfoUtils::convertTargetByRecordModel($targetRecordModel, $toModule);
				$recordModel = $recordModels['Contacts'];
				$customerId = $recordModel->getId();

				// Transfer activity from target to contact when converting from target to contacts without going through leads
				transferTargetRelatedActivitiesToContacts($targetRecordModel->getId(), $customerId);

				// Sync linked tag to new record
				$existingTags = [];
				$allAccessibleTags = getCustomerTags($targetRecordModel->getId(), 'CPTarget');

				foreach ($allAccessibleTags as $tag) {
					$existingTags [] = $tag['id'];
				}

				Vtiger_Tag_Model::saveForRecord($customerId, $existingTags, $current_user->id, 'Contacts');

				// Transfer all comments from target to contact when converting from target to contacts without going through leads
				transferTargetRelatedCommentsToContact($targetRecordModel->getId(), $customerId);

				// Notify to call popup
				notifyMsgToClientAfterConverted($targetRecordModel->getId(), $customerId, 'Contacts');
			}
		}

		// Process
		try {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);

			if (!empty($id)) {
				$recordModel = Vtiger_Record_Model::getInstanceById($id, $moduleName);
			}

			$retrievedId = $recordModel->get('id');

			$recordModel->set('description', $data['description']);
			$statusFieldName = getStatusFieldName($moduleName);

			if (!empty($statusFieldName)) {
				$recordModel->set($statusFieldName, $data[$statusFieldName]);
			}

			if ($moduleName == 'SalesOrder') {
				$recordModel->set('ship_street', $data['ship_street']);
				$recordModel->set('receiver_name', $data['receiver_name']);
				$recordModel->set('receiver_phone', $data['receiver_phone']);
				$recordModel->set('has_invoice', $data['has_invoice']);
			}

			if (empty($retrievedId)) {
				if (empty($customerId)) {
					return false;
				}
				
				$customerRecordModel = Vtiger_Record_Model::getInstanceById($customerId);

				// Fill contact related if has value
				if ($customerRecordModel->getModuleName() == 'Contacts') {
					$recordModel->set('contact_id', $customerRecordModel->getId());
					$recordModel->set('subject', 'Đơn hàng của ' . $customerRecordModel->get('contact_no') . ' ngày ' . date('Y-m-d'));
				}
 
				if ($customerRecordModel->getModuleName() == 'Leads') {
					if ($customerRecordModel->get('leadstatus') == 'Converted' && $customerRecordModel->get('contact_converted_id')) {
						$recordModel->set('contact_id', $customerRecordModel->get('contact_converted_id'));

						// Set up subject
						$contactRecordModel = Vtiger_Record_Model::getInstanceById($customerRecordModel->get('contact_converted_id'));
						$recordModel->set('subject', 'Đơn hàng của ' . $contactRecordModel->get('contact_no') . ' ngày ' . date('Y-m-d'));
					}
				}

				if ($customerRecordModel->getModuleName() == 'CPTarget') {
					if ($customerRecordModel->get('cptarget_status') == 'Converted' && $customerRecordModel->get('contact_converted_id')) {
						$recordModel->set('contact_id', $customerRecordModel->get('contact_converted_id'));

						// Set up subject
						$contactRecordModel = Vtiger_Record_Model::getInstanceById($customerRecordModel->get('contact_converted_id'));
						$recordModel->set('subject', 'Đơn hàng của ' . $contactRecordModel->get('contact_no') . ' ngày ' . date('Y-m-d'));
					}
				}

				// Fill account related if has value
				if (!empty($customerRecordModel->get('account_id'))) {
					$recordModel->set('account_id', $customerRecordModel->get('account_id'));
				}
				else {
					$recordModel->set('account_id', Accounts_Data_Helper::getPersonalAccountId());
				}

				// Fill campaign related if has value
				if (!empty($data['campaign_id'])) {
					$recordModel->set('related_campaign', $data['campaign_id']);
				}

				// Set order_date
				$recordModel->set('order_date', date('Y-m-d'));
			}
			else {
				$recordModel->set('mode', 'edit');
			}

			$recordModel->save();

			// Respond
			$response = [
				'success' => 1,
				'id' => $recordModel->get('id'),
				'table_name' => $recordModel->getModule()->basetable
			];

			return $response;
		}
		// Handle error
		catch (Exception $ex) {
			return false;
		}	
	}
}