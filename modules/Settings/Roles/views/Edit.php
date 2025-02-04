<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Settings_Roles_Edit_View extends Settings_Roles_Index_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		$record = $request->get('record');
		$parentRoleId = $request->get('parent_roleid');
		$roleDirectlyRelated = false;

		// Added by Hieu Nguyen on 2022-03-01 to check forbidden feature
		if (empty($record)) {
			checkAccessForbiddenFeature('CreateNewRolesAndProfiles');
		}
		// End Hieu Nguyen

		if(!empty($record)) {
			$recordModel = Settings_Roles_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$recordModel = new Settings_Roles_Record_Model();
			$recordModel->setParent(Settings_Roles_Record_Model::getInstanceById($parentRoleId));
			$viewer->assign('MODE', '');
            // $roleDirectlyRelated = true; // Commented out this line by Hieu Nguyen on 2020-12-01 to boost performance
		}
		$profileId = $recordModel->getDirectlyRelatedProfileId();
		if($profileId){
			$viewer->assign('PROFILE_ID',$profileId);
			$roleDirectlyRelated = true;
		}


		$viewer->assign('PROFILE_DIRECTLY_RELATED_TO_ROLE',$roleDirectlyRelated);
		$viewer->assign('ALL_PROFILES', Settings_Profiles_Record_Model::getAll());
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->view('EditView.tpl', $qualifiedModuleName);
	}


}