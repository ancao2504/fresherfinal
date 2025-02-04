<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();

		// Added by Tin Bui on 2021.01.04: Add change ticket status button
		if (Users_Privileges_Model::isPermitted('HelpDesk', 'EditView', $recordModel->getId())) {
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues([
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_UPDATE_TICKET_STATUS',
				'linkurl' => 'javascript:HelpDeskModalUtils.openStatusModal();',
				'linkicon' => ''
			]);
		}
		// Ended by Tin Bui

		$quotesModuleModel = Vtiger_Module_Model::getInstance('Faq');
		if($currentUserModel->hasModuleActionPermission($quotesModuleModel->getId(), 'CreateView')) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_CONVERT_FAQ',
				'linkurl' => $recordModel->getConvertFAQUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		return $linkModelList;
	}

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		$widgets = array();

        $documentsInstance = Vtiger_Module_Model::getInstance('Documents');
		if($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView')) {
			$createPermission = $userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'CreateView');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Documents',
					'linkName'	=> $documentsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Documents&mode=showRelatedRecords&page=1&limit=5',
					'action'	=>	($createPermission == true) ? array('Add') : array(),
					'actionURL' =>	$documentsInstance->getQuickCreateUrl()
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}

	public function getDetailViewRelatedLinks() {
		$relatedLinks = parent::getDetailViewRelatedLinks();
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();

		// Add ticket response subpanel
		$relatedLinks[] = [
			'linktype' => 'DETAILVIEWTAB',
			'linklabel' => vtranslate('LBL_TAB_REPLIES', $moduleName),
			'linkurl' => strval($recordModel->getDetailViewUrl()) . '&mode=showEmailReplies',
			'linkicon' => ''
		];
		
		// Hide module subpanel
		$hideSubpanelModules = [
			'CPTicketCommunicationLog'
		];

		foreach ($relatedLinks as $key => $relatedLink) {
            if (isset($relatedLink['relatedModuleName']) && in_array($relatedLink['relatedModuleName'], $hideSubpanelModules)) {
                unset($relatedLinks[$key]);
            }
        }

		return $relatedLinks;
	}
}
