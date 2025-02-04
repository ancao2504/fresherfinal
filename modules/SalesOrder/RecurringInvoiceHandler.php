<?php
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/

require_once('include/utils/utils.php');

class RecurringInvoiceHandler extends VTEventHandler {
	public function handleEvent($handlerType, $entityData){
		global $log, $adb;
		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'SalesOrder') {
			$soId = $entityData->getId();
			$data = $entityData->getData();
			if($data['enable_recurring'] == 'on' || $data['enable_recurring'] == 1) {
				$frequency = $data['recurring_frequency'];
				$startPeriod = getValidDBInsertDateValue($data['start_period']);
				$endPeriod = getValidDBInsertDateValue($data['end_period']);
				$paymentDuration = $data['payment_duration'];
				$invoiceStatus = $data['invoicestatus'];
				if (isset($frequency) && $frequency != '' && $frequency != '--None--') {
					$check_query = "SELECT * FROM vtiger_invoice_recurring_info WHERE salesorderid=?";
					$check_res = $adb->pquery($check_query, array($soId));
					$noofrows = $adb->num_rows($check_res);
					if ($noofrows > 0) {
						$row = $adb->query_result_rowdata($check_res, 0);
						$query = "UPDATE vtiger_invoice_recurring_info SET recurring_frequency=?, start_period=?, end_period=?, payment_duration=?, invoice_status=? WHERE salesorderid=?";
						$params = array($frequency,$startPeriod,$endPeriod,$paymentDuration,$invoiceStatus,$soId);
					} else {
						$query = "INSERT INTO vtiger_invoice_recurring_info VALUES (?,?,?,?,?,?,?)";
						$params = array($soId,$frequency,$startPeriod,$endPeriod,$startPeriod,$paymentDuration,$invoiceStatus);
					}
					$adb->pquery($query, $params);
				}
			} else {
				$query = "DELETE FROM vtiger_invoice_recurring_info WHERE salesorderid = ?";
				$adb->pquery($query, array($soId));	
			}
		}

		global $currentModule, $current_user, $adb;
		// Added by Phuc on 2019.10.07 to calculate balanace
		include_once 'include/Webservices/Revise.php';
		include_once 'include/Webservices/Retrieve.php';

		if ($handlerType == 'vtiger.entity.aftersave') {
			if ($moduleName != 'SalesOrder') // Modified by Hoang Duc 2020-05-21 to fix cannot save SO with relate module
				return;
				
			$wsId = vtws_getWebserviceEntityId('SalesOrder', $entityData->getId());
			$wsRecord = vtws_retrieve($wsId, $current_user);
		 
			$wsRecord['balance'] = floatval($wsRecord['hdnGrandTotal'] - $wsRecord['total_received']);
			$query = "UPDATE vtiger_salesorder SET balance = ? WHERE salesorderid = ?";

			$adb->pquery($query, array($wsRecord['balance'], $entityData->getId()));
		}
		// Ended by Phuc
	}
} 


?>