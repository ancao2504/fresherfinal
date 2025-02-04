<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_DetailAjax_Action extends Vtiger_BasicAjax_Action{
    
    public function __construct() {
        parent::__construct();
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getUsersByDepartment'); // Added by Phuc on 2020.04.22
		$this->exposeMethod('getCampaignsByTime'); // Added by Phuc on 2020.04.22
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
		$record = $request->get('record');
		$reportModel = Reports_Record_Model::getInstanceById($record);
		$reportModel->setModule('Reports');
		$reportModel->set('advancedFilter', $request->get('advanced_filter'));
        
        $advFilterSql = $reportModel->getAdvancedFilterSQL();
        $query = $reportModel->getReportSQL($advFilterSql, 'PDF');
        $countQuery = $reportModel->generateCountQuery($query);

        $count = $reportModel->getReportsCount($countQuery);
        $response = new Vtiger_Response();
        $response->setResult($count);
        $response->emit();
    }
	
	
	// Added by Phuc on 2020.02.24 to get user by roles
	public function getUsersByDepartment(Vtiger_Request $request) {
		$addEmptyOption = $request->get('add_empty');
		$addAllOption = $request->get('add_all');

		if ($addEmptyOption === 'false' || $addEmptyOption === 0) {
			$addEmptyOption = false;
		}
		else {
			$addEmptyOption = true;
		}
		
		if ($addAllOption === 'false' || $addAllOption === 0) {
			$addAllOption = false;
		}
		else {
			$addAllOption = true;
		}

		$users = Reports_CustomReport_Helper::getUsersByDepartment($request->get('department'), $addEmptyOption, $addAllOption);

		$response = new Vtiger_Response();
        $response->setResult($users);
        $response->emit();
	}
	// Ended by Phuc

	
	// Added by Phuc on 2020.06.25 to get campaign by time for report
	public function getCampaignsByTime(Vtiger_Request $request) {
		$params = $_REQUEST;
		$period = Reports_CustomReport_Helper::getPeriodFromFilter($params, true);
		$campaigns = Campaigns_Data_Model::getAllCampaigns(true, $period['from_date'], $period['to_date']);

		$response = new Vtiger_Response();
        $response->setResult($campaigns);
        $response->emit();
	}
	// Ended by Phuc
}