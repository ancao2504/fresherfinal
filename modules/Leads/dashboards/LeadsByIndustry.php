<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Leads_LeadsByIndustry_Dashboard extends Vtiger_IndexAjax_View {
    
    function getSearchParams($value,$assignedto,$dates) {
        $listSearchParams = array();
        $conditions = array(array('industry','e',  decode_html(urlencode(escapeSlashes($value)))));
        if($value == vtranslate('LBL_BLANK', 'Leads')){
            $conditions = array(array('industry','y'));
		}
		
		// Modified by Phuc on 2020.02.04
        if ($assignedto != 'all' && !empty($assignedto)) {
            if (strpos($assignedto, ':')) {
				$assignedto = explode(':', $assignedto);
				$assignedto = $assignedto[1];
			}
			
			$ownerType = vtws_getOwnerType($assignedto);

            if ($ownerType == 'Users')
				array_push($conditions, array('assigned_user_id', 'c', 'Users:' . $assignedto));
            else{
                array_push($conditions, array('assigned_user_id', 'c', 'Groups:' . $assignedto));
            }
		}
		// Ended by Phuc

		// Add by Phuc on 2020.02.19 to add new condition for status
		array_push($conditions, array('leadstatus', 'n', 'Converted'));
		// Ended by Phuc
		
        if(!empty($dates)){
            array_push($conditions,array('createdtime','bw',$dates['start'].' 00:00:00,'.$dates['end'].' 23:59:59'));
        }
        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');
		$data = $request->get('data');

		$dates = $request->get('createdtime');
		
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		// Modified by Phu Vo on 2019.06.19 to process ownerid from request
		$user = $request->get('assigned_user_id');
		if ($user) $user = Vtiger_CustomOwnerField_Helper::getOwnerIdFromRequest($user);

		$data = $moduleModel->getLeadsByIndustry($user, $dates);
		// End Phu Vo
		
        $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
        for($i = 0;$i<count($data);$i++){
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i][2], $request->get('assigned_user_id'), $request->get('dateFilter')).'&nolistcache=1'; // Updated by Phuc on 2020.02.04
        }

		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05

		//Include special script and css needed for this widget

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('CURRENTUSER', $currentUser);

		// Comment out by Phu Vo on 2019-06-17 to boost performance
		/*$accessibleUsers = $currentUser->getAccessibleUsersForModule('Leads');
		$viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);*/
		// End Phu

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/LeadsByIndustry.tpl', $moduleName);
		}
	}
}