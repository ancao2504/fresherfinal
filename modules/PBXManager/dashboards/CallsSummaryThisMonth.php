<?php

/*
    Widget CallsSummaryThisMonth
    Author: Hieu Nguyen
    Date: 2019-10-01
    Purpose: display call summary this month widget on the dashboard
*/

class PBXManager_CallsSummaryThisMonth_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$direction = trim($request->get('direction'));

        if (empty($direction)) {
            $direction = PBXManager_Widget_Model::$DEFAULT_DIRECTION;
        }

        $data = PBXManager_Widget_Model::getDataForCallsSummaryThisMonthWidget($direction);
        $data['compare_count_ratio'] = $data['last_month']['calls_count'] == 0 ? 0 : round(($data['this_month']['calls_count'] - $data['last_month']['calls_count']) / $data['last_month']['calls_count'] * 100, 1);
        $data['compare_duration_ratio'] = $data['last_month']['total_duration'] == 0 ? 0 : round(($data['this_month']['total_duration'] - $data['last_month']['total_duration']) / $data['last_month']['total_duration'] * 100, 1);
        $data['compare_duration_per_call_ratio'] = $data['last_month']['duration_per_call'] == 0 ? 0 : round(($data['this_month']['duration_per_call'] - $data['last_month']['duration_per_call']) / $data['last_month']['duration_per_call'] * 100, 1);
        $showCompare = ($data['last_month']['calls_count'] > 0) ? 1 : 0; // Show compare only when previous period has data

        $viewer = $this->getViewer($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId())); // Refactored by Hieu Nguyen on 2021-01-05
		$viewer->assign('DATA', $data);
        $viewer->assign('SHOW_COMPARE', $showCompare);

        $content = $viewer->fetch('modules/PBXManager/tpls/dashboard/CallsSummaryThisMonthWidgetContents.tpl');

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
			$viewer->display('modules/PBXManager/tpls/dashboard/CallsSummaryThisMonthWidget.tpl');
		}
	}
}