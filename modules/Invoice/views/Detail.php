<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_Detail_View extends Inventory_Detail_View {

    public function process(Vtiger_Request $request) {
		// Added by Phuc on 2019.08.01 to display type of order
		$recordId = $request->get('record');
		$viewer = $this->getViewer($request);

		if (!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, 'Invoice');
			
			if ($recordModel->get('invoice_type') == 'buy') {
				if (!empty($recordModel->get('related_purchaseorder'))) {
					$orderModel = Vtiger_Record_Model::getInstanceById($recordModel->get('related_purchaseorder'), 'PurchaseOrder');
					$orderLink = '<span class="value"><a target="_blank" href="index.php?module=PurchaseOrder&view=Detail&record='.$recordModel->get('related_purchaseorder').'">'.$orderModel->get('subject').'</a></span>';
				}

				if (!empty($recordModel->get('related_vendor'))) {
					$partnerModel =  Vtiger_Record_Model::getInstanceById($recordModel->get('related_vendor'), 'Vendors');
					$partnerLink = '<span class="value"><a target="_blank" href="index.php?module=Vendors&view=Detail&record='.$recordModel->get('related_vendor').'">'.$partnerModel->get('vendorname').'</a></span>';
				}
			}
			else if ($recordModel->get('invoice_type') == 'sell') { // Modified by Phu Vo on 2021.11.13
				if (!empty($recordModel->get('salesorder_id'))) {
					$orderModel = Vtiger_Record_Model::getInstanceById($recordModel->get('salesorder_id'), 'SalesOrder');
					$orderLink = '<span class="value"><a target="_blank" href="index.php?module=SalesOrder&view=Detail&record='.$recordModel->get('salesorder_id').'">'.$orderModel->get('subject').'</a></span>';
				}
				if (!empty($recordModel->get('account_id'))) {
					$partnerModel =  Vtiger_Record_Model::getInstanceById($recordModel->get('account_id'), 'Accounts');
					$partnerLink = '<span class="value"><a target="_blank" href="index.php?module=Accounts&view=Detail&record='.$recordModel->get('account_id').'">'.$partnerModel->get('accountname').'</a></span>';
				}
			}
			// Added by Phu Vo on 2021.11.13 to display label for case account
			else if (!empty($recordModel->get('account_id'))) {
				$partnerModel =  Vtiger_Record_Model::getInstanceById($recordModel->get('account_id'), 'Accounts');
				$partnerLink = '<span class="value"><a target="_blank" href="index.php?module=Accounts&view=Detail&record='.$recordModel->get('account_id').'">'.$partnerModel->get('accountname').'</a></span>';
			}
			// End Phu Vo

			$viewer->assign('ORDER_LINK', $orderLink);
			$viewer->assign('PARTNER_LINK', $partnerLink);
			
		}
		// Ended by Phuc
		parent::process($request);
	}

	// Added by Phuc on 2019.10.07 to display language in overlay
	public function getOverlayHeaderScripts(Vtiger_Request $request) {
		$jsFileNames = array(
			'~modules/Invoice/resources/DetailView.js'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

		// Get current language
		$currentLanguage = Vtiger_Language_Handler::getLanguage();
		$jsLanguageString = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage, 'Invoice');
		$jsLanguageString = json_encode($jsLanguageString['jsLanguageStrings']);

		// Echo languages
		echo  "<script>jsLanguageString = {$jsLanguageString};</script>";

		return $jsScriptInstances;
	}
	public function showModuleSummaryView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		$resultDocument = Invoice_Record_Model::summaryDocumentByIdInvoice($recordId);
		$resultDevices = Invoice_Record_Model::summaryDeviceslByIdInvoice($recordId);
	
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('EXTRA_SUMMARY_DOCUMENTS', $resultDocument);
		$viewer->assign('EXTRA_SUMMARY_DEVICES', $resultDevices);
	
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
	  
		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}
}
