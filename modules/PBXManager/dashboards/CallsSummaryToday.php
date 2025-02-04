<?php

/*
    Widget CallsSummaryToday
    Author: Hieu Nguyen
    Date: 2019-09-30
    Purpose: display call summary today widget on the dashboard
*/

class PBXManager_CallsSummaryToday_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
		$linkId = $request->get('linkid');
		$direction = trim($request->get('direction'));

        if (empty($direction)) {
            $direction = PBXManager_Widget_Model::$DEFAULT_DIRECTION;
        }

        $data = PBXManager_Widget_Model::getDataForCallsSummaryTodayWidget($direction);
        $data['compare_count_ratio'] = $data['yesterday']['calls_count'] == 0 ? 0 : round(($data['today']['calls_count'] - $data['yesterday']['calls_count']) / $data['yesterday']['calls_count'] * 100, 1);
        $data['compare_duration_ratio'] = $data['yesterday']['total_duration'] == 0 ? 0 : round(($data['today']['total_duration'] - $data['yesterday']['total_duration']) / $data['yesterday']['total_duration'] * 100, 1);
        $data['compare_duration_per_call_ratio'] = $data['yesterday']['duration_per_call'] == 0 ? 0 : round(($data['today']['duration_per_call'] - $data['yesterday']['duration_per_call']) / $data['yesterday']['duration_per_call'] * 100, 1);
        $showCompare = ($data['yesterday']['calls_count'] > 0) ? 1 : 0; // Show compare only when previous period has data

        $viewer = $this->getViewer($request);
		$viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
		$viewer->assign('STYLES', $this->getHeaderCss($request));
        $viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('WIDGET', Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId())); // Refactored by Hieu Nguyen on 2021-01-05
		$viewer->assign('DATA', $data);
		$viewer->assign('SHOW_COMPARE', $showCompare);

        $content = $viewer->fetch('modules/PBXManager/tpls/dashboard/CallsSummaryTodayWidgetContents.tpl');

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
			$viewer->display('modules/PBXManager/tpls/dashboard/CallsSummaryTodayWidget.tpl');
		}
	}
}