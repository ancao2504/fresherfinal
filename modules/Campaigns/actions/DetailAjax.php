<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_DetailAjax_Action extends Vtiger_BasicAjax_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getMessageStatisticsDetail'); // Added by Phu Vo on 2020.11.17
		$this->exposeMethod('getEmailLogsDetail'); // Added by Phu Vo on 2020.11.17
		$this->exposeMethod('exportMessageStatisticExcel'); // Added by Phu Vo on 2020.11.17
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to get related Records count from this relation
	 * @param <Vtiger_Request> $request
	 * @return <Number> Number of record from this relation
	 */
	public function getRecordsCount(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$count =  $relationListView->getRelatedEntriesCount();
		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

    /** Implemented by Phu Vo on 2020.11.17 */
    public function getMessageStatisticsDetail(Vtiger_Request $request) {
        $widgetModel = new Campaigns_MessageStatisticsWidget_Model();
        $result = $widgetModel->getWidgetDataTableData($request->getAll());
        echo json_encode($result);
        exit;
	}

    /** Implemented by Phu Vo on 2020.11.17 */
    public function getEmailLogsDetail(Vtiger_Request $request) {
        $widgetModel = new Campaigns_EmailLogsWidget_Model();
        $result = $widgetModel->getWidgetDataTableData($request->getAll());
        echo json_encode($result);
        exit;
	}
	
	/** Implemented by Phu Vo on 2020.11.17 */
	public function exportMessageStatisticExcel(Vtiger_Request $request) {
		$params = [
			'campaign_id' => $request->get('campaign_id'),
			'smsnotifier_id' => $request->get('smsnotifier_id'),
			'status' => $request->get('status'),
		];

		$excelFile = Campaigns_Logic_Helper::getCustomerExcelFile($params);

		header("Location: $excelFile");
	}
}