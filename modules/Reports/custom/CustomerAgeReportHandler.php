<?php

require_once('modules/Reports/custom/CustomReportHandler.php');

class CustomerAgeReportHandler extends CustomReportHandler {

    function prepare() {
     
        $this->getQueryColumnsList($this->reportid, 'HTML');
        $this->_columnslist = array_insert_before(
        'CRM_crmentity:crmid:LBL_ACTION:crmid:I',
        $this->_columnslist,
        'contacts_age',
        "YEAR(NOW()) - YEAR(CRM_contactsubdetails.birthday) AS Contacts_LBL_AGE"
        );
         
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $processor = function(&$rowViewer, &$result, $row) {
            $rowViewer->assign('ROW_DATA', $row);
            $result .= $rowViewer->fetch('modules/Reports/tpls/CustomReportRowTemplate.tpl');
            };
            // Init viewer for main report
            $mainViewer = new VTiger_Viewer();
            if($showReportName) {
            $mainViewer->assign('REPORT_NAME', $this->reportname);
            }
            $mainViewer->assign('REPORT_HEADERS', $this->getReportHeaders());
            $mainViewer->assign('REPORT_RESULT', $this->getReportResult($processor, $filterSql, false, $print));
            $mainViewer->assign('PRIMARY_MODULE', $this->primarymodule);
            $mainViewer->assign('PRINT', $print);
           
            $reportResult = $mainViewer->fetch('modules/Reports/tpls/CustomReport.tpl');
            return $reportResult;
    }

    function writeReportToCSVFile($tempFileName, $advanceFilterSql) {
        $this->prepare();
        parent::writeReportToCSVFile($tempFileName, $advanceFilterSql);
       
    }

    function writeReportToExcelFile($tempFileName, $advanceFilterSql) {
        // Implement logic to write the report to an Excel file
        $this->prepare();
        parent::writeReportToExcelFile($tempFileName, $advanceFilterSql);
    }
}
