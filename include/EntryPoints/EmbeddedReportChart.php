<?php

/*
    EntryPoint EmbeddedReportChart
    Author: Hieu Nguyen
    Date: 2020-09-07
    Purpose: to provide a way to load report chart for Mobile view
    Usage: (GET) entrypoint.php?name=EmbeddedReportChart&token=<Mobile-Auth-Token>
*/

require_once('include/utils/MobileApiUtils.php');

class EmbeddedReportChart extends Vtiger_EntryPoint {

	function process(Vtiger_Request $request) {
        global $current_user;
        $current_user = null;
        $token = $request->get('token');
        MobileApiUtils::checkSession($token);

		$viewer = new Vtiger_Viewer();
		$reportId = $request->get('record');

		$reportModel = Reports_Record_Model::getInstanceById($reportId);
        $indexView = new Vtiger_Index_View();

        if ($reportModel->get('reporttype') == 'tabular') {
            $customHandler = $reportModel->getCustomHandler();
            if (!$customHandler) return;
            

            $params = $customHandler->getFilterParams();
            $reportFilter = $customHandler->renderReportFilter($params);
            $chart = $customHandler->renderChart($params);

            $viewer->assign('JS_LANGUAGE_STRINGS', $indexView->getJSLanguageStrings($request));
            $viewer->assign('REPORT_TITLE', $reportModel->getName());
            $viewer->assign('REPORT_FILTER', $reportFilter);
            $viewer->assign('CHART', $chart);
            $viewer->assign('REPORT_DETAIL_JS_FILE', $this->getReportDetailJsFile($customHandler));
            $viewer->display('modules/Reports/tpls/EmbeddedReportChart.tpl');
        }
        else {
            if ($reportModel->get('reporttype') == 'chart') {
                $primaryModuleName = $reportModel->getPrimaryModule();
                $chartTitle = $reportModel->getName() . ' ('. vtranslate($primaryModuleName, $primaryModuleName) . ')';

                $reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);

                $reportModel->set('custom_handler_file', 'modules/Reports/custom/CustomChartReportHandler.php');
                $customReportHandler = $reportModel->getCustomHandler();
                $customReportHandler->setChartReportModel($reportChartModel);
                $chart = $customReportHandler->renderChart([]);

                $viewer->assign('JS_LANGUAGE_STRINGS', $indexView->getJSLanguageStrings($request));
                $viewer->assign('REPORT_TITLE', $chartTitle);
                $viewer->assign('CHART', $chart);
                $viewer->display('modules/Reports/tpls/EmbeddedReportChart.tpl');
            }
        }
	}

    function getReportDetailJsFile($reportHandler) {
        // START-- Added by Phu Vo on 2020.9.20 to support custom detail js file from handler
        $customDetailJsFile = $reportHandler->getReportDetailJsFile();
        if (!empty($customDetailJsFile)) return $customDetailJsFile;
        // END-- Added by Phu Vo on 2020.9.20 to support custom detail js file from handler

        $handlerClassName = get_class($reportHandler);
        $reportDetailJsName = str_replace('Handler', 'Detail.js', $handlerClassName);
        $jsFile = 'modules/Reports/resources/' . $reportDetailJsName;
        return $jsFile;
    }
}