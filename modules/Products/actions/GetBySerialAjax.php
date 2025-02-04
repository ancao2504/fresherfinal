<?php

class Products_GetBySerialAjax_Action extends Vtiger_Action_Controller {

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

        // Write your own logic to check for access permission
        $allowAccess = $currentUserPrivilegesModel->hasModulePermission($moduleModel->getId());

        if (!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }
    // function process(Vtiger_Request $request) {
    //     try {
    //         $serial = $request->get('serial');
    //         $result = Products_Record_Model::getInstanceBySerial($serial);
    //         $response = new Vtiger_Response();
    //         $response->setResult($result ? $result->getData() : null);
    //         $response->emit();
    //     } catch (Exception $e) {
    //         $response = new Vtiger_Response();
    //         $response->setError($e->getMessage());
    //         $response->emit();
    //     }
    // }
    function process(Vtiger_Request $request) {
        try {
            $serial = $request->get('serial');
            $result = Products_Record_Model::getInstanceBySerial($serial);
    
            $response = new Vtiger_Response();
            if ($result) {
                $response->setResult($result->getData());
            } else {
                $response->setResult(null);
            }
            $response->emit();
        } catch (Exception $e) {
            // Trả về lỗi
            $response = new Vtiger_Response();
            $response->setError($e->getMessage());
            $response->emit();
        }
    }
    
}
?>
