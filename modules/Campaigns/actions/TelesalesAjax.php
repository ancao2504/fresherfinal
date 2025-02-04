<?php

/*
	Action TelesalesAjax
	Author: Hieu Nguyen
	Date: 2020-11-09
	Purpose: provide ajax functions for New/Edit Telesales Campaign forms
*/

class Campaigns_TelesalesAjax_Action  extends Vtiger_Action_Controller {

	function __construct() {
		$this->exposeMethod('getTelesalesConfig');
		$this->exposeMethod('removeMKTList');
		$this->exposeMethod('getUserInfoWithStatistics');
		$this->exposeMethod('verifyTransferData');
		$this->exposeMethod('transferData');
		$this->exposeMethod('getDistributableCustomersCount');
		$this->exposeMethod('exportDuplicatedCustomersByMobileNumber');
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

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function getTelesalesConfig(Vtiger_Request $request) {
		$config = CPTelesales_Config_Helper::loadConfig();

		$response = new Vtiger_Response();
		$response->setResult(['config' => $config]);
		$response->emit();
	}

	function removeMKTList(Vtiger_Request $request) {
		$mktListId = $request->get('mkt_list_id');
		$campaignId = $request->get('campaign_id');
		$result = Campaigns_Telesales_Model::removeMKTList($mktListId, $campaignId);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	function getUserInfoWithStatistics(Vtiger_Request $request) {
		$userId = $request->get('user_id');
		$campaignId = $request->get('campaign_id');
		$userInfo = Campaigns_Telesales_Model::getSelectedUserInfoWithStatistics($userId, $campaignId);

		$response = new Vtiger_Response();
		$response->setResult(['user_info' => $userInfo]);
		$response->emit();
	}

	function verifyTransferData(Vtiger_Request $request) {
		$campaignId = $request->get('campaign_id');
		$sourceUserId = $request->get('source_user_id');
		$targetUserId = $request->get('target_user_id');
		$dataType = $request->get('data_type');
		$quotaLimit = $request->get('quota_limit');
		
		$sourceUserInfo = Campaigns_Telesales_Model::getSelectedUserInfoWithStatistics($sourceUserId, $campaignId);
		$targetUserInfo = Campaigns_Telesales_Model::getSelectedUserInfoWithStatistics($targetUserId, $campaignId);

		if (empty($sourceUserInfo) || empty($targetUserInfo)) {
			return;
		}

		$result = ['valid' => true];

		if ($dataType == 'not_called_customers') {
			$transferNumber = $request->get('transfer_number');

			if ($transferNumber == 'all') {
				$targetUserInfo['statistics']['not_called_count'] += $sourceUserInfo['statistics']['not_called_count'];
			}
			else {
				$targetUserInfo['statistics']['not_called_count'] += $transferNumber;
			}

			$newTotal = $targetUserInfo['statistics']['not_called_count'] += $targetUserInfo['statistics']['already_called_count'];
		}
		else {
			$newTotal = $targetUserInfo['statistics']['all_distributed_count'] += $sourceUserInfo['statistics']['all_distributed_count'];
		}

		if ($newTotal > $quotaLimit) {
			$result = ['valid' => false, 'new_total' => $newTotal];
		}

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	function transferData(Vtiger_Request $request) {
		$sourceUserId = $request->get('source_user_id');
		$targetUserId = $request->get('target_user_id');
		$dataType = $request->get('data_type');
		$transferNumber = $request->get('transfer_number');
		$campaignId = $request->get('campaign_id');
		$removeSourceUser = $request->get('remove_source_user');
		$result = Campaigns_Telesales_Model::transferData($campaignId, $sourceUserId, $targetUserId, $dataType, $transferNumber, $removeSourceUser == 'true');

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	function getDistributableCustomersCount(Vtiger_Request $request) {
		$mktListIds = $request->get('mkt_list_ids');
		$campaignId = $request->get('campaign_id');
		$distributableCount = Campaigns_Telesales_Model::countDistributableCustomersFromMKTLists($mktListIds, $campaignId);

		$response = new Vtiger_Response();
		$response->setResult(['distributable_count' => $distributableCount]);
		$response->emit();
	}

	function exportDuplicatedCustomersByMobileNumber(Vtiger_Request $request) {
		require_once('include/ExcelHelper.php');
		global $site_URL;
		$mktListIdsStr = $request->get('mkt_list_ids');
		$mktListIds = explode(',', $mktListIdsStr);
		$customers = Campaigns_Telesales_Model::getDuplicatedCustomersByMobileNumber($mktListIds);

		// Generate excel data
		ini_set('max_input_vars', 1000000);
		$data = [];

		// Header row
		$data[0] = [
			'Record URL',
			vtranslate('LBL_FULL_NAME', 'Leads'),
			vtranslate('Mobile', 'Leads'),
			vtranslate('Email', 'Leads'),
		];

		// Data rows
		foreach ($customers as $index => $info) {
			$rowNum = $index + 1;
			$recordUrl = "{$site_URL}/index.php?module={$info['module']}&view=Detail&record={$info['id']}";

			$data[$rowNum] = [
				['value' => $recordUrl, 'format' => 'url'],
				$info['full_name'],
				['value' => $info['mobile'], 'format' => 'string'],
				$info['email'],
			];
		}

		// Export
		$fileName = 'Telesales_Campaign_Duplicated_Customers_' . date('Y.m.d_H.i.s');
		$savedFile = ExcelHelper::exportToExcel($data, $fileName, true, true);
		header("Location: {$savedFile}");
	}
}