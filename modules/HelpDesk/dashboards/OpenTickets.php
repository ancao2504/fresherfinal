<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDesk_OpenTickets_Dashboard extends Vtiger_IndexAjax_View {
    
    function getSearchParams($value) {
        $listSearchParams = array();
		$conditions = array(array('ticketstatus','e','Open'));
		
		// Modified by Phuc on 2020.02.19
        if ($value != 'all' && !empty($value)) {
            if (strpos($value, ':')) {
				$value = explode(':', $value);
				$value = $value[1];	
			}
		
			$ownerType = vtws_getOwnerType($value);
			
            if ($ownerType == 'Users')
				array_push($conditions, array('assigned_user_id', 'c', 'Users:' . $value));
            else{
                array_push($conditions, array('assigned_user_id', 'c', 'Groups:' . $value));
            }
		}
		// Ended by Phuc

        $listSearchParams[] = $conditions;
        return '&search_params='. json_encode($listSearchParams);
    }

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$linkId = $request->get('linkid');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getOpenTickets();
        $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
        for($i = 0;$i<count($data);$i++){
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i][2]).'&nolistcache=1';
        }

		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
        
		//Include special script and css needed for this widget
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/OpenTickets.tpl', $moduleName);
		}
	}
}
