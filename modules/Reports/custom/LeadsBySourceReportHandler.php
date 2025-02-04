<?php

require_once('modules/Reports/custom/CustomReportHandler.php');

class LeadsBySourceReportHandler extends CustomReportHandler {

    protected $chartTemplate = 'modules/Reports/tpls/LeadsBySourceReportChart.tpl';
    protected $dashboardWidgetFilterTemplate = 'modules/Reports/tpls/dashboard/LeadsBySourceWidgetFilter.tpl';

    protected function getChartData(array $params) {
        global $adb, $current_user;
        $sql = "";
        $sqlParams = [];
        $result = $adb->pquery($sql, $sqlParams);
        $data = [];

        // TODO: xử lý result lấy data cho chart

        return $data;
    }

    protected function getReportData($params) {
        global $adb, $current_user;
        $sql = "SELECT ... FROM ... WHERE 1 = 1 ";
        $sqlParams = [];
        
        // Kiểm tra từng param, có thì nối chuỗi query
        if (!empty($params['start_date'])) {
            $sql .= "... >= ?";
            $sqlParams[] = [$params['start_date']];
        }

        if (!empty($params['end_date'])) {
            $sql .= "... <= ?";
            $sqlParams[] = [$params['end_date']];
        }

        $result = $adb->pquery($sql, $sqlParams);
        $data = [];

        // TODO: xử lý result lấy data cho report

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        // Lấy data từ bộ lọc report
        $params = $_REQUEST;

        // Render chart
        $chart = $this->renderChart($params);    // Params tại đây lấy trực tiếp từ POST

        // Lấy report data
        $reportData = $this->getReportData($params);

        // TODO: render report data ra table HTML. 
        // Report đơn giản thì loop qua data, fetch từng row result từ file LeadsBySourceReportHandlerRowTemplate.tpl
        // Report phức tạp (lặp cột) thì có thể gửi mảng data ra tpl, xử lý logic bằng smarty syntax (for, while, if, else)
        $reportResult = 'REPORT RESULT';    // Biến này sẽ chứa nhiều <tr></tr> nối lại

        $viewer = new Vtiger_Viewer();
        $viewer->assign('CHART', $chart);
        $viewer->assign('REPORT_RESULT', $reportResult);
        $viewer->display('modules/Reports/tpls/LeadsBySourceReport.tpl');
    }
}