<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Invoice_Record_Model extends Inventory_Record_Model {

	public function getCreatePurchaseOrderUrl() {
		$purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
		return "index.php?module=".$purchaseOrderModuleModel->getName()."&view=".$purchaseOrderModuleModel->getEditViewName()."&invoice_id=".$this->getId();
	}


	static function getSummaryReceivedDetails($entityId) {
		global $adb;

		$sql = "SELECT balance, received FROM vtiger_invoice WHERE invoiceid = ?";
		$result = $adb->pquery($sql, [$entityId]);
		$result = $adb->fetchByAssoc($result);

		return $result;
	}
	static function summaryDocumentByIdInvoice($invoiceId) {
		global $adb; 
	
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_notes.title, vtiger_notes.folderid, vtiger_notes.filename, vtiger_notes.filestatus, vtiger_crmentity.modifiedtime, vtiger_notes.filedownloadcount, vtiger_crmentity.smownerid, vtiger_notes.filelocationtype FROM vtiger_notes inner join vtiger_senotesrel on vtiger_senotesrel.notesid= vtiger_notes.notesid left join vtiger_notescf ON vtiger_notescf.notesid= vtiger_notes.notesid inner join vtiger_crmentity on vtiger_crmentity.crmid= vtiger_notes.notesid and vtiger_crmentity.deleted=0 inner join vtiger_crmentity crm2 on crm2.crmid=vtiger_senotesrel.crmid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid left join vtiger_seattachmentsrel on vtiger_seattachmentsrel.crmid =vtiger_notes.notesid left join vtiger_attachments on vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid left join vtiger_users on vtiger_crmentity.smownerid= vtiger_users.id  where crm2.crmid=? AND vtiger_notes.filestatus = 1";


		$result = $adb->pquery($sql, [$invoiceId]);
	
		$count = $adb->getRowCount($result);
		return $count ;
		
	}
	static function summaryDeviceslByIdInvoice($invoiceId) {
		global $adb; 
	
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_assets.asset_no, vtiger_assets.assetname, vtiger_assets.product, vtiger_assets.account, vtiger_assets.assetstatus, vtiger_assets.contact, vtiger_assets.invoiceid, vtiger_assets.datesold, vtiger_assets.dateinservice FROM vtiger_assets INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_assets.assetsid LEFT JOIN vtiger_assetscf ON vtiger_assetscf.assetsid = vtiger_assets.assetsid INNER JOIN vtiger_invoice AS vtiger_invoiceInvoice ON vtiger_invoiceInvoice.invoiceid = vtiger_assets.invoiceid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_invoiceInvoice.invoiceid =?";


		$result = $adb->pquery($sql, [$invoiceId]);
	
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function summaryVouchersByIdInvoice($invoiceId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_cppayment.code, vtiger_cppayment.amount, vtiger_cppayment.cppayment_currency, vtiger_cppayment.amount_vnd, vtiger_cppayment.paid_date, vtiger_cppayment.cppayment_status, vtiger_crmentity.smownerid, vtiger_crmentity.createdtime FROM vtiger_cppayment INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid INNER JOIN vtiger_crmentityrel_1 ON (vtiger_crmentityrel_1.relcrmid = vtiger_crmentity.crmid) LEFT JOIN vtiger_cppaymentcf ON vtiger_cppaymentcf.cppaymentid = vtiger_cppayment.cppaymentid LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid  WHERE vtiger_crmentity.deleted = 0";


		$result = $adb->pquery($sql, []);
	
		$count = $adb->getRowCount($result);
		return $count;
		
	}
}
