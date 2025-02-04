<?php

class Products_CheckWarrantyAjax_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct($isFullView = false);
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $allowAccess = true; // Set this to false nếu vai trò của người dùng không được phép truy cập

        if (!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request) {
        $matchedProduct = Products_Record_Model::getInstanceBySerial($request->get('serial'));

        if ($matchedProduct == null || $matchedProduct->get('productid') == '') {
            echo vtranslate('LBL_WARRANTY_SERIAL_NOT_FOUND', 'Products');
            return;
        }

        $warrantyStatus = vtranslate('LBL_WARRANTY_STATUS_VALID', 'Products');
        $statusLabel = 'label-success';

        // Bảo hành hết hạn nếu ngày hết hạn đã qua
        if (strtotime($matchedProduct->get('expiry_date')) < strtotime(date('Y-m-d'))) {
            $warrantyStatus = vtranslate('LBL_WARRANTY_STATUS_ENDED', 'Products');
            $statusLabel = 'label-danger';
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('PRODUCT_RECORD', $matchedProduct);
        $viewer->assign('WARRANTY_STATUS', $warrantyStatus);
        $viewer->assign('STATUS_LABEL', $statusLabel);
        $result = $viewer->fetch('modules/Products/tpls/CheckWarrantyResult.tpl');

        echo $result;
    }
}
