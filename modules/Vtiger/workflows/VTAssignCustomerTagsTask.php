<?php

/*
	Workflow Task Assign Customer Tags
	Author: Hieu Nguyen
	Date: 2021-11-24
	Purpose: handle workflow action to assign tags to customer
*/

require_once('modules/com_vtiger_workflow/VTTaskManager.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');

class VTAssignCustomerTagsTask extends VTTask {

	public $executeImmediately = true;
	
	public function getFieldNames() {
		$formFields = [
			'related_customer_field',
			'get_tags_from_products_services',
			'tag_ids',
		];

		return $formFields;
	}
	
	public function doTask($entity) {
		VTTask::saveLog('[VTAssignCustomerTagsTask::doTask] Begin', $entity->getData());
		VTTask::saveLog('[VTAssignCustomerTagsTask::doTask] Workflow task info', $this);
		$selectedModule = $this->workflow->moduleName;

		// Process task
		$util = new VTWorkflowUtils();
		$util->adminUser();
		$entityWsId = $entity->getId();

		// Get customer id & customer type
		if (Settings_Workflows_Util_Helper::isCustomerModule($selectedModule)) {
			$customerId = end(explode('x', $entityWsId));
			$customerType = $selectedModule;
		}
		else {
			$customerWsId = $entity->get($this->related_customer_field);

			if (empty($customerWsId)) {
				$util->revertUser();
				VTTask::saveLog('[VTZaloOAMessageTask::doTask] No linked customer found. Nothing to do!');
				return;
			}

			$customerId = end(explode('x', $customerWsId));
			$customerType = Vtiger_Util_Helper::detectModulenameFromRecordId($customerWsId);
		}
		
		// Assign selected tags to customer
		if ($this->get_tags_from_products_services == '1') {
			$inventoryRecordId = end(explode('x', $entityWsId));
			$tags = $this->getPublicTagsFromProductsAndServices($inventoryRecordId);
		}
		else {
			$tags = is_array($this->tag_ids) ? $this->tag_ids : [$this->tag_ids];
		}
		
		Vtiger_Tag_Model::saveForRecord($customerId, $tags, 1, $customerType);

		// Finish task
		$util->revertUser();
		VTTask::saveLog('[VTAssignCustomerTagsTask::doTask] Finished');
	}

	private function getPublicTagsFromProductsAndServices($inventoryRecordId) {
		global $adb;
		$sql = "SELECT GROUP_CONCAT(DISTINCT t.id)
			FROM vtiger_freetags AS t
			INNER JOIN vtiger_freetagged_objects AS pt ON (pt.tag_id = t.id)
			INNER JOIN vtiger_inventoryproductrel AS ip ON (ip.productid = pt.object_id)
			INNER JOIN vtiger_crmentity AS pe ON (pe.crmid = ip.productid AND pe.deleted = 0)
			WHERE t.visibility = 'public' AND ip.id = ?";
		$tagIdsString = $adb->getOne($sql, [$inventoryRecordId]);
		$tagIds = explode(',', $tagIdsString);
		return $tagIds;
	}
}