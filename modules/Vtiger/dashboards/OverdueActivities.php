<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Vtiger_OverdueActivities_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$moduleName = $request->getModule();
		$page = $request->get('page');
		$linkId = $request->get('linkid');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $page);
		$pagingModel->set('limit', 10);

		// Modified by Phu Vo on 2019.06.19 to process ownerid from request
		$user = $request->get('assigned_user_id');
		if ($user) $user = Vtiger_CustomOwnerField_Helper::getOwnerIdFromRequest($user);
		// End Phu Vo

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$overDueActivities = $moduleModel->getCalendarActivities('overdue', $pagingModel, $user);

		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05
		$viewer = $this->getViewer($request);

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('ACTIVITIES', $overDueActivities);
		$viewer->assign('PAGING', $pagingModel);
		$viewer->assign('CURRENTUSER', $currentUser);
		
		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/CalendarActivitiesContents.tpl', $moduleName);
		} else {
			
			// Comment out by Phu Vo on 2019-06-17 to boost performance
			/*$sharedUsers = Calendar_Module_Model::getSharedUsersOfCurrentUser($currentUser->id);
			$sharedGroups = Calendar_Module_Model::getSharedCalendarGroupsList($currentUser->id);
			$viewer->assign('SHARED_USERS', $sharedUsers);
			$viewer->assign('SHARED_GROUPS', $sharedGroups);*/
			// End Phu Vo
			
			$viewer->view('dashboards/CalendarActivities.tpl', $moduleName);
		}
	}
}