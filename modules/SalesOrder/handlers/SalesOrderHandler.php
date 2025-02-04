<?php

/*
*	SalesOrderHandler.php
*	Author: Phuc Lu
*	Date: 2020.03.05
*   Purpose: provide handler events for SalesOrder
*/

class SalesOrderHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'SalesOrder') return;

		if ($eventName === 'vtiger.entity.beforesave') {
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			$this->updateAccountTotalSales($entityData->get('account_id'));
			$this->updateCustomerGroup($entityData);
			$this->updateCustomerType($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
		}

		if ($eventName === 'vtiger.entity.afterdelete') {	
		}
	}
	private function updateAccountTotalSales($accountId) {
		SalesOrder_Record_Model::updateAccountTotalSales($accountId);
    }

	private function updateMauticContactStage($entityData) {
		// Commented out to disable unused logic by Hieu Nguyen on 2021-11-02
		// if (CPMauticIntegration_Config_Helper::hasConfig('Contacts') && !empty($entityData->get('contact_id'))) {
		// 	CPMauticIntegration_Data_Helper::updateContactStageSegmentByStatus('SalesOrder', $entityData, $entityData->get('contact_id'));
		// }
	}

	private function updateCustomerGroup($entityData) {
		$accountId = $entityData->get('account_id');
		$status = $entityData->get('sostatus');

		if (!empty($accountId) && $status != 'Created' && $status != 'Cancelled') {
			Accounts_Data_Helper::setCustomerGroupForAccount($accountId);
		}
	}

	private function updateCustomerType($entityData) {
	
		$personalAccountId = Accounts_Data_Helper::getPersonalAccountId();

		if ($entityData->get('account_id') == $personalAccountId) {
			if (!empty($entityData->get('contact_id'))) {
				Contacts_Record_Model::updateContactType($entityData->get('contact_id'), 'Customer');
			}
		}
		else {
			Accounts_Record_Model::updateAccountType($entityData->get('account_id'), 'Customer');
		}
	}
}

