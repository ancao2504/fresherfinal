<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_ListAjax_View extends Vtiger_List_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
		$this->exposeMethod('showSearchResults');
		$this->exposeMethod('ShowListColumnsEdit');
		$this->exposeMethod('showSearchResultsWithValue');
		$this->exposeMethod('searchAll');
	}

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function showSearchResults(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$listMode = $request->get('listMode');
		if(!empty($listMode)) {
			$request->set('mode', $listMode);
		}

		$customView = new CustomView();
		$this->viewName = $customView->getViewIdByName('All', $moduleName);

		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORD_ACTIONS', $this->getRecordActionsFromModule($moduleModel));
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$moduleFields = $moduleModel->getFields();
		$fieldsInfo = array();
		foreach($moduleFields as $fieldName => $fieldModel){
			$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
		}
		$viewer->assign('ADV_SEARCH_FIELDS_INFO', json_encode($fieldsInfo));
		if($request->get('_onlyContents',false)){
			$viewer->view('UnifiedSearchResultsContents.tpl',$moduleName);
		}else{
			$viewer->view('UnifiedSearchResults.tpl', $moduleName);
		}
	}

	public function ShowListColumnsEdit(Vtiger_Request $request){
        $GLOBALS['current_view'] = 'detail';
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$cvId = $request->get('cvid');
		$cvModel = CustomView_Record_Model::getInstanceById($cvId);

		$moduleModel = Vtiger_Module_Model::getInstance($request->get('source_module'));
		$recordStructureModel = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_FILTER);
		$recordStructure = $recordStructureModel->getStructure();

		$cvSelectedFields = $cvModel->getSelectedFields();

		$cvSelectedFieldModelsMapping = array();
		foreach ($recordStructure as $blockFields) {
			foreach ($blockFields as $field) {
				$cvSelectedFieldModelsMapping[$field->getCustomViewColumnName()] = $field;
			}
		}

		$selectedFields = array();
		foreach ($cvSelectedFields as $cvFieldName) {
            // Modified by Hieu Nguyen on 2021-04-19 to fix bug ListView column edit crash when one of selected fields has been deleted permanently or removed from DetailView layout
            $fieldModel = $cvSelectedFieldModelsMapping[$cvFieldName];
            if ($fieldModel) $selectedFields[$cvFieldName] = $fieldModel;
            // End Hieu Nguyen
		}

		$viewer->assign('CV_MODEL',$cvModel);
		$viewer->assign('RECORD_STRUCTURE',$recordStructure);
		$viewer->assign('SELECTED_FIELDS',$selectedFields);
		$viewer->assign('MODULE',$moduleName);
		$viewer->view('ListColumnsEdit.tpl',$moduleName);
	}

    // Modified this function by Hieu Nguyen on 2020-07-03 to support global search with enabled modules and enabled fields from config
	public function searchAll(Vtiger_Request $request) {
        global $globalSearchConfig;
		$keyword = $request->get('value');

        // Create common object outside the loop to save memory
        $customView = new CustomView();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', $globalSearchConfig['page_limit']);

        // Get search result
        $searchResult = Vtiger_GlobalSearch_Helper::search($keyword);
		$matchingRecords = [];

		foreach ($searchResult as $moduleName => $searchResult) {
            // Pepair for paging
			$cvId = $customView->getViewIdByName('All', $moduleName);
			$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
			$listViewModel->listViewHeaders = $listViewModel->getListViewHeaders();
			$listViewModel->set('pageNumber', 1);

            $fakeListForPaging = array_fill(0, $searchResult['total_count'], '');
			$listViewPagingModel = clone $pagingModel;
			$listViewPagingModel->calculatePageRange($fakeListForPaging);
			$listViewModel->pagingModel = $listViewPagingModel;
			$listViewModel->recordsCount = $searchResult['total_count'];

            // Fetch full record data
			foreach ($searchResult['records'] as $record) {
                $recordId = $record['id'];
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				$recordModel->setRawData(array_merge($record, $recordModel->getData()));

                // Render display value for display columns
				foreach ($listViewModel->listViewHeaders as $fieldName => $fieldModel) {
					$recordModel->set($fieldName, $fieldModel->getDisplayValue($recordModel->get($fieldName)));
				}

				$listViewModel->listViewEntries[$recordId] = $recordModel;
			}

			$matchingRecords[$moduleName] = $listViewModel;
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('SEARCH_VALUE', $keyword);
		$viewer->assign('PAGE_NUMBER', 1);
		$viewer->assign('MATCHING_RECORDS', $matchingRecords);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());

		echo $viewer->view('SearchResults.tpl', '', true);
	}

	public function showSearchResultsWithValue(Vtiger_Request $request) {
		// Added by Hieu Nguyen on 2021-11-22 to bypass permission for Global Search
		vglobal('current_user', Users::getRootAdminUser());
		// End Hieu Nguyen
		
		$moduleName = $request->getModule();
		$pageNumber = $request->get('page');
		$searchValue = $request->get('value');
		$recordsCount = $request->get('recordsCount');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
        // Modified by Hieu Nguyen on 2020-07-13
        $searchFields = Vtiger_GlobalSearch_Helper::getSearchFieldsByModule($moduleName);
        $searchParams = Vtiger_GlobalSearch_Helper::getSearchParams($searchFields, $searchValue);
        // End Hieu Nguyen

		$request->set('search_params', $searchParams);
		$request->set('orderby', $moduleModel->basetableid);

		// Modified by Hieu Nguyen on 2020-07-03
        global $globalSearchConfig;
        $pageLimit = $globalSearchConfig['page_limit'];
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('limit', $pageLimit);
		$pagingModel->set('page', $pageNumber);
        // End Hieu Nguyen

		$range = array();
		$previousPageRecordCount = (($pageNumber-1)*$pageLimit);
		$range['start'] = $previousPageRecordCount+1;
		$range['end'] = $previousPageRecordCount+$pageLimit;
		$pagingModel->set('range', $range);
		$this->pagingModel = $pagingModel;

		$customView = new CustomView();
		$this->viewName = $customView->getViewIdByName('All', $moduleName);

		$viewer = $this->getViewer($request);
		$this->initializeListViewContents($request, $viewer);

        // Added by Hieu Nguyen on 2020-10-13 to fix bug keyword cached in ListView after click button next page in global search list
        unset($_SESSION[$moduleName .'_'. $this->viewName]);
        // End Hieu Nguyen

		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('RECORDS_COUNT', $recordsCount);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('ModuleSearchResults.tpl', $moduleName);
	}
}