<?php

/*
    Widget CompareCallsThisWeek
    Author: Hieu Nguyen
    Date: 2019-10-26
    Purpose: display compare calls this week widget on the dashboard
*/

class PBXManager_CompareCallsThisWeek_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
		$linkId = $request->get('linkid');
        $direction = trim($request->get('direction'));

        if (empty($direction)) {
            $direction = PBXManager_Widget_Model::$DEFAULT_DIRECTION;
        }

        $data = PBXManager_Widget_Model::getDataForCompareCallsThisWeekWidget($direction);

        $viewer = $this->getViewer($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId())); // Refactored by Hieu Nguyen on 2021-01-05
		$viewer->assign('DATA', $data);

        $content = $viewer->fetch('modules/PBXManager/tpls/dashboard/CompareCallsThisWeekWidgetContents.tpl');

        // Prevent access this feature if it is not available in selected CRM package
        if (isForbiddenFeature('CallCenterIntegration')) {
            $content = getForbiddenFeatureErrorMessage();
        }

        // Load content only
		if (!empty($request->get('content'))) {
			echo $content;
            exit;
		}
        // Load full widget
        else {
            $viewer->assign('WIDGET_CONTENT', $content);
			$viewer->display('modules/PBXManager/tpls/dashboard/CompareCallsThisWeekWidget.tpl');
		}
	}
}