<?php

/*
	CustomerAgeReportHandler.php
    Author: The Vi
    Date: 2025-01-03
    Purpose: Provide handler for Best Sellers Report
*/

require_once ('modules/Reports/custom/CustomReportHandler.php');

class BestSellersReportHandler extends CustomReportHandler {

	public function prepare() {}

	//Tạo câu truy vấn đến lấy dữ liệu
	public function sGetSQLforReport($reportid, $filterSql) {
		$request = new Vtiger_Request($_REQUEST);
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
		$currentUser = Users_Record_Model::getCurrentUserModel();
        if (!empty($startDate)) {
            $startDateTimeField = new DateTimeField($startDate . ' 00:00:00');
            $startDate = $startDateTimeField->getDBInsertDateTimeValue($currentUser);
        }
        if (!empty($endDate)) {
            $endDateTimeField = new DateTimeField($endDate . ' 23:59:59');
            $endDate = $endDateTimeField->getDBInsertDateTimeValue($currentUser);
        }
		if (!empty($startDate) && !empty($endDate)) {
			if ($endDate < $startDate) {
				throw new Exception("Ngày kết thúc phải sau Ngày bắt đầu.");
			}
			$dateFilter = "AND vtiger_crmentity.createdtime BETWEEN '$startDate' AND '$endDate'";
		}
		elseif (!empty($startDate)) {
			$dateFilter = "AND vtiger_crmentity.createdtime >= '$startDate'";
		}
		elseif (!empty($endDate)) {
			$dateFilter = "AND vtiger_crmentity.createdtime <= '$endDate'";
		}
		
        $sql = "SELECT DISTINCT vtiger_products.productcategory AS 'Danh mục sản phẩm', 
                  	CAST(SUM(vtiger_inventoryproductrel.quantity) AS UNSIGNED) AS 'Tổng số lượng SP',
             		CAST(SUM(vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity) AS UNSIGNED) AS 'Tổng tiền'
				FROM vtiger_salesorder
                INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
                INNER JOIN vtiger_inventoryproductrel ON vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id
                INNER JOIN vtiger_products ON vtiger_inventoryproductrel.productid = vtiger_products.productid
                WHERE vtiger_crmentity.deleted = 0 AND vtiger_salesorder.sostatus = 'Delivered' $dateFilter
				GROUP BY vtiger_products.productcategory
				ORDER BY SUM(vtiger_inventoryproductrel.quantity) DESC 
				LIMIT 10";

        return $sql;
    }
    // trả ra danh sách tên cột đã dịch sẵn theo ngôn ngữ người dùng
	public function getReportHeaders() {
        return [
            vtranslate('LBL_PRODUCT_CATEGORY', 'Reports') => '20%',
            vtranslate('LBL_PRODUCT_AMOUNT_TOTAL', 'Reports') => '20%',
            vtranslate('LBL_AMOUNT_MONEY_TOTAL', 'Reports') => '80%',
        ];
    }

	//render kết quả report thành HTML
	public function renderReportResult($filterSql, $showReportName = false, $print = false) {
		// var_dump($row);
		//Nhận kết quả từ getReportResult,nó fetch qua từng row dữ liệu rồi tạo html cho từng hàng record
		$processor = function(&$rowViewer, &$result, $row) {
			$rowViewer->assign('ROW_DATA', $row);
			$result .= $rowViewer->fetch('modules/Reports/tpls/CustomReportRowBestSellers.tpl');
		};
		$mainViewer = new Vtiger_Viewer();

		if($showReportName) {
			$mainViewer->assign('REPORT_NAME', $this->reportname);
		}
		$timeFilters = ['Tuần', 'Tháng', 'Quý'];
		$mainViewer->assign('TIME_FILTERS', $timeFilters);
		$reportHeaders = $this->getReportHeaders();
		$reportResult = $this->getReportResult($processor, $filterSql, false, $print);
		$mainViewer->assign('REPORT_HEADERS', $reportHeaders);
		$mainViewer->assign('REPORT_RESULT', $reportResult);
		$mainViewer->assign('PRIMARY_MODULE', $this->primarymodule);
		$mainViewer->assign('PRINT', $print);

		$reportResult = $mainViewer->fetch('modules/Reports/tpls/CustomReportBestSellers.tpl');
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