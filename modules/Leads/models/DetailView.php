<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// Modified by Hieu Nguyen on 2020-11-11 to make this class extends from Contacts DetailView
class Leads_DetailView_Model extends Contacts_DetailView_Model {

	public function getDetailViewLinks($linkParams) {
		require_once('libraries/ArrayUtils/ArrayUtils.php');
        $detailViewLinks = parent::getDetailViewLinks($linkParams);
        $currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $moduleModel = $this->getModule();
        $moduleName = $moduleModel->getName();
        $recordModel = $this->getRecord();
        $recordId = $recordModel->getId();

        // Convert
		if (
            !$recordModel->isLeadConverted() 
            && Users_Privileges_Model::isPermitted($moduleName, 'ConvertLead', $recordId) 
            && Users_Privileges_Model::isPermitted($moduleName, 'EditView', $recordId)
        ) {
			$convertLeadLink = [
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_CONVERT_LEAD',
				'linkurl' => 'javascript:Leads_Detail_Js.convertLead("'. $recordModel->getConvertLeadUrl() .'");', // Modified by Phu Vo on 2021.03.27 to remove unusued param
				'linkicon' => ''
            ];

			$detailViewLinks['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($convertLeadLink);
		}

		// Hide button transfer from Lead & Target DetailView
		foreach ($detailViewLinks['DETAILVIEW'] as $index => $link) {
			if ($link->getLabel() == 'LBL_TRANSFER_OWNERSHIP') {
				unset($detailViewLinks['DETAILVIEW'][$index]);
			}
		}
		
		return $detailViewLinks;
	}
}
