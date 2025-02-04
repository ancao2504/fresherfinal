<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_DetailView_Model extends Inventory_DetailView_Model {

	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		$purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
		if ($currentUserModel->hasModuleActionPermission($purchaseOrderModuleModel->getId(), 'CreateView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => vtranslate('LBL_GENERATE') . ' ' . vtranslate($purchaseOrderModuleModel->getSingularLabelKey(), 'PurchaseOrder'),
				'linkurl' => $recordModel->getCreatePurchaseOrderUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		return $linkModelList;
	}

	// Added by Phuc on 2019.08.09 to remove marketing list on tabs
	public function getDetailViewRelatedLinks() {
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$recordModel = $this->getRecord();

		if ($recordModel->get('invoice_type') == 'sell') {
			$removeTab = 'CPPayment';
		}
		else {
			$removeTab = 'CPReceipt';
		}

		foreach ($relatedLinks as $key => $relatedLink) {
			if (isset($relatedLink['relatedModuleName']) && $relatedLink['relatedModuleName'] == $removeTab) {
				unset($relatedLinks[$key]);
				break;
			}
		}

		return $relatedLinks;
	}
	// Ended by Phucz
}
