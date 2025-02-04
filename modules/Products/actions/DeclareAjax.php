<?php

class Products_DeclareAjax_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        // Viết logic của bạn để kiểm tra quyền truy cập
        $allowAccess = $currentUserPrivilegesModel->hasModulePermission($moduleModel->getId());

        if (!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    // function process(Vtiger_Request $request) {
    //     if ($request->isAjax()) {
    //         // Xử lý yêu cầu
    //         $productName = $request->get('product_name');
    //         $serialNo = $request->get('serial_no');
    //         $warrantyStartDate = $request->get('warranty_start_date');
    //         $warrantyEndDate = $request->get('warranty_end_date');
    //         $website = $request->get('website');
    //         $productId = Products_Record_Model::declareProduct($productName, $serialNo, $warrantyStartDate, $warrantyEndDate, $website);

    //         // Trả về kết quả
    //         $result = array('success' => $productId ? true : false);
    //         $response = new Vtiger_Response();
    //         $response->setResult($result);
    //         $response->emit();
    //     }
    // }
    function process(Vtiger_Request $request) {
        if ($request->isAjax()) {
            $response = new Vtiger_Response();
            
            try {
                // Xử lý yêu cầu
                $productName = $request->get('product_name');
                $serialNo = $request->get('serial_no');
                $warrantyStartDate = $request->get('warranty_start_date');
                $warrantyEndDate = $request->get('warranty_end_date');
                $website = $request->get('website');
                
                $productId = Products_Record_Model::declareProduct($productName, $serialNo, $warrantyStartDate, $warrantyEndDate, $website);
    
                // Trả về kết quả thành công
                $response->setResult(array(
                    'success' => true,
                    'productId' => $productId
                ));
                
            } catch (Exception $e) {
                // // Xử lý lỗi khi trùng serial
                // $response->setError($e->getMessage());
                 // Phân loại lỗi trùng Serial
            if ($e->getCode() === 409) {
                $response->setResult([
                    'success' => false,
                    'type' => 'duplicate_serial',
                    'message' => $e->getMessage()
                ]);
            } else {
                // Các lỗi khác
                $response->setError($e->getMessage());
            }
            }
            
            $response->emit();
        }
    }
}
?>
