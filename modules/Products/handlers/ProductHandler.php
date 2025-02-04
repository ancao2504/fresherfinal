<?php

/*
*	ProductHandler.php
*	Author: Hieu Nguyen
*	Date: 2019-08-15
*   Purpose: provide handler function for Products
*/

class ProductHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if (strpos($eventName, 'vtiger.picklist') !== false) {
			$this->handlePicklistEvent($eventName, $entityData);
			return;
		}

		if ($entityData->getModuleName() != 'Products') return;

		try {
			$this->syncProductToBBH($eventName, $entityData);
		}
		catch (Exception $ex) {
			$err = $ex->getMessage();   // To debug
		}

		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			$this->deleteSocialImageMapping($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {
			$this->deleteSocialImageMapping($entityData, true);
		}
	}

	private function handlePicklistEvent($eventName, $eventData) {
		try {
			$this->syncProductCategoryToBBH($eventName, $eventData);
		}
		catch (Exception $ex) {
			$err = $ex->getMessage();   // To debug
		}
	}

	// Sync category to BBH
	function syncProductCategoryToBBH($eventName, $eventData) {
		require_once('include/utils/BBHUtils.php');
		if (!CPChatBotIntegration_Config_Helper::isBBHEnabled()) return;
		if ($eventData['fieldname'] != 'productcategory') return;
		$chatBotConfig = CPChatBotIntegration_Config_Helper::getConfig();
 
		// When a category is created or updated
		if ($eventName == 'vtiger.picklist.aftercreate' || $eventName == 'vtiger.picklist.afterrename') {
			$action = 'partner_category_create';
			$categoryId = $eventData['optionid'];
			$categoryName = $eventData['optionvalue'];

			if ($eventName == 'vtiger.picklist.afterrename') {
				$action = 'partner_category_update';
				$categoryName = $eventData['newvalue'];
			}

			$serviceUrl = BBHUtils::getServiceUrl('store', 'category/' . $action);
			$headers = ["Authorization: {$chatBotConfig['params']['store_access_token']}"];
			$params = [
				'category_id' => $categoryId,
				'category_name' => $categoryName,
				'other_info' => [
					'productcategory' => vtranslate($categoryName, 'Products', 'vn_vn')
				]
			];

			$result = BBHUtils::callBBHApi($serviceUrl, 'POST', $headers, $params);
		}

		// When a category is deleted
		if ($eventName == 'vtiger.picklist.afterdelete') {
			$deletedIds = $eventData['deletedids'];

			foreach ($deletedIds as $deletedId) {
				$serviceUrl = BBHUtils::getServiceUrl('store', 'category/partner_category_delete');
				$serviceUrl .= '?category_id=' . $deletedId;
				$headers = ["Authorization: {$chatBotConfig['params']['store_access_token']}"];

				$result = BBHUtils::callBBHApi($serviceUrl, 'GET', $headers, []);
			}
		}
	}

	// Sync Product To BBH
	function syncProductToBBH($eventName, $entityData) {
		require_once('include/utils/BBHUtils.php');
		if (!CPChatBotIntegration_Config_Helper::isBBHEnabled()) return;
		global $site_URL;
		$chatBotConfig = CPChatBotIntegration_Config_Helper::getConfig();
 
		// When a new product is created
		if ($eventName == 'vtiger.entity.aftersave') {
			$action = 'partner_product_update';

			if ($entityData->isNew()) {
				$action = 'partner_product_create';
			}

			$recordModel = Vtiger_Record_Model::getInstanceById($entityData->getId(), 'Products');
			$images = $recordModel->getImageDetails();
			$image = '';

			if (!empty($images[0]['id'])) {
				$image = "{$site_URL}/{$images[0]['path']}_{$images[0]['name']}";
			}

			$serviceUrl = BBHUtils::getServiceUrl('store', 'product/' . $action);
			$headers = ["Authorization: {$chatBotConfig['params']['store_access_token']}"];
			$params = [
				'category_id' => getPicklistOptionId('productcategory', $entityData->get('productcategory')),
				'product_id' => $entityData->getId(),
				'product_name' => $entityData->get('productname'),
				'product_description' => $entityData->get('description'),
				'product_code' => $entityData->get('productcode'),
				'product_price' => $entityData->get('unit_price'),
				'product_quantity' => $entityData->get('qtyinstock'),
				'cost' => $entityData->get('purchase_cost'),
				'active' => $entityData->get('active') == 'on' ? 1 : 0,
				'currency_unit' => 'VND',
				'image' => $image,
				'other_info' =>  [
					'usageunit' => $entityData->get('usageunit'),
					'qty_per_unit' => $entityData->get('qty_per_unit'),
					'manufacturer' => $entityData->get('manufacturer')
				]
			];

			$result = BBHUtils::callBBHApi($serviceUrl, 'POST', $headers, $params);
		}

		// When a product is deleted
		if ($eventName == 'vtiger.entity.afterdelete') {
			$serviceUrl = BBHUtils::getServiceUrl('store', 'product/partner_product_delete');
			$serviceUrl .= '?product_id=' . $entityData->getId();
			$headers = ["Authorization: {$chatBotConfig['params']['store_access_token']}"];

			$result = BBHUtils::callBBHApi($serviceUrl, 'GET', $headers, []);
		}
	}

	function deleteSocialImageMapping($entityData, bool $productDeleted = false) {
		// Delete all product image mapping if the product is deleted
		if ($productDeleted == true) {
			CPSocialIntegration_Data_Model::deleteAllProductImageMapping($entityData->entityId, 'Zalo');
			return;
		}

		// Delete product image mapping if they are removed in this product
		if (isset($_REQUEST['imgDeleted']) && !empty($_REQUEST['imageid'])) {
			$deletedImageIds = json_decode($_REQUEST['imageid']);
			CPSocialIntegration_Data_Model::deleteProductImageMapping($entityData->get('id'), 'Zalo', $deletedImageIds);
		}
	}

}

