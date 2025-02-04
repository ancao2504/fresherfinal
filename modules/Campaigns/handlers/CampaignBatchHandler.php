<?php

/**
 * Name: CampaignBatchHandler.php
 * Author: Phu Vo
 * Date: 2020.12.03
 */

class CampaignBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if($entityDataList[0]->getModuleName() != 'Campaigns') return;

		if($eventName === 'vtiger.batchevent.save') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.afterdelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.beforerestore') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.batchevent.afterrestore') {
			// Add handler functions here
		}
	}

    // Handle process_records event
    static function processRecords(&$recordModel) {
		// Modified by Hieu Nguyen on 2022-06-28 to show campaign statistics at ListView
		if (Campaigns_Logic_Helper::hasCampaignLog($recordModel->getRaw('campaigntype'))) {
			$sendingStatistic = Campaigns_MessageStatisticsWidget_Model::getWidgetStatisticForListView(['record' => $recordModel->getId()]);
			$recordModel->set('sending_statistic', $sendingStatistic);
		}
		// End Hieu Nguyen
	}
}