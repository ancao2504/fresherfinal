<?php

/*
	File: EditTelesalesCampaignForm.php
	Author: Hieu Nguyen
	Date: 2022-10-24
	Purpose: render edit Telesales Campaign form in wizard mode
*/

require_once('include/utils/LangUtils.php');

class Campaigns_EditTelesalesCampaignForm_View extends CustomView_Base_View {

	function __construct() {
		parent::__construct($isFullView = true);
	}

	function checkPermission(Vtiger_Request $request) {
		checkAccessForbiddenFeature('TelesalesCampaign');
		$moduleName = $request->get('module');
		$record = $request->get('record');

		if (!Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()) {	// Modified by Vu Mai on 2023-02-13 to update logic decentralize redistribute
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function getPageTitle(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		return vtranslate('LBL_EDIT_TELESALES_CAMPAIGN_WIZARD_FORM_TITLE', $moduleName);
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$recordId = $request->get('record');

		// Check campaign
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		if ($recordModel->get('campaigntype') != 'Telesales') {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RECORD_ID', $recordId);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('SELECTED_MKT_LISTS', Campaigns_Telesales_Model::getSelectedMKTListsToEdit($recordId));
		$viewer->assign('SELECTED_USERS', Campaigns_Telesales_Model::getSelectedUsersToEdit($recordId));
		$viewer->assign('DISTRIBUTION_OPTIONS', Campaigns_Telesales_Model::getDistributionOptions($recordId));
		$viewer->display('modules/Campaigns/tpls/Telesales/Edit/Form.tpl');
	}
}