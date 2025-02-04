<?php

/*
	Data_Model
	Author: Hieu Nguyen
	Date: 2021-08-03
	Purpose: to provide util function to manipulate with the data
*/

class Potentials_Data_Model {

	// Get data for progress bar
	static function getDataForProgressBar($recordModel) {
		global $adb;
        $allNodes = Vtiger_Util_Helper::getPickListValues('sales_stage');
		$excludeNodes = ['Closed Won', 'Closed Lost'];
		$allNodes = array_diff($allNodes, $excludeNodes);

		$sql = "SELECT mtd.prevalue, mtd.postvalue, mtb.whodid, mtb.changedon
            FROM vtiger_modtracker_detail AS mtd 
            INNER JOIN vtiger_modtracker_basic AS mtb ON (mtb.id = mtd.id AND mtb.crmid = ?)
            INNER JOIN vtiger_sales_stage AS st ON (st.sales_stage = mtd.postvalue)
            WHERE mtd.fieldname = 'sales_stage' AND st.sortorderid <= (
                SELECT sortorderid FROM vtiger_sales_stage WHERE sales_stage = ?
            )
            ORDER BY st.sortorderid;";
		$result = $adb->pquery($sql, [$recordModel->getId(), $recordModel->get('sales_stage')]);
		$visitedNodes = [];

		while ($row = $adb->fetchByAssoc($result)) {
            $nodeInfo = [
				'node_value' => vtranslate($row['postvalue'], 'Potentials'),
				'prev_value' => vtranslate($row['prevalue'], 'Potentials'),
				'updated_by' => getUserFullName($row['whodid']),
				'updated_time' => DateTimeField::convertToUserFormat($row['changedon']),
			];

			$params = [
				'%node_value' => $nodeInfo['node_value'],
				'%prev_value' => $nodeInfo['prev_value'],
				'%updated_by' => $nodeInfo['updated_by'],
				'%updated_time' => $nodeInfo['updated_time'],
			];
			$nodeInfo['tooltip'] = htmlentities(vtranslate('LBL_PROGRESS_BAR_VISITED_NODE_TOOLTIP', 'Potentials', $params));
			
			$visitedNodes[$row['postvalue']] = $nodeInfo;
		}

		$data = [
			'all_nodes' => $allNodes, 
			'visited_nodes' => $visitedNodes
		];

		if ($recordModel->get('potentialresult') == 'Closed Won' && $recordModel->get('sales_stage') != 'Closed Won') {
			$params = ['%sales_stage' => vtranslate($recordModel->get('sales_stage'), 'Potentials')];
			$data['won_result_tooltip'] = vtranslate('LBL_PROGRESS_BAR_WON_RESULT_TOOLTIP', 'Potentials', $params);
		}

		if ($recordModel->get('potentialresult') == 'Closed Lost' && $recordModel->get('sales_stage') != 'Closed Lost') {
			$params = [
				'%sales_stage' => vtranslate($recordModel->get('sales_stage'), 'Potentials'),
				'%lost_reason' => vtranslate($recordModel->get('potentiallostreason'), 'Potentials')
			];
			$data['lost_result_tooltip'] = vtranslate('LBL_PROGRESS_BAR_LOST_RESULT_TOOLTIP', 'Potentials', $params);
		}

		return $data;
	}
}