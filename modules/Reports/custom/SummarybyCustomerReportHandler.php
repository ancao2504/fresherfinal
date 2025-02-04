<?php

require_once ('modules/Reports/custom/CustomReportHandler.php');

class SummarybyCustomerReportHandler extends CustomReportHandler {
    
	public function prepare() {}
    // trả ra danh sách tên cột đã dịch sẵn theo ngôn ngữ người dùng

	public function getReportHeaders() {
        return [
            vtranslate('LBL_SERIAL_PRODUCT', 'Reports'),
            vtranslate('LBL_CUSTOMER_NAME', 'Reports'),
            vtranslate('LBL_PRODUCT_NAME', 'Reports'),
            vtranslate('LBL_SALE_DAY', 'Reports') ,
            vtranslate('LBL_SALE_MONEY', 'Reports'),
        ];
    }
	public function getReportData() {
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
        } elseif (!empty($startDate)) {
            $dateFilter = "AND vtiger_crmentity.createdtime >= '$startDate'";
        } elseif (!empty($endDate)) {
            $dateFilter = "AND vtiger_crmentity.createdtime <= '$endDate'";
        }

        $sql = "SELECT vtiger_products.serialno AS 'Serial',
	                vtiger_account.accountname AS 'Tên khách hàng',
	                vtiger_products.productname AS 'Tên sản phẩm',
	                vtiger_crmentity.createdtime AS 'Ngày bán',
	                CAST((vtiger_inventoryproductrel.quantity * vtiger_inventoryproductrel.listprice) AS UNSIGNED) AS 'Số tiền bán' 
				FROM vtiger_salesorder
	            INNER JOIN vtiger_crmentity ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
	            INNER JOIN vtiger_inventoryproductrel ON vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id
	            INNER JOIN vtiger_products ON vtiger_inventoryproductrel.productid = vtiger_products.productid
	            INNER JOIN vtiger_account ON vtiger_salesorder.accountid = vtiger_account.accountid
	            WHERE vtiger_crmentity.deleted = 0 $dateFilter
				GROUP BY vtiger_account.accountid, vtiger_products.productid
				ORDER BY vtiger_crmentity.createdtime DESC";
		
        global $adb;
        // $relult:
        // [
        //     [
        //         "Serial" => "12345",
        //         "Tên khách hàng" => "Công ty ABC",
        //         "Tên sản phẩm" => "Laptop",
        //         "Ngày bán" => "2025-01-05 10:00:00",
        //         "Số tiền bán" => "25000000"
        //     ],
        //     [
        //         "Serial" => "54321",
        //         "Tên khách hàng" => "Công ty XYZ",
        //         "Tên sản phẩm" => "Máy in",
        //         "Ngày bán" => "2025-01-08 15:00:00",
        //         "Số tiền bán" => "5000000"
        //     ]
        // ]
        
        $result = $adb->pquery($sql, array());
        $data = array();
        if ($adb->num_rows($result) > 0) {
            while ($row = $adb->fetchByAssoc($result)) {
                $data[] = array_values($row);
            }
        }
        // Hàm array_values trong PHP được sử dụng để trả về một mảng mới với các giá trị từ mảng ban đầu, đồng thời đặt lại các chỉ số của mảng về dạng số nguyên liên tiếp bắt đầu từ 0. Hàm này rất hữu ích khi bạn muốn loại bỏ các chỉ số gốc của mảng (nếu chúng là chuỗi hoặc không liên tiếp).
        // //data:
        // $data = [
        //     ["12345", "Công ty ABC", "Laptop", "2025-01-05 10:00:00", "25000000"],
        //     ["54321", "Công ty XYZ", "Máy in", "2025-01-08 15:00:00", "5000000"]
        // ];
        
        // print_r($data);
        return $data;
    
    }

	
	//render kết quả report thành HTML
	public function renderReportResult($filterSql, $showReportName = false, $print = false) {
        $mainViewer = new Vtiger_Viewer();

		if($showReportName) {
			$mainViewer->assign('REPORT_NAME', $this->reportname);
		}

		$mainViewer->assign('REPORT_HEADERS', $this->getReportHeaders());
		$mainViewer->assign('REPORT_DATA', $this->getReportData());
		$mainViewer->assign('PRIMARY_MODULE', $this->primarymodule);
		$mainViewer->assign('PRINT', $print);

		$reportResult = $mainViewer->fetch('modules/Reports/tpls/CustomReportSummaryByCustomer.tpl');
		return $reportResult;
	}
}