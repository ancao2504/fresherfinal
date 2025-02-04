
<?php
/*
    ReceiptBySubcategory.php
    Author: Phuc Lu
    Date: 2019.07.26
*/

require_once('modules/Reports/custom/CustomReportHandler.php');
require_once('include/utils/CustomReportUtils.php');

class ReceiptBySubcategory extends CustomReportHandler {


    function getReportData($filter){
        global $adb;

        
        // Get all categroy
        $sql = "SELECT cpreceipt_category FROM vtiger_cpreceipt_category";
        $result = $adb->pquery($sql, []);
        $category = [];
        $data = [];
        $data[0][] = '';
        $data[0][] = vtranslate('LBL_EMPTY', 'CPReceipt');
        $category[''] = '';

        while($row = $adb->fetchByAssoc($result)) {
            $category[] = $row['cpreceipt_category'];
        }

        // Get all subcategory
        $sql = "SELECT cpreceipt_subcategory FROM vtiger_cpreceipt_subcategory";
        $result = $adb->pquery($sql, []);
        $subCategory = [];
        $subCategory[''] = '';

        while($row = $adb->fetchByAssoc($result)) {
            $subCategory[] = $row['cpreceipt_subcategory'];
            $data[0][] = vtranslate($row['cpreceipt_subcategory'], 'CPReceipt');
        }

        $filter = str_replace('vtiger_cpreceipt.', 'r.', $filter);
        
        if (!empty($filter)) {
            $filter = ' WHERE '. $filter;
        }

        $sql = "SELECT cpreceipt_category, cpreceipt_subcategory, SUM(r.amount_vnd) AS amount
        FROM vtiger_cpreceipt r
        INNER JOIN vtiger_crmentity ce on (ce.crmid = r.cpreceiptid and ce.deleted = 0)
        $filter
        GROUP BY cpreceipt_category, cpreceipt_subcategory";

        $result = $adb->pquery($sql, []);
        $categoryData = [];

        while ($row = $adb->fetchByAssoc($result)) {            
            $categoryData[$row['cpreceipt_category']][$row['cpreceipt_subcategory']] = $row['amount'];
        }


        $number = 0;
        foreach ($category as $kC => $c) {  
            $number++;          
            $data[$number][] = $c == '' ? vtranslate('LBL_EMPTY', 'CPReceipt') : vtranslate($c, 'CPReceipt');
            foreach ($subCategory as $kSC => $sc) {
                if (isset($categoryData[$c][$sc])) {
                    $data[$number][] = (int)($categoryData[$c][$sc]);
                }
                else {
                    $data[$number][] = 0;
                }
            }
        }

        return $data;
    }

    function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $mainViewer = new Vtiger_Viewer();

        if ($showReportName) {
            $mainViewer->assign('REPORT_NAME', $this->reportname);
        }

        $filter = $this->getFilterFromScreen();
        $reportData = $this->getReportData($filter);
        $mainViewer->assign('DATA', json_encode($reportData));
        return $mainViewer->fetch('modules/Reports/tpls/ReceiptBySubcategory.tpl');
    }

    function getFilterFromScreen() {
        $filter = $this->_advfiltersql;
        $reportquery = $this->getReportsQuery($this->primarymodule, 'HTML');
        $reportquery = explode(" join ", $reportquery);
        $tableReplace = [];

        foreach ($reportquery as $part) {
            if (substr($part, 0, 7) == 'vtiger_') {
                $part = explode(" on ", $part);
                $part = $part[0];

                if (strpos($part, ' as ') > 0) {
                    $part = explode(' as ', $part);
                    $tableReplace[trim($part[1])] = trim($part[0]);
                }
                else {
                    $tableReplace[trim($part)] = trim($part);
                }
            }
        }

        $find = array_keys($tableReplace);
        $replace = array_values($tableReplace);
        $filter = str_ireplace($find, $replace, $filter);

        return $filter;
    }
}
