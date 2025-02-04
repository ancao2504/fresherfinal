<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class HelpDesk_Detail_View extends Vtiger_Detail_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showRelatedRecords');
		$this->exposeMethod('showEmailReplies');
	}

	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request) {
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if($currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');

			$pageNumber = $request->get('page');
			if(empty ($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('limit', 10);

			if(!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities('', $pagingModel, 'all', $recordId);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);

			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}

	// Implemented by Tin Bui on 2022.03.16 - Extend more stylesheet
	function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = [
			"~modules/HelpDesk/resources/EmailReplies.css",
		];

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		
		return $headerCssInstances;
	}


	// Implemented by Tin Bui on 2022.03.16 - Extend more script 
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'~modules/HelpDesk/resources/EmailReplies/RepliesHistory.js',
			'~modules/HelpDesk/resources/EmailReplies/InternalCommentWidget.js',
			'~modules/HelpDesk/resources/EmailReplies/EmailReplyForm.js',
			'~modules/HelpDesk/resources/EmailReplies/StatusLogHistory.js',
			'~modules/HelpDesk/resources/EmailReplies/EmailReplies.js',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		
		return $headerScriptInstances;
	}

	// Implemented by Tin Bui on 2022.03.16 - Render email reply subpannel
	public function showEmailReplies(Vtiger_Request $request) {
        $fileUploadValidatorConfigs = HelpDesk_GeneralUtils_Helper::getFileUploadValidatorConfigs();

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_ID', $request->get('record'));
		$viewer->assign('FILE_VALIDATOR_CONFIGS', $fileUploadValidatorConfigs);

		return $viewer->fetch('modules/HelpDesk/tpls/EmailReplies/Main.tpl');
	}
}
