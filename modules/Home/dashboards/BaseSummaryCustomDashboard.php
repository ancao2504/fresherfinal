<?php

/**
 * BaseSummaryCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_BaseSummaryCustomDashboard_Dashboard extends Home_BaseCustomDashboard_Dashboard {

    public function process(Vtiger_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $linkId = $request->get('linkid');

        $this->moduleName = $request->getModule();
        $this->widgetName = $request->get('name');
        if (empty($this->widgetModel)) $this->widgetModel = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05
        if (empty($this->params)) $this->params = $this->getRequestParams($request);

        // Process logic involve viewer
        $viewer = $this->getViewer($request);
        $viewer->assign('DATA', $this->getWidgetData());

        parent::process($request);
    }

    public function getWidgetContentTpl() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetModel->getName();
        $tpl = "modules/{$moduleName}/tpls/dashboard/{$widgetName}Contents.tpl";

        if (!file_exists($tpl)) $tpl = "modules/Home/tpls/dashboard/BaseSummaryCustomDashboardContents.tpl";

        return $tpl;
    }

    public function getHeaderScripts(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $widgetName = $this->widgetModel->getName();

		$jsFileNames = array(
            "~modules/Home/resources/dashboard/BaseSummaryCustomDashboard.js",
            "~modules/{$moduleName}/resources/dashboard/{$widgetName}.js",
		);

		$headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request) ?? [];

		$cssFileNames = array(
            "~modules/Home/resources/dashboard/BaseSummaryCustomDashboard.css",
        );

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
    }

    public function getWidgetMeta($params) {
        $widgetMeta = [
            'widget_headers' => $this->getDataModel()->getWidgetHeaders($params),
            'last_period' => $this->getDataModel()->lastPeriod,
            'column' => $this->getDataModel()->column,
        ];

        return $widgetMeta;
    }
}
