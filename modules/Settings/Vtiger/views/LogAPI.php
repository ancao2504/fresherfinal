<?php
/*
	Settings_Vtiger_LogAPI_View
	Author: Tung Nguyen
	Date: 2022.06.23
	Purpose: Handle view log API
*/

class Settings_Vtiger_LogAPI_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct();
	}
	
    function checkPermission(Vtiger_Request $request) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        // Modified by Tung Nguyen
        if (!$currentUserModel->isAdminUser()) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }
    }

    public function getPageTitle(Vtiger_Request $request) {
        return vtranslate('CPLogAPI', 'CPLogAPI');
    }

	function process(Vtiger_Request $request) {
		$loggers = CPLogAPI_LoggerConfig_Model::getLoggers();

		if (empty($request->get('logger'))) {
			$request->setGlobal('logger', reset($loggers));
		}
		
		// Feftch filter
		$this->viewer->assign('API', $request->get('api'));
		$this->viewer->assign('KEYWORD', $request->get('keyword'));
		$this->viewer->assign('FROM_DATE', $request->get('from_date'));
		$this->viewer->assign('TO_DATE', $request->get('to_date'));
		$this->viewer->assign('LOGGER', $request->get('logger'));
		$this->viewer->assign('ACTOR', $request->get('actor'));

		$page = $request->get('page') ?: 1;

		$paginator = new CPLogAPI_Paginator_Helper($page);
		
		$data = $paginator->getData($request->getAll());
		$this->viewer->assign('DATA', $data);
		$this->viewer->assign('SEARCH_PARAMS', http_build_query([
			'api' => $request->get('api'),
			'keyword' => $request->get('keyword'),
			'from_date' => $request->get('from_date'),
			'to_date' => $request->get('to_date'),
			'logger' => $request->get('logger'),
			'actor' => $request->get('actor'),
			])
		);

		$resultPaginator = $paginator->getPaginator();
		$this->viewer->assign('CURRENT_PAGE', $resultPaginator['current_page']);
		$this->viewer->assign('START_PAGE', $resultPaginator['start']);
		$this->viewer->assign('END_PAGE', $resultPaginator['end']);
		$this->viewer->assign('TOTAL_PAGE', $resultPaginator['total_page']);
		$this->viewer->assign('TOTAL', $resultPaginator['total_row']);
		$this->viewer->assign('MIN', $resultPaginator['max'] ? $resultPaginator['min'] : 0);
		$this->viewer->assign('MAX', $resultPaginator['max']);
		$this->viewer->assign('SQLITE_LOGGER', $loggers);

		$this->viewer->assign('USER_LIST', $this->getUserList());
		$this->viewer->assign('SEARCHABLE_FIELDS', CPLogAPI_LoggerConfig_Model::getAllSearchableFields());

		$this->viewer->assign('ENTRY_URL', 'index.php?module=Vtiger&parent=Settings&view=LogAPI');
		$this->viewer->display('modules/Settings/Vtiger/tpls/LogAPI.tpl');
	}

	private function getUserList() {
		global $adb;

		$sql = 'SELECT id, user_name FROM vtiger_users WHERE deleted = 0 ORDER BY user_name';
		$rs = $adb->pquery($sql);

		$data = [];

		while ($row = $adb->fetchByAssoc($rs)) {
			$data[$row['id']] = $row['user_name'];
		}

		return $data;
	}
}