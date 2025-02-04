<?php

/*
    EntryPoint structure
    Author: Hieu Nguyen
    Date: 2018-08-24
    Purpose: provide an entry point structure similar to SugarCRM
    Usage:
        - Copy this file into a new file, then rename the file name and class name that is corresponding to your logic
        - When you access /entrypoint.php?name=<Entry-Point-Name>, the entry point inside include/EntryPoints/<Entry-Point-Name>.php will be activated
*/
require_once('include/utils/MobileApiUtils.php');
class CheckWarranty extends Vtiger_EntryPoint {

    function process(Vtiger_Request $request) {
        $viewer = new Vtiger_Viewer();
        if (!empty($_POST)) {
          
            $matchedProduct = Products_Record_Model::getInstanceBySerial($request->get('serial'));
            $viewer->assign('RESULT', $this->renderResult($matchedProduct));
        }
        $viewer->display('include/EntryPoints/tpls/CheckWarranty.tpl');
        echo "Hello";
    }
    function renderResult($matchedProduct) {
        if ($matchedProduct == null || $matchedProduct->get('productid') == '') {
            // echo vtranslate('LBL_WARRANTY_SERIAL_NOT_FOUND', 'Products');
            return vtranslate('LBL_WARRANTY_SERIAL_NOT_FOUND', 'Products');
        }
        $warrantyStatus = vtranslate('LBL_WARRANTY_STATUS_VALID', 'Products');
        $statusLabel = 'label-success';

        // Warranty is ended if the expiry date is passed
        if (strtotime($matchedProduct->get('expiry_date')) < strtotime(date('Y-m-d'))) {
            $warrantyStatus = vtranslate('LBL_WARRANTY_STATUS_ENDED', 'Products');
            $statusLabel = 'label-danger';
        }
    
        $viewer = new Vtiger_Viewer();
        $viewer->assign('PRODUCT_RECORD', $matchedProduct);
        $viewer->assign('WARRANTY_STATUS', $warrantyStatus);
        $viewer->assign('STATUS_LABEL', $statusLabel);
        $result = $viewer->fetch('modules/Products/tpls/CheckWarrantyResult.tpl');
    
        return $result;
    }
}