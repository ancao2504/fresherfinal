<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_ChartReportWidget_Dashboard extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$record = $request->get('reportid');
        $widgetId = $request->get('widgetid');

		$reportModel = Reports_Record_Model::getInstanceById($record);

        // Modified by Hieu Nguyen on 2021-06-02 to support custom chart report widget at dashboard
        $isChartReport = $reportModel->get('reporttype') == 'chart';

        if ($isChartReport) {
            $reportModel->set('custom_handler_file', 'modules/Reports/custom/CustomChartReportHandler.php');
        }

        global $current_user;
        $customHandler = $reportModel->getCustomHandler();
        
        if ($customHandler) {
            if ($isChartReport) {
                $reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
                $customHandler->setChartReportModel($reportChartModel);
            }

            // Load widget content only
            if ($request->get('content')) {
                $params = $_POST['filter'];

                // Save new params first
                Vtiger_Widget_Model::updateWidgetParams($widgetId, $params);

                // Then render chart using that params
                $params['widget_id'] = $widgetId;
                $customChart = $customHandler->renderChart($params);

                echo $customChart;
            } 
            // Load the whole widget
            else {
                $widget = Vtiger_Widget_Model::getInstanceForCustomChartWidget($widgetId, $current_user->id);
                $widget->set('title', $reportModel->getName());
                $params = json_decode(decodeUTF8($widget->get('data')), true) ?? [];
                
                // No widget params and filter for default Chart Report for now
                if ($isChartReport) {
                    $params = [];
                    $widgetFilter = '';
                }
                else {
                    $widgetFilter = $customHandler->renderWidgetFilter($params);
                }

                $params['widget_id'] = $widget->get('id');  // This will help to unique multiple widgets at the same dashboard
                $customChart = $customHandler->renderChart($params);

                $viewer->assign('WIDGET', $widget);
                $viewer->assign('WIDGET_CONTENT', $customChart);
                $viewer->assign('WIDGET_FILTER', $widgetFilter);
                $viewer->assign('IS_CHART_REPORT', $isChartReport);

                // Required info for chart report widget
                if ($isChartReport) {
                    $viewer->assign('CHART_TYPE', 'chart');
                    $viewer->assign('REPORT_MODEL', $reportModel);
                }

                $viewer->display('modules/Reports/tpls/dashboard/CustomChartWidget.tpl');
            }

            exit;
        }
        // End Hieu Nguyen

		$reportChartModel = Reports_Chart_Model::getInstanceById($reportModel);
        $primaryModule = $reportModel->getPrimaryModule();
        $moduleModel = Vtiger_Module_Model::getInstance($primaryModule);
        if(!$moduleModel->isPermitted('DetailView')){
			$viewer->assign('MESSAGE', $primaryModule.' '.vtranslate('LBL_NOT_ACCESSIBLE'));
			$viewer->view('OperationNotPermitted.tpl', $primaryModule);
        }
		$secondaryModules = $reportModel->getSecondaryModules();
		if(empty($secondaryModules)) {
			$viewer->assign('CLICK_THROUGH', true);
		}

		$viewer->assign('CHART_TYPE', $reportChartModel->getChartType());
        $data = $reportChartModel->getData();
        $data = json_encode($data, JSON_HEX_APOS);
		$viewer->assign('DATA', $data);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $widget = Vtiger_Widget_Model::getInstanceForCustomChartWidget($widgetId, $currentUser->getId());   // Modified by Hieu Nguyen on 2021-01-05 to support multiple chart report widget in the same dashboard tab
        $widget->set('title',$reportModel->getName().' ('.vtranslate($primaryModule, $primaryModule).')');
		$viewer->assign('WIDGET', $widget);

		$viewer->assign('RECORD_ID', $record);
        $viewer->assign('WIDGET_ID', $widgetId);
		$viewer->assign('REPORT_MODEL', $reportModel);
		$viewer->assign('SECONDARY_MODULES',$secondaryModules);
		$viewer->assign('MODULE', $moduleName);
        $viewer->assign('PRIMARY_MODULE', $primaryModule);

		$isPercentExist = false;
		$selectedDataFields = $reportChartModel->get('datafields');
		foreach ($selectedDataFields as $dataField) {
			list($tableName, $columnName, $moduleField, $fieldName, $single) = split(':', $dataField);
			list($relModuleName, $fieldLabel) = split('_', $moduleField);
			$relModuleModel = Vtiger_Module_Model::getInstance($relModuleName);
			$fieldModel = Vtiger_Field_Model::getInstance($fieldName, $relModuleModel);
			if ($fieldModel && $fieldModel->getFieldDataType() != 'currency') {
				$isPercentExist = true;
				break;
			} else if (!$fieldModel) {
				$isPercentExist = true;
			}
		}
		$yAxisFieldDataType = (!$isPercentExist) ? 'currency' : '';
		$viewer->assign('YAXIS_FIELD_TYPE', $yAxisFieldDataType);

        $content = $request->get('content');
		if(!empty($content)) {
			$viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
		} else {
			$viewer->view('dashboards/DashBoardWidget.tpl', $moduleName);
		}
	}
}

