<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
require_once("include/ExcelHelper.php");
class Contacts_ExportContacts_View extends Vtiger_View_Controller {


    function __construct() {
        parent::__construct();
    }

   
    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        // Write your own logic to check for access permission
        $allowAccess = true; // Set this to false if a user's role is not permitted

        if(!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    public function process(Vtiger_Request $request) {
      
        $rows = [
            [
                ['value' => 'Name', 'bold' => true], // Header được in đậm
                ['value' => 'Email', 'bold' => true],
                ['value' => 'Phone', 'bold' => true],
            ],
            ['TheVi', 'john@example.com', '123456789'], // Dữ liệu
            ['Jane Smith', 'jane@example.com', '987654321'],
        ];
    
        // Tên file và đường dẫn template
        $fileName = 'exported-exercise-19-export-file';
        $templatePath = 'C:\xampp\htdocs\fresherfinal\exercise-19-export-file.xlsx';
    
        // Gọi hàm exportToExcel
        $filePath = ExcelHelper::exportToExcel($rows, $fileName, true);
    
        // Sau khi export, thực hiện xử lý bổ sung
        require_once('libraries/PHPExcel/PHPExcel.php');
        $phpExcel = PHPExcel_IOFactory::load($filePath); // Tải file đã xuất
        $worksheet = $phpExcel->getActiveSheet();
    
        // Định dạng auto width
        foreach (range('A', $worksheet->getHighestDataColumn()) as $column) {
            $worksheet->getColumnDimension($column)->setAutoSize(true);
        }
    
        // Định dạng text cho một cell cụ thể (ví dụ: A2)
        $worksheet->setCellValueExplicit('A2', 'Text Example', PHPExcel_Cell_DataType::TYPE_STRING);
    
        // Lưu lại file sau khi chỉnh sửa
        $phpExcelWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $phpExcelWriter->save($filePath);
    
        echo "File has been exported to: " . $filePath;
        
    }
    

    
}
