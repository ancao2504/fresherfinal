<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Detail_View extends Vtiger_Detail_View {
	
	
	/**
	 * Function returns related records
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showRelatedList(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');

		$requestedPage = $request->get('page');
		if(empty ($requestedPage)) {
			$requestedPage = 1;
		}

		if($relatedModuleName != "Products"  &&  $relatedModuleName != "Services") {
			return parent::showRelatedList($request);
		}

		/* Begin: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
		$moduleFields = $relatedModuleModel->getFields();
		$searchParams = $request->get('search_params');

		//-- Special support for listing prices
		$searchParamsListPrice = [];
		if ($relatedModuleName == "Products" || $relatedModuleName == "Services") {
			foreach ($searchParams as $indexGroup => $fieldListGroup) {
				foreach ($fieldListGroup as $index => $fieldSearchInfo) {
					if ('listprice' == $fieldSearchInfo[0]) {
						$searchParamsListPrice = $searchParams[$indexGroup][$index];
						unset($searchParams[$indexGroup][$index]);
					}
				}
			}
		}

		$whereCondition = Vtiger_RelatedList_View::getWhereCondition($searchParams, $relatedModuleModel, $moduleFields);

		//-- Special support for listing prices
		if (count($searchParamsListPrice) > 0) {
			$whereCondition[$searchParamsListPrice[0]] = ["vtiger_pricebookproductrel.{$searchParamsListPrice[0]}", $searchParamsListPrice[1], $searchParamsListPrice[2], $searchParamsListPrice[3]];
			$searchParams[0][] = $searchParamsListPrice;
			$searchParams[$searchParamsListPrice[0]] = [
				'fieldName' => $searchParamsListPrice[0],
				'comparator' => $searchParamsListPrice[1],
				'searchValue' => $searchParamsListPrice[2],
			];
		}
		/* End: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);

		/* Begin: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/
		if (!empty($whereCondition)) $relationListView->set('whereCondition', $whereCondition);
		/* End: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/

		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder',$sortOrder);
		}
		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);

		$parentRecordCurrencyId = $parentRecordModel->get('currency_id');
		if ($parentRecordCurrencyId) {
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);

			foreach ($models as $recordId => $recorModel) {
				$productIdsList[$recordId] = $recordId;
			}
			$unitPricesList = $relatedModuleModel->getPricesForProducts($parentRecordCurrencyId, $productIdsList);

			foreach ($models as $recordId => $recorModel) {
				$recorModel->set('unit_price', $unitPricesList[$recordId]);
			}

			$parentRecordCurrencyDetails = getCurrencySymbolandCRate($parentRecordCurrencyId);
		}

		$moduleFields = $relatedModuleModel->getFields();
		$fieldsInfo = array();
		foreach($moduleFields as $fieldName => $fieldModel){
			$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
		}

		$relationModel = $relationListView->getRelationModel();
		$relationField = $relationModel->getRelationField();

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_FIELDS_INFO', json_encode($fieldsInfo));
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relationModel->getRelationModuleModel());
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);

		if ($parentRecordCurrencyDetails) {
			$viewer->assign('PARENT_RECORD_CURRENCY_SYMBOL', $parentRecordCurrencyDetails['symbol']);
		}

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('TAB_LABEL', $request->get('tab_label'));

		/* Begin: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		/* End: Added by Kelvin Thang on 2020.11.06 issue #1893 Cannot search for products / services in the price list and next page*/

		return $viewer->view('RelatedList.tpl', $moduleName, 'true');
	}
}
