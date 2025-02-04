<?php

/**
 * Name: CampaignHandler.php
 * Author: Phu Vo
 * Date: 2020.12.03
 */

class CampaignHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if($entityData->getModuleName() != 'Campaigns') return;
		
		if($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
			$this->calculateRoi($entityData);
		}

		if($eventName === 'vtiger.entity.aftersave') {
			// Add handler functions here
			$this->saveTelesalesCampaignDistributionInfo($entityData);	// Added by Hieu Nguyen on 2022-11-10
		}

		if($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}

	function calculateRoi(&$entityData) {
		$budgetCost = floatval($entityData->get('budgetcost'));
		$expectedRevenue = floatval($entityData->get('expectedrevenue'));
		$actualCost = floatval($entityData->get('actualcost'));
		$actualRevenue = floatval($entityData->get('actual_revenue'));
		$expectedRoi = 0;
		$actualRoi = 0;

		if (!empty($budgetCost) && !empty($expectedRevenue) && $budgetCost > 0) {
			$expectedRoi = ($expectedRevenue - $budgetCost) / $budgetCost;
		}

		if (!empty($actualCost) && !empty($actualRevenue) && $actualCost > 0) {
			$actualRoi = ($actualRevenue - $actualCost) / $actualCost;
		}

		$entityData->set('expectedroi', round($expectedRoi, 2));
		$entityData->set('actualroi', round($actualRoi, 2));
	}

	// Added by Hieu Nguyen on 2022-11-10
	function saveTelesalesCampaignDistributionInfo($entityData) {
		if (isForbiddenFeature('TelesalesCampaign')) return;
		if ($entityData->get('campaigntype') != 'Telesales') return;

		if ($_REQUEST['wizard'] == 'true') {
			$campaignId = $entityData->getId();
			$request = new Vtiger_Request($_REQUEST);
			$mktListIds = $request->get('mkt_list_ids');
			$selectedUserIds = $request->get('selected_user_ids');
			$distributionOptions = $request->get('distribution_options');

			$result = Campaigns_Telesales_Model::distribute($campaignId, $mktListIds, $selectedUserIds, $distributionOptions, true);
			saveLog('PLATFORM', '[CampaignHandler::saveTelesalesCampaignDistributionInfo] Distribution result:', $result);

			Campaigns_Telesales_Model::updateSelectedUsers($campaignId, $selectedUserIds);
			Campaigns_Telesales_Model::updateDistributionOptions($campaignId, $distributionOptions);
			Campaigns_Telesales_Model::saveRelatedMKTLists($campaignId, $mktListIds);
		}
	}
}