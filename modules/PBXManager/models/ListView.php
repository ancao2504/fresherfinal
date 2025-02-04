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
 * PBXManager ListView Model Class
 */

class PBXManager_ListView_Model extends Vtiger_ListView_Model {
    
    /**
    * Overrided to remove add button 
    */
    public function getBasicLinks(){
		$basicLinks = array();
		return $basicLinks;
	}
    
     
    /**
    * Overrided to remove Mass Edit Option 
    */
    public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module='.$moduleModel->get('name').'&action=MassDelete");',
				'linkicon' => ''
			);
        
		    foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		    }
		}

		return $links;
	}
    
    // Removed function getListViewEntries() here by Hieu Nguyen on 2020-09-08 to use the default logic from Vtiger ListView model
}
