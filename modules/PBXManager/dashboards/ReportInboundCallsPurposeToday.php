<?php

/*
    Widget ReportInboundCallsPurposeToday
    Author: Hieu Nguyen
    Date: 2020-02-18
    Purpose: display report inbound calls purpose today widget on the dashboard
*/

class PBXManager_ReportInboundCallsPurposeToday_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
		$linkId = $request->get('linkid');
        $data = PBXManager_Widget_Model::getDataForReportCallsPurposeTodayWidget('Inbound');

        $viewer = $this->getViewer($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId())); // Refactored by Hieu Nguyen on 2021-01-05
		$viewer->assign('DATA', $data);

        $content = $viewer->fetch('modules/PBXManager/tpls/dashboard/ReportInboundCallsPurposeTodayWidgetContents.tpl');

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
			$viewer->display('modules/PBXManager/tpls/dashboard/ReportInboundCallsPurposeTodayWidget.tpl');
		}
	}
}