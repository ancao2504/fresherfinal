<?php

/*
	Class Block Model
	Author: Hieu Nguyen
	Date: 2021-08-20
	Purpose: to handle logic related to module blocks
*/

class Contacts_Block_Model extends Vtiger_Block_Model {

	public function getFields() {
		if ((isForbiddenFeature('CustomerPortal') || !canCreatePortalCustomers()) && $this->label == 'LBL_CUSTOMER_PORTAL_INFORMATION') {
			return [];
		}
		
		return parent::getFields();
	}
}
