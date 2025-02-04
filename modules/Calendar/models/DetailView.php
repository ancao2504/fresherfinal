<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	// Modified by Hieu Nguyen on 2022-01-14 to display related tabs on Calendar DetailView
	public function getDetailViewRelatedLinks() {
		$relatedLinks = parent::getDetailViewRelatedLinks();

		foreach ($relatedLinks as $i => $link) {
			// Remove related tab Contacts
			if ($link['linklabel'] == 'Contacts') {
				unset($relatedLinks[$i]);
			}
		}

		return $relatedLinks;
	}

	/** Implemented by Phu Vo on 2020.07.27 */
	public function getDetailViewLinks($linkParams) {
		$recordModel = $this->getRecord();
		$linkModelList = parent::getDetailViewLinks($linkParams);

		// Remove detail view edit button on uneditable record
		if ($linkModelList['DETAILVIEWBASIC'] != null && !$recordModel->isEditable()) {
			$linkModelList['DETAILVIEWBASIC'] = removeButtons($linkModelList['DETAILVIEWBASIC'], ['LBL_EDIT']);
		}

		// Remove detail view delete button on undeleteable record
		if ($linkModelList['DETAILVIEW'] != null && !$recordModel->isDeletable()) {
			$linkModelList['DETAILVIEW'] = removeButtons($linkModelList['DETAILVIEW'], ['LBL_DELETE']);
		}

		// Added by Hieu Nguyen on 2022-09-05 to hide button Duplicate when create activity action is not supported
		if (!Calendar_Module_Model::canCreateActivity($recordModel->get('activitytype'))) {
			$linkModelList['DETAILVIEW'] = removeButtons($linkModelList['DETAILVIEW'], ['LBL_DUPLICATE']);
		}
		// End Hieu Nguyen

		return $linkModelList;
	}
}
