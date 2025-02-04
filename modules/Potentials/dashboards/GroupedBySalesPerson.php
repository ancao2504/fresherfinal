<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Potentials_GroupedBySalesPerson_Dashboard extends Vtiger_IndexAjax_View {
    
    function getSearchParams($assignedto,$stage) {
		$listSearchParams = array();

		// Updated by Phuc on 2020.02.04
		$conditions = [["sales_stage", "e", decode_html(urlencode(escapeSlashes($stage)))]];

        if ($assignedto != 'all' && !empty($assignedto)) {
            if (strpos($assignedto, ':')) {
				$assignedto = explode(':', $assignedto);
				$assignedto = $assignedto[1];			
			}

			$ownerType = vtws_getOwnerType($assignedto);

            // Modified by Hieu Nguyen on 2020-03-26 to get the correct drill down params
            if ($ownerType == 'Users') {
				array_push($conditions, ['main_owner_id', 'c', 'Users:' . $assignedto]);
            }
            else {
                array_push($conditions, ['main_owner_id', 'c', 'Groups:' . $assignedto]);
            }
            // End Hieu Nguyen
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
		$data = $moduleModel->getPotentialsCountBySalesPerson();
        $listViewUrl = $moduleModel->getListViewUrlWithAllFilter();
        for($i = 0;$i<count($data);$i++){
            $data[$i]["links"] = $listViewUrl.$this->getSearchParams($data[$i]["smownerid"],$data[$i]["link"]).'&nolistcache=1';
        }

		$widget = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05

		$viewer->assign('WIDGET', $widget);
		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('DATA', $data);

		//Include special script and css needed for this widget
		$viewer->assign('STYLES',$this->getHeaderCss($request));
		$viewer->assign('CURRENTUSER', $currentUser);

		$content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/GroupBySalesPerson.tpl', $moduleName);
		}
	}
}