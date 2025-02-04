<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_TotalRevenuePerSalesPerson_Dashboard extends Vtiger_IndexAjax_View {
    
    function getSearchParams($assignedto,$dates) {
		$listSearchParams = array();
		
		// [CustomOwnerField] Modified by Phu Vo on 2019.11.22 Fix assigned user id search params only contain label (fzVirkBs)
		// Modified by Phuc on 2020.02.04
		$conditions = [["sales_stage", "e", "Closed Won"]];

        if ($assignedto != 'all' && !empty($assignedto)) {
            if (strpos($assignedto, ':')) {
				$assignedto = explode(':', $assignedto);
				$assignedto = $assignedto[1];			
			}

			$ownerType = vtws_getOwnerType($assignedto);

            if ($ownerType == 'Users')
				array_push($conditions, array('main_owner_id', 'e', 'Users:' . $assignedto));
            else{
                array_push($conditions, array('main_owner_id', 'e', 'Groups:' . $assignedto));
            }
		}
		// Ended Phuc
		// End Phu Vo

        if(!empty($dates)) {
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
		
		$dates = $request->get('createdtime');
        
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$data = $moduleModel->getTotalRevenuePerSalesPerson($dates);
        $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
        for($i = 0;$i<count($data);$i++){
			// [CustomOwnerField] Modified by Phu Vo on 2019.11.28 to get data based on main ownership (fzVirkBs)
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i]['id'], $request->get('dateFilter')).'&nolistcache=1';
			// End by Phu Vo
        }

		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);
		$viewer->assign('YAXIS_FIELD_TYPE', 'currency');

		//Include special script and css needed for this widget
		$viewer->assign('STYLES',$this->getHeaderCss($request));
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/TotalRevenuePerSalesPerson.tpl', $moduleName);
		}
	}
}
