<?php

/**
 * BaseListCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_BaseListCustomDashboard_Dashboard extends Home_BaseCustomDashboard_Dashboard {

    public function getWidgetContentTpl() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetModel->getName();
        $tpl = "modules/{$moduleName}/tpls/dashboard/{$widgetName}Contents.tpl";

        if (!file_exists($tpl)) $tpl = "modules/Home/tpls/dashboard/BaseListCustomDashboardContents.tpl";

        return $tpl;
    }

    public function getHeaderScripts(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $widgetName = $this->widgetModel->getName();

		$jsFileNames = array(
            "~resources/libraries/DataTables/js/jquery.dataTables.min.js",
            "~modules/Home/resources/dashboard/BaseListCustomDashboard.js",
            "~modules/{$moduleName}/resources/dashboard/{$widgetName}.js",
		);

		$headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request) ?? [];

		$cssFileNames = array(
            "resources/libraries/DataTables/css/jquery.dataTables.min.css",
        );

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
    }

    public function getDefaultParams() {
        $defaultParams = parent::getDefaultParams();
        
        $defaultParams['length'] = 5;
        $defaultParams['start'] = 0;

        return $defaultParams;
    }

    public function getWidgetMeta($params) {
        $widgetMeta = [
            'widget_headers' => $this->getDataModel()->getWidgetHeaders($params),
        ];

        return $widgetMeta;
    }
}
