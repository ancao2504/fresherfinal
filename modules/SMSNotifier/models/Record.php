<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
vimport('~~/modules/SMSNotifier/SMSNotifier.php');

class SMSNotifier_Record_Model extends Vtiger_Record_Model {

	public static function SendSMS($message, $toNumbers, $currentUserId, $recordIds, $moduleName) {
		return SMSNotifier::sendsms($message, $toNumbers, $currentUserId, $recordIds, $moduleName);
	}

	// Added by Phu Vo on 2018.12.19
	public static function getSMSTemplateList() {
		global $adb;
		
		$sql = "SELECT t.cpsmstemplateid AS id, name, e.description FROM vtiger_cpsmstemplate AS t INNER JOIN vtiger_crmentity AS e ON e.crmid = t.cpsmstemplateid AND e.deleted = 0 WHERE e.setype = 'CPSMSTemplate'";
		$result = $adb->pquery($sql);
		$data = [];
		
		while ($row = $adb->fetchByAssoc($result)) {
			$data[] = $row;	
		}
		
		return $data;
	}
	// End Phu Vo

	public function checkStatus() {
		$statusDetails = SMSNotifier::smsquery($this->get('id'));
		$statusColor = $this->getColorForStatus($statusDetails[0]['status']);

		$this->setData($statusDetails[0]);

		return $this;
	}

	public function getCheckStatusUrl() {
		return "index.php?module=".$this->getModuleName()."&view=CheckStatus&record=".$this->getId();
	}

	public function getColorForStatus($smsStatus) {
		if ($smsStatus == 'Processing') {
			$statusColor = '#FFFCDF';
		} elseif ($smsStatus == 'Dispatched') {
			$statusColor = '#E8FFCF';
		} elseif ($smsStatus == 'Failed') {
			$statusColor = '#FFE2AF';
		} else {
			$statusColor = '#FFFFFF';
		}
		return $statusColor;
	}
}