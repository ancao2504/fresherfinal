<?php

require_once('libraries/ArrayUtils/ArrayUtils.php');
require_once('modules/Reports/ReportRun.php');

/*
    CustomReportHandler
    Author: Hieu Nguyen
    Date: 2018-12-09
    Purpose: a parent class for custom reports
*/

class CustomReportHandler extends ReportRun {

    protected $chartTemplate = '';
    protected $reportFilterTemplate = '';
    protected $dashboardWidgetFilterTemplate = '';
    protected $detailJsFile = '';
    protected $reportFilterMeta = [];

    // Return filter params. Override this function to handle your own logic if needed
    function getFilterParams() {
        $params = $_REQUEST;    // TODO: save params for each user
        return $params;
    }

    // Render report filter as HTML. Override this function to handle your own logic if needed
    function renderReportFilter(array $params) {
        $viewer = new Vtiger_Viewer();
        $viewer->assign('PARAMS', $params);
        $viewer->assign('FILTER_META', $this->reportFilterMeta);

        return $viewer->fetch($this->reportFilterTemplate);
    }

    // Return translated header names
    public function getReportHeaders($params = null) { // Added by Phu Vo
        global $adb, $current_user;
        $headers = [];

        foreach($this->_columnslist as $field => $column) {
            $parts = split(':', $field);
            $headerName = $parts[2];

            if($headerName != 'LBL_ACTION') {
                $translatedLabel = getTranslatedString($headerName, $module);

                if($fieldLabel == $translatedLabel) {
                    $translatedLabel = getTranslatedString(str_replace('_', ' ', $headerName), $module);
                }
                else {
                    $translatedLabel = str_replace('_', ' ', $translatedLabel);
                }

                $headers[] = $translatedLabel;
            }
        }

        return $headers;
    }

    // Return processed report result. Override this function to handle your own logic if needed
    function getReportResult($processor, $filterSql, $returnArray = true, $print = false) {
        global $adb, $current_user;
        $result = ($returnArray) ? [] : '';

        $this->prepare();

        // Init viewer for rows
        $rowViewer = new Vtiger_Viewer();
        $rowViewer->assign('PRINT', $print);

        // Query and process data
        $sql = $this->sGetSQLforReport($this->reportid, $filterSql);
        $res = $adb->pquery($sql, []);

        while($row = $adb->fetchByAssoc($res)) {
            $processor($rowViewer, $result, $row);     // Call processor function to process data
        }

        return $result;
    }

    // Override this function to handle your own logic to get chart data
    protected function getChartData(array $params) {
        return [];
    }

    // Render chart as HTML. Override this function to handle your own logic if needed
    function renderChart(array $params) {
        global $current_user;
        if (empty($this->chartTemplate)) return;

        $reportParams = $this->getFilterParams();
        $chartData = $this->getChartData($params);
        $viewer = new Vtiger_Viewer();
        $viewer->assign('DATA', $chartData);
        $viewer->assign('PARAMS', $reportParams);

        // Params sent from Dashboard will have widget_id to help unique the multiple chart widget from the same report
        if (!empty($params['widget_id'])) {
            $viewer->assign('WIDGET_ID', $params['widget_id']);
        }

        // Display chart title for Dashboard widget
        if (!empty($params['chart_title'])) {
            $viewer->assign('CHART_TITLE', $params['chart_title']);
        }
        else {
            $widget = Vtiger_Widget_Model::getInstanceForCustomChartWidget($params['widget_id'], $current_user->id);
            $data = json_decode(decodeUTF8($widget->get('data')), true);

            if (!empty($data) && !empty($data['chart_title'])) {
                $viewer->assign('CHART_TITLE', $data['chart_title']);
            }
        }

        return $viewer->fetch($this->chartTemplate);
    }

    // Render dashboard widget filter as HTML. Override this function to handle your own logic if needed
    function renderWidgetFilter(array $params) {
        $viewer = new Vtiger_Viewer();
        $viewer->assign('PARAMS', $params);

        return $viewer->fetch($this->dashboardWidgetFilterTemplate);
    }

    // Render report result as HTML. Override this function to handle your own logic if needed
    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        return '';
    }

    // Display report in detail view. Override this function to handle your own logic if needed
    function display() {
        global $adb, $current_user;
        $this->getQueryColumnsList($this->reportid, 'HTML');

        $reportResult = $this->renderReportResult('');
        echo $reportResult;
    }

    // Return HTML result for printing. Override this function to handle your own logic if needed
    function getPrintResult($advanceFilterSql) {
        $this->getQueryColumnsList($this->reportid, 'PRINT');

        $reportResult = $this->renderReportResult($advanceFilterSql, true, true);
        return $reportResult;
    }

    // Write the report result into the CSV file for downloading. Override this function to handle your own logic if needed
    function writeReportToCSVFile($tempFileName, $advanceFilterSql) {
        $this->getQueryColumnsList($this->reportid, 'HTML');
        parent::writeReportToCSVFile($tempFileName, $advanceFilterSql);
    }

    // Write the report result into the Excel file for downloading. Override this function to handle your own logic if needed
    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        $this->getQueryColumnsList($this->reportid, 'HTML');
        parent::writeReportToExcelFile($tempFileName, $advanceFilterSql);
    }

    function getReportDetailJsFile() {
        return $this->detailJsFile;
    }
}
