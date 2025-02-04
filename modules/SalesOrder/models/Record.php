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
class SalesOrder_Record_Model extends Inventory_Record_Model {
	
	static function updateAccountTotalSales($accountId) {
		if (empty($accountId)) return;

        global $adb;

		$sql = "SELECT SUM(total) AS total_sales
		FROM vtiger_salesorder
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid
		WHERE vtiger_crmentity.deleted = 0 
		  AND vtiger_salesorder.accountid = ? 
		  AND vtiger_salesorder.sostatus = 'Delivered'";
        $result = $adb->pquery($sql, [$accountId]);

        $totalSales = $adb->query_result($result, 0, 'total_sales');
        $totalSales = $totalSales ?: 0; 

        $updateSql = "UPDATE vtiger_account SET total_sales = ? WHERE accountid = ?";
        $adb->pquery($updateSql, [$totalSales, $accountId]);
		
	}
	static function summaryInvoiceByIdSaleOrder($saleOrderId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
	
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_invoice.invoice_type, vtiger_invoice.subject, vtiger_invoice.accountid, vtiger_invoice.contactid, vtiger_invoice.invoicedate, vtiger_invoice.invoicestatus, vtiger_invoice.total, vtiger_invoice.duedate, vtiger_crmentity.smownerid FROM vtiger_invoice inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid left outer join vtiger_account on vtiger_account.accountid=vtiger_invoice.accountid inner join vtiger_salesorder on vtiger_salesorder.salesorderid=vtiger_invoice.salesorderid LEFT JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid LEFT JOIN vtiger_invoicebillads ON vtiger_invoicebillads.invoicebilladdressid = vtiger_invoice.invoiceid LEFT JOIN vtiger_invoiceshipads ON vtiger_invoiceshipads.invoiceshipaddressid = vtiger_invoice.invoiceid left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid  where vtiger_crmentity.deleted=0 and vtiger_salesorder.salesorderid=?";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$saleOrderId]);
	
		// Kiểm tra kết quả
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function summaryReceiptsByIdSaleOrder($saleOrderId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_cpreceipt.code, vtiger_cpreceipt.amount, vtiger_cpreceipt.cpreceipt_currency, vtiger_cpreceipt.amount_vnd, vtiger_cpreceipt.paid_date, vtiger_cpreceipt.cpreceipt_status, vtiger_crmentity.smownerid, vtiger_crmentity.createdtime FROM vtiger_cpreceipt INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_cpreceipt.cpreceiptid LEFT JOIN vtiger_cpreceiptcf ON vtiger_cpreceiptcf.cpreceiptid = vtiger_cpreceipt.cpreceiptid INNER JOIN vtiger_salesorder AS vtiger_salesorderSalesOrder ON vtiger_salesorderSalesOrder.salesorderid = vtiger_cpreceipt.related_salesorder LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_salesorderSalesOrder.salesorderid = ?";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$saleOrderId]);
	
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	static function summaryExpensesByIdSaleOrder($saleOrderId) {
		global $adb; // Sử dụng đối tượng PearDatabase toàn cục
	
		
		$sql = "SELECT DISTINCT vtiger_crmentity.crmid,vtiger_cppayment.code, vtiger_cppayment.amount, vtiger_cppayment.cppayment_currency, vtiger_cppayment.amount_vnd, vtiger_cppayment.paid_date, vtiger_cppayment.cppayment_status, vtiger_crmentity.smownerid, vtiger_crmentity.createdtime FROM vtiger_cppayment INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_cppayment.cppaymentid LEFT JOIN vtiger_cppaymentcf ON vtiger_cppaymentcf.cppaymentid = vtiger_cppayment.cppaymentid INNER JOIN vtiger_salesorder AS vtiger_salesorderSalesOrder ON vtiger_salesorderSalesOrder.salesorderid = vtiger_cppayment.related_salesorder LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid  WHERE vtiger_crmentity.deleted = 0 AND vtiger_salesorderSalesOrder.salesorderid = ?";
	
		// Thực hiện truy vấn
		$result = $adb->pquery($sql, [$saleOrderId]);
	
		$count = $adb->getRowCount($result);
		return $count;
		
	}
	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');

		return "index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&salesorder_id=".$this->getId();
	}

	// Modified by Tung Nguyen on 2022.08.19 to support case add relationship between PO and SO when create PO from custom link at module SO
	function getCreatePurchaseOrderUrl() {
		$purchaseOrderModuleModel = Vtiger_Module_Model::getInstance('PurchaseOrder');
		return "index.php?module=".$purchaseOrderModuleModel->getName()."&view=".$purchaseOrderModuleModel->getEditViewName()."&salesorder_id=".$this->getId().
		"&sourceModule=".$this->getModuleName()."&sourceRecord=".$this->getId()."&relationOperation=true";
	}

	/**
	 * Implement by Tung Nguyen on 2022.08.18 to define default function
	 * Function to get List of Fields which are related from SalesOrder to Inventory Record.
	 * @return [['parentField' => 'salesorder_fieldname', 'inventoryField' => 'current_inventorymodule_filename'], 'defaultValue' => '']
	 */
	public function getInventoryMappingFields() {
		return [
			['parentField' => 'contact_id', 'inventoryField' => 'contact_id', 'defaultValue' => '']
		];
	}
}