<?php

/*
	View RecordingPopup
	Author: Hieu Nguyen
	Date: 2018-12-26
	Purpose: to render recording popup from the backend
*/

class PBXManager_RecordingPopup_View extends CustomView_Base_View {

	function checkPermission(Vtiger_Request $request) {
        return true;
	}

	function process(Vtiger_Request $request) {
        $callLogModel = Vtiger_Record_Model::getInstanceById($request->get('call_log_id'), 'Calendar');
        $pbxCallModel = Vtiger_Record_Model::getInstanceByConditions('PBXManager', ['sourceuuid' => $callLogModel->get('pbx_call_id')]);

		$viewer = $this->getViewer($request);
        $viewer->assign('CALL_LOG_MODEL', $callLogModel);
        $viewer->assign('PBX_CALL_MODEL', $pbxCallModel);
        
        $viewer->display('modules/PBXManager/tpls/RecordingPopup.tpl');
    }
}