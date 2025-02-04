<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartActions_Action extends Vtiger_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('pinChartToDashboard');
		$this->exposeMethod('unpinChartFromDashboard');
        $this->exposeMethod('addChartToDashboard'); // Added by Hieu Nguyen on 2020-03-30 to add chart from custom report to dashboard
	}

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Reports_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
    
    // Implemented by Hieu Nguyen on 2020-03-30 to add chart from custom report to dashboard
    public function addChartToDashboard(Vtiger_Request $request) {
        global $adb, $current_user;
        $reportId = $request->get('reportId');
        $widgetTitle = $request->get('title');
        $dashBoardTabId = $request->get('dashBoardTabId');
        $data = $request->getRaw('data');

        if (empty($dashBoardTabId)) {
            $dasbBoardModel = Vtiger_DashBoard_Model::getInstance('Reports');
            $userDefaultTab = $dasbBoardModel->getUserDefaultTab($current_user->id);
            $dashBoardTabId = $userDefaultTab['id'];
        }

        // TODO: Remove this check when we can add multiple widget into the sample dashboard tab
        if (!empty($reportId) && !empty($dashBoardTabId)) {
            $sql = "SELECT 1 FROM vtiger_module_dashboard_widgets WHERE reportid = ? AND dashboardtabid = ?";
            $exists = $adb->getOne($sql, [$reportId, $dashBoardTabId]);
            
            if ($exists) {
                $result = ['success' => true];
                $response = new Vtiger_Response();
                $response->setResult($result);
                $response->emit();
                exit;
            }
        }

        // Custom by Phuc on 2020.05.18 to add size params for action
        $size = [];
        
        if (isset($data['size'])) {
            $size = $data['size'];
            unset($data['size']);
        }
        
        $sql = "INSERT INTO vtiger_module_dashboard_widgets(userid, reportid, linkid, name_en, name_vn, dashboardtabid, data, size, type) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $param = [$current_user->id, $reportId, 0, $widgetTitle, $widgetTitle, $dashBoardTabId, json_encode($data), json_encode($size), 'custom'];
        $result = $adb->pquery($sql, $param);
        // Ended custom by Phuc

        $result = ['success' => true];
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
