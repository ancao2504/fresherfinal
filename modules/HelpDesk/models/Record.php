<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return Vtiger_Util_Helper::getRecordName($this->getId());
	}

	/**
	 * Function to get URL for Convert FAQ
	 * @return <String>
	 */
	public function getConvertFAQUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ConvertFAQ&record=".$this->getId();
	}

	/**
	 * Function to get Comments List of this Record
	 * @return <String>
	 */
	public function getCommentsList() {
		$db = PearDatabase::getInstance();
		$commentsList = array();

		$result = $db->pquery("SELECT commentcontent AS comments FROM vtiger_modcomments WHERE related_to = ?", array($this->getId()));
		$numOfRows = $db->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			array_push($commentsList, $db->query_result($result, $i, 'comments'));
		}

		return $commentsList;
	}

	// Added by Phuc on 2020.06.29 to display stars for rating in Relate listview
	public function getDisplayValue($fieldName, $recordId = false) {
		if (empty($recordId)) {
			$recordId = $this->getId();
		}

		if ($fieldName == 'helpdesk_rating') {
			$ticketEntity = Vtiger_Record_Model::getInstanceById($recordId);

			if ($ticketEntity->get('ticketstatus') != 'Closed') {
				return '';
			}
			else {
				$smarty = new Vtiger_Viewer();
				$smarty->assign('CURRENT_STAR', $ticketEntity->get('helpdesk_rating'));
				$smarty->assign('TICKET_STATUS', $ticketEntity->get('ticketstatus'));

				return $smarty->fetch('modules/HelpDesk/tpls/RatingReadView.tpl');
			}
		}
		
		return parent::getDisplayValue($fieldName, $recordId);
	}
	// Ended by Phuc
}