<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		
        $recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);

        // Disable Send Email button in Account DetailView by Hieu Nguyen on 2020-11-09 as these action should be done in specific customer modules
        /*$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');
		if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendEmail("index.php?module='.$this->getModule()->getName().
								'&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}*/

		// Modified by Phu Vo on 2019.09.16 to remove delete button when it is personal account
		if ($this->record->get('account_no') === 'PACC') {
			foreach($linkModelList['DETAILVIEWBASIC'] as $index => $link) {
				if ($link->linklabel == 'LBL_EDIT') unset($linkModelList['DETAILVIEWBASIC'][$index]);
			}

			foreach($linkModelList['DETAILVIEW'] as $index => $link) {
				if ($link->linklabel == 'LBL_DELETE') unset($linkModelList['DETAILVIEW'][$index]);
			}
		}
		// End Phu Vo remove delete button when it is personal accounts

		//TODO: update the database so that these separate handlings are not required
		$index=0;
		foreach($linkModelList['DETAILVIEW'] as $link) {
			if($link->linklabel == 'View History' || $link->linklabel == 'Send SMS') {
				unset($linkModelList['DETAILVIEW'][$index]);
			} else if($link->linklabel == 'LBL_SHOW_ACCOUNT_HIERARCHY') {
				$linkURL = 'index.php?module=Accounts&view=AccountHierarchy&record='.$recordModel->getId();
				$link->linkurl = 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("'.$linkURL.'");';
				unset($linkModelList['DETAILVIEW'][$index]);
				$linkModelList['DETAILVIEW'][$index] = $link;
			}
			$index++;
		}
		
		$CalendarActionLinks = array();
		$CalendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		if($currentUserModel->hasModuleActionPermission($CalendarModuleModel->getId(), 'CreateView')) {
			$CalendarActionLinks[] = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_ADD_EVENT',
					'linkurl' => $recordModel->getCreateEventUrl(),
					'linkicon' => ''
			);

			// Modified by Hieu Nguyen on 2022-09-19 to hide this button when this package is not supported
			if (Calendar_Module_Model::canCreateActivity('Task')) {
				$CalendarActionLinks[] = array(
						'linktype' => 'DETAILVIEW',
						'linklabel' => 'LBL_ADD_TASK',
						'linkurl' => $recordModel->getCreateTaskUrl(),
						'linkicon' => ''
				);
			}
			// End Hieu Nguyen
		}

        // Disable Send SMS button in Account DetailView by Hieu Nguyen on 2020-11-09 as these action should be done in specific customer modules
		/*$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if(!empty($SMSNotifierModuleModel) && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSMSOTT("index.php?module='.$this->getModule()->getName().
								'&view=MassActionAjax&mode=showSendSMSOTTModal","SMSNotifier");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}*/
		
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerTransferOwnership("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

        foreach($CalendarActionLinks as $basicLink) {
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		return $linkModelList;
	}
}
