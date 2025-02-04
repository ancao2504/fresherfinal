<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelationListView_Model extends Vtiger_RelationListView_Model {

	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Vtiger_Link_Model>
	 */
	public function getLinks() {
		$relatedLinks = parent::getLinks();
		$relationModel = $this->getRelationModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		// Added by Hieu Nguyen on 2022-12-20 to hide all buttons at subpanel MKT Lists when in the Telesales Campaign
		if ($relatedModuleName === 'CPTargetList' && $this->parentRecordModel->get('campaigntype') == 'Telesales') {
			return [];
		}
		// End Hieu Nguyen

		// Added by Phu Vo on 2019.08.07 to delete all buttons for related module CPSocialArticleLog and CPSocialMessageLog
		// Updated by Phuc on 2019.10.03 to add Leads, Contact and CPTarget
        $removeAllLinkModules = ['CPSocialArticleLog', 'CPSocialMessageLog', 'CPSocialFeedback', 'Leads', 'Contacts', 'CPTarget'];
		
		if (in_array($relatedModuleName, $removeAllLinkModules)) {
			return [];
		}
		// End delete all buttons for related module CPSocialArticleLog and CPSocialMessageLog

		// Added by Phu Vo on 2019.08.21 to remove Social Article Create button
		if ($relatedModuleName === 'CPSocialArticle') {
			foreach ($relatedLinks['LISTVIEWBASIC'] as $index => $link) {
				if ($link->linklabel === vtranslate('LBL_ADD_RECORD', 'CPSocialArticle') || $link->linklabel === 'LBL_ADD_RECORD') {
					unset($relatedLinks['LISTVIEWBASIC'][$index]);
				}
			}
		}
		// End remove Social Article Create button

		// Added by Phu Vo on 2019.09.11 to remove Marketing List Create Button
		if ($relatedModuleName === 'CPTargetList') {
			foreach ($relatedLinks['LISTVIEWBASIC'] as $index => $link) {
				if ($link->linklabel === vtranslate('LBL_ADD_RECORD', 'CPTargetList') || $link->linklabel === 'LBL_ADD_RECORD') {
					unset($relatedLinks['LISTVIEWBASIC'][$index]);
				}
			}
		}
		// End remove Marketing List Create Button

		if (array_key_exists($relatedModuleName, $relationModel->getEmailEnabledModulesInfoForDetailView())) {
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($currentUserPriviligesModel->hasModulePermission(getTabid('Emails'))) {
				$emailLink = Vtiger_Link_Model::getInstanceFromValues(array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => vtranslate('LBL_SEND_EMAIL', $relatedModuleName),
						'linkurl' => "javascript:Campaigns_RelatedList_Js.triggerSendEmail('index.php?module=$relatedModuleName&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');",
						'linkicon' => ''
				));
				$emailLink->set('_sendEmail',true);
				$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
			}
		}
		return $relatedLinks;
	}

	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$emailEnabledModulesInfo = $relationModel->getEmailEnabledModulesInfoForDetailView();

		if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo) && $relatedRecordModelsList) {
			$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT campaignrelstatus, $fieldName FROM $tableName
						INNER JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = $tableName.campaignrelstatusid
						WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).") AND campaignid = ?";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());

			$result = $db->pquery($query, $relatedRecordIdsList);
			$numOfrows = $db->num_rows($result);

			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				$relatedRecordModel = $relatedRecordModelsList[$recordId];

				$relatedRecordModel->set('status', $db->query_result($result, $i, 'campaignrelstatus'));
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}
