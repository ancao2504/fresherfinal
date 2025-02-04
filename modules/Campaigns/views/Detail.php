<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_Detail_View extends Vtiger_Detail_View {

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
				'modules.Vtiger.resources.List',
				"modules.$moduleName.resources.List",
				'modules.CustomView.resources.CustomView',
				"modules.$moduleName.resources.CustomView",
				"modules.Emails.resources.MassEdit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	/**
	 * Implemented by Phu Vo on 2020.12.04
	 */
	function showModuleBasicView($request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!$this->record){
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();

		$viewer = $this->getViewer($request);

		// Modified by Hieu Nguyen on 2022-06-28 to show campaign log
		if (Campaigns_Logic_Helper::hasCampaignLog($recordModel->get('campaigntype'))) {
			if ($recordModel->get('campaigns_purpose') == 'promotion') {
				$emailLogsModel = new Campaigns_EmailLogsWidget_Model();
				$viewer->assign('EMAIL_LOGS_WIDGET_MODEL', $emailLogsModel);
			}

			$messageStatisticsModel = new Campaigns_MessageStatisticsWidget_Model();
			$viewer->assign('MESSAGE_STATISTICS_WIDGET_MODEL', $messageStatisticsModel);
		}
		// End Hieu Nguyen

		// Modified by Vu Mai on 2022-11-22 to show telesales campaign summary
		if ($recordModel->get('campaigntype') == "Telesales") {
			$viewer->assign('TELESALES_CAMPAIGN_SUMMARY', $this->getTelesaleCampaignSummary($request));
		}
		// End Vu Mai

		parent::showModuleBasicView($request);
	}

	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request) {
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if($currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');

			$pageNumber = $request->get('page');
			if(empty ($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('limit', 10);

			if(!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities('', $pagingModel, 'all', $recordId);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);

			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}

	// Added by Vu Mai on 2022-11-30 to render telesale campaign summary view
	public function getTelesaleCampaignSummary(Vtiger_Request $request) {
		$module = $request->getModule();
		$record = $request->get('record');

		// Prepare date range
		$campaignInfo = Campaigns_Telesales_Model::getCampaignInfo($record);
		$dateRange = [];
		$dateRange['from'] = date('Y-m-d', strtotime($campaignInfo['start_date']));
		$dateRange['to'] = date('Y-m-d', strtotime($campaignInfo['end_date']));
		
		// Get customer statistics detail
		$customerStatistics = CPTelesales_Telesales_Model::getCustomerStatistics($record, $campaignInfo['purpose']);

		// Get call statistics detail
		$callStatistics = CPTelesales_Telesales_Model::getCallStatistics($record, $dateRange);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $record);
		$viewer->assign('MODULE', $module);
		$viewer->assign('CUSTOMER_STATISTICS', $customerStatistics);
		$viewer->assign('CALL_STATISTICS', $callStatistics);
		return $viewer->fetch('modules/Campaigns/tpls/TelesalesCampaignSummary.tpl');
	}
	// End Vu Mai
}