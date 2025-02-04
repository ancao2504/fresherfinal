<?php

/*
    Settings_CronTasks_SeviceAjax_Action
    Author: Hieu Nguyen
    Date: 2018-08-18
    Purpose: handle logic to control services
*/

class Settings_CronTasks_ServiceAjax_Action extends Settings_Vtiger_Index_Action {

    public function __construct() {
        $this->exposeMethod('resetService');
        $this->exposeMethod('testService');
    }

	public function checkPermission(Vtiger_Request $request) {
		parent::checkPermission($request);

        $recordId = $request->get('record');
        
		if(!$recordId) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

    static function resetService(Vtiger_Request $request) {
        $serviceId = $request->get('record');
		$qualifiedModuleName = $request->getModule(false);

		$recordModel = Settings_CronTasks_Record_Model::getInstanceById($serviceId, $qualifiedModuleName);
		$recordModel->reset();

        $response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
    }

    static function testService(Vtiger_Request $request) {
        $serviceId = $request->get('record');

		$service = Vtiger_Cron::getInstanceById($serviceId);
        $handlerFile = $service->getHandlerFile();
		checkFileAccess($handlerFile);		
		require_once($handlerFile);

        $response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
    }
}