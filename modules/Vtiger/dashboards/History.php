<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_History_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$LIMIT = 10;
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$historyType = $request->get('historyType');

		// Modified by Phu Vo on 2019.06.19 to process ownerid from request
		$userId = $request->get('assigned_user_id');
		if ($userId) $userId = Vtiger_CustomOwnerField_Helper::getOwnerIdFromRequest($userId);
		// End Phu Vo
            
		$page = $request->get('page');
		if(empty($page)) {
			$page = 1;
		}
		$linkId = $request->get('linkid');

		$modifiedTime = $request->get('modifiedtime');
		//Date conversion from user to database format
		if(!empty($modifiedTime)) {
			$startDate = Vtiger_Date_UIType::getDBInsertedValue($modifiedTime['start']);
			$dates['start'] = getValidDBInsertDateTimeValue($startDate . ' 00:00:00');
			$endDate = Vtiger_Date_UIType::getDBInsertedValue($modifiedTime['end']);
			$dates['end'] = getValidDBInsertDateTimeValue($endDate . ' 23:59:59');
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', $LIMIT);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$history = $moduleModel->getHistory($pagingModel, $historyType,$userId, $dates);
		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05
		$modCommentsModel = Vtiger_Module_Model::getInstance('ModComments'); 

		$viewer->assign('CURRENT_USER', $currentUser);
		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('HISTORIES', $history);
		$viewer->assign('PAGE', $page);
		$viewer->assign('HISTORY_TYPE', $historyType); 
		$viewer->assign('NEXTPAGE', ($pagingModel->get('historycount') < $LIMIT)? 0 : $page+1);
		$viewer->assign('COMMENTS_MODULE_MODEL', $modCommentsModel);

		$userCurrencyInfo = getCurrencySymbolandCRate($currentUser->get('currency_id'));
		$viewer->assign('USER_CURRENCY_SYMBOL', $userCurrencyInfo['symbol']);
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/HistoryContents.tpl', $moduleName);
		} else {
			
			// Comment out by Phu Vo on 2019-06-17 to boost performance
			/* $accessibleUsers = $currentUser->getAccessibleUsers();
			$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);*/
			// End Phu Vo

			// Added by Phu Vo on 2019.06.18 to process filter with custom owner field
			$ownerFieldModel = Vtiger_Field_Model::getInstance('assigned_user_id', Vtiger_Module_Model::getInstance('Accounts'));
			$ownerUITypeModel = Vtiger_Base_UIType::getInstanceFromField($ownerFieldModel);

			$viewer->assign('OWNER_FIELD_MODEL', $ownerFieldModel);
			$viewer->assign('OWNER_UITYPE_MODEL', $ownerUITypeModel);
			// End Phu Vo

			$viewer->view('dashboards/History.tpl', $moduleName);
		}
	}
}
