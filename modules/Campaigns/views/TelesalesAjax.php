<?php

/*
	File: TelesalesAjax.php
	Author: Hieu Nguyen
	Date: 2022-11-09
	Purpose: provide ajax functions for New/Edit Telesales Campaign forms
*/

require_once('include/utils/LangUtils.php');

class Campaigns_TelesalesAjax_View extends CustomView_Base_View {

	function __construct() {
		$this->exposeMethod('getMKTListsTableRows');
		$this->exposeMethod('getDataStatistics');
		$this->exposeMethod('getUserListTableRow');
		$this->exposeMethod('getEstimationResult');
	}

	function checkPermission(Vtiger_Request $request) {
		checkAccessForbiddenFeature('TelesalesCampaign');
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$userPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if (!$userPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function getMKTListsTableRows(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$mktListIds = $request->get('mkt_list_ids');
		$campaignId = $request->get('campaign_id');
		$mktListsInfo = [];

		if (empty($campaignId)) {
			$mktListsInfo = Campaigns_Telesales_Model::getMKTListsInfo($mktListIds);
		}
		else {
			foreach ($mktListIds as $mktListId) {
				$mktListsInfo[] = Campaigns_Telesales_Model::getMKTListInfoWithStatistics($mktListId, $campaignId);
			}
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$rowsHtml = '';

		foreach ($mktListsInfo as $info) {
			$viewer->assign('MKT_LIST_INFO', $info);

			if (empty($campaignId)) {
				$rowHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/New/TableMKTListsRowTemplate.tpl');
			}
			else {
				$rowHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/Edit/TableMKTListsRowTemplate.tpl');
			}

			$rowsHtml .= $rowHtml;
		}
		
		echo $rowsHtml;
	}

	public function getDataStatistics(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$mktListIds = $request->get('mkt_list_ids');
		$campaignId = $request->get('campaign_id');
		$result = Campaigns_Telesales_Model::getDataStatistics($mktListIds, $campaignId);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RESULT', $result);
		$viewer->assign('MKT_LIST_IDS', $mktListIds);

		if (empty($campaignId)) {
			$resultHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/New/TableDataStatistics.tpl');
		}
		else {
			$resultHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/Edit/TableDataStatistics.tpl');
		}

		echo $resultHtml;
	}

	public function getUserListTableRow(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$userId = $request->get('user_id');
		$campaignId = $request->get('campaign_id');
		$userInfo = Campaigns_Telesales_Model::getSelectedUserInfoWithStatistics($userId, $campaignId);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('USER_INFO', $userInfo);
		$rowHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/Edit/TableUserListRowTemplate.tpl');
		echo $rowHtml;
	}

	public function getEstimationResult(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$campaignId = $request->get('campaign_id');
		$mktListIds = $request->get('mkt_list_ids');
		$selectedUserIds = $request->get('selected_user_ids');
		$distributionOptions = $request->get('distribution_options');
		$result = Campaigns_Telesales_Model::distribute($campaignId, $mktListIds, $selectedUserIds, $distributionOptions);

		// Get user info
		foreach ($result['detail_by_user'] as $userId => $info) {
			$userInfo = Campaigns_Telesales_Model::getUserInfo($userId);
			$result['detail_by_user'][$userId]['full_name'] = $userInfo['full_name'];
			$result['detail_by_user'][$userId]['email'] = $userInfo['email'];
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('RESULT', $result);

		if (empty($campaignId)) {
			$resultHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/New/TableEstimationResult.tpl');
		}
		else {
			// Calculate footer total
			$footerTotal = ['current_data_count' => 0, 'final_data_count' => 0];

			foreach ($result['detail_by_user'] as $info) {
				$footerTotal['current_data_count'] += $info['current_data_count'];
				$footerTotal['final_data_count'] += $info['final_data_count'];
			}

			$viewer->assign('FOOTER_TOTAL', $footerTotal);
			$resultHtml = $viewer->fetch('modules/Campaigns/tpls/Telesales/Edit/TableEstimationResult.tpl');
		}
		echo $resultHtml;
	}
}