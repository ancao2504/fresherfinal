<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SalesOrder_Detail_View extends Inventory_Detail_View {

    public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~modules/SalesOrder/resources/DetailView.css'
        );
        
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
    }
	
	public function showModuleSummaryView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		
		$resultInvoices= SalesOrder_Record_Model::summaryInvoiceByIdSaleOrder($recordId);
		$resultReceipts = SalesOrder_Record_Model::summaryReceiptsByIdSaleOrder($recordId);
		$resultExpenses = SalesOrder_Record_Model::summaryExpensesByIdSaleOrder($recordId);
	
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
	
		$viewer->assign('EXTRA_SUMMARY_EXPENSES', $resultExpenses );
		$viewer->assign('EXTRA_SUMMARY_INVOICES', $resultInvoices);
		$viewer->assign('EXTRA_SUMMARY_RECEIPTS', 	$resultReceipts);
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_NAME', $moduleName);
	  
		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}
	   

}
