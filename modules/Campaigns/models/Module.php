<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Module_Model extends Vtiger_Module_Model {

	// Added by Phu Vo on 2021.09.25 base on UI UX Request
	public function isQuickCreateSupported() {
		return false;
	}

	// Implemented by Hieu Nguyen on 2022-10-24
	public function getModuleBasicLinks() {
		require_once('libraries/ArrayUtils/ArrayUtils.php');
		$basicLinks = parent::getModuleBasicLinks();
		
		if (Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()) {	// Modified by Vu Mai on 2023-02-13 to update logic only admins or Telelsales Managers can be created
			// Add new buttons right before button Create Campaign
			array_insert_before_index($basicLinks, 0, [
				'linktype' => 'BASIC',
				'linklabel' => 'LBL_CREATE_TELESALES_CAMPAIGN',
				'linkurl' => $this->getCreateRecordUrl() . '&campaigntype=Telesales',
				'linkicon' => 'fa-plus'
			]);
		}

		return $basicLinks;
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		// Comment out by Phu Vo to display converted Lead on Campaign related tab
		// if ($relatedModule === 'Leads') {
		// 	$specificQuery = 'AND vtiger_leaddetails.converted = 0';
		// 	return $specificQuery;
		// }
		return parent::getSpecificRelationQuery($relatedModule);
 	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Leads', 'Accounts', 'Contacts'))) {
			switch($sourceModule) {
				case 'Leads'		: $tableName = 'vtiger_campaignleadrel';		$relatedFieldName = 'leadid';		break;
				case 'Accounts'		: $tableName = 'vtiger_campaignaccountrel';		$relatedFieldName = 'accountid';	break;
				case 'Contacts'		: $tableName = 'vtiger_campaigncontrel';		$relatedFieldName = 'contactid';	break;
			}

			$condition = " vtiger_campaign.campaignid NOT IN (SELECT campaignid FROM $tableName WHERE $relatedFieldName = '$record')";
			$pos = stripos($listQuery, 'where');

			if ($pos) {
				$split = preg_split('/where/i', $listQuery);

                // Added by Hieu Nguyen on 2019-06-21 to fix bug filter error when apply subquery with sub WHERE
                $split = fixSplittedQueryPartsByWhere($split);
                // End Hieu Nguyen

				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function is used to give links in the All menu bar
	 */
	public function getQuickMenuModels() {
		if ($this->isEntityModule()) {
			$moduleName = $this->getName();
			$listViewModel = Vtiger_ListView_Model::getCleanInstance($moduleName);
			$basicListViewLinks = $listViewModel->getBasicLinks();
		}

		if ($basicListViewLinks) {
			foreach ($basicListViewLinks as $basicListViewLink) {
				if (is_array($basicListViewLink)) {
					$links[] = Vtiger_Link_Model::getInstanceFromValues($basicListViewLink);
				} else if (is_a($basicListViewLink, 'Vtiger_Link_Model')) {
					$links[] = $basicListViewLink;
				}
			}
		}
		return $links;
	}

	/*
	 * Function to get supported utility actions for a module
	 */
	function getUtilityActionsNames() {
		return array();
	}

}