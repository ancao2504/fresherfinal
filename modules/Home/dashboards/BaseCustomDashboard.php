<?php

/**
 * BaseListCustomDashboard
 * Author: Phu Vo
 * Date: 2020.08.25
 */

class Home_BaseCustomDashboard_Dashboard extends Vtiger_IndexAjax_View {

    public function process(Vtiger_Request $request) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $linkId = $request->get('linkid');

        $this->moduleName = $request->getModule();
        $this->widgetName = $request->get('name');

        if (empty($this->widgetModel)) $this->widgetModel = Vtiger_Widget_Model::getInstance($request->get('widgetid'), $currentUser->getId()); // Refactored by Hieu Nguyen on 2021-01-05
        if (empty($this->params)) $this->params = $this->getRequestParams($request);

        // Process raw data
        if (!empty($request->get('data'))) {
            $result = $this->getWidgetData();
            echo json_encode($result);
            return;
        }

        // Process logic involve viewer
        $viewer = $this->getViewer($request);

        $viewer->assign('WIDGET_META', $this->getWidgetMeta($this->params));
        $viewer->assign('MODULE_NAME', $this->moduleName);
        $viewer->assign('PARAMS', $this->params);
        $viewer->assign('WIDGET_NAME', $this->widgetName);
        $viewer->assign('CONTENT', $request->get('content'));

        if (!empty($request->get('content'))) {
            $viewer->display($this->getWidgetContentTpl());
        }
        else {
            $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));
            $viewer->assign('STYLES', $this->getHeaderCss($request));
            $viewer->assign('WIDGET_JS_MODEL_NAME', $this->getWidgetJsModelName());
            $viewer->assign('CONTENT_TPL', $this->getWidgetContentTpl());
            $viewer->assign('FILTER_TPL', $this->getWidgetFilterTpl());
            $viewer->assign('WIDGET', $this->widgetModel);

            $viewer->display($this->getWidgetTpl());
        }
    }

    public function getHeaderScripts(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $widgetName = $this->widgetName;

		$jsFileNames = array(
            "~modules/{$moduleName}/resources/dashboard/{$widgetName}.js",
		);

		$headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request) ?? [];
        $moduleName = $request->getModule();
        $widgetName = $this->widgetName;

		$cssFileNames = array(
            "~modules/Home/resources/dashboard/BaseCustomDashboard.css",
            "~modules/{$moduleName}/resources/dashboard/{$widgetName}.css",
        );

		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
    }

    public function getDataModel() {
        if (!empty($this->dataModel)) return $this->dataModel;

        $moduleName = $this->moduleName;
        $widgetName = $this->widgetName;
        $className = "{$moduleName}_{$widgetName}_Model";
        $this->dataModel = new $className();

        return $this->dataModel;
    }

    public function getWidgetTpl() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetName;
        $tpl = "modules/{$moduleName}/tpls/dashboard/{$widgetName}.tpl";

        if (!file_exists($tpl)) $tpl = "modules/Home/tpls/dashboard/BaseCustomDashboard.tpl";

        return $tpl;
    }

    public function getWidgetContentTpl() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetName;
        $tpl = "modules/{$moduleName}/tpls/dashboard/{$widgetName}Contents.tpl";

        if (!file_exists($tpl)) $tpl = "";

        return $tpl;
    }

    public function getWidgetFilterTpl() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetName;
        $tpl = "modules/{$moduleName}/tpls/dashboard/{$widgetName}Filters.tpl";

        if (!file_exists($tpl)) $tpl = "";

        return $tpl;
    }

    public function getWidgetJsModelName() {
        $moduleName = $this->moduleName;
        $widgetName = $this->widgetName;

        return "{$moduleName}_{$widgetName}_Widget_Js";
    }

    public function getDefaultParams() {
        $dataModel = $this->getDataModel();
        return $dataModel->getDefaultParams() ?? [];
    }

    public function getRequestParams(Vtiger_Request $request) {
        $defaultParams = $this->getDefaultParams();
        $requestParams = $request->getAll();
        $params = !empty($requestParams) ? $requestParams : [];

        foreach ($defaultParams as $key => $value) {
            if (!isset($params[$key])) $params[$key] = $value;
        }

        return $params;
    }

    public function getWidgetData() {
        $dataModel = $this->getDataModel();
        return $dataModel->getWidgetData($this->params);
    }

    public function getWidgetMeta($params) {
        return [];
    }
}
