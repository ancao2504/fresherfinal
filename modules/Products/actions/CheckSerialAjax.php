<?php

class Products_CheckSerialAjax_Action extends Vtiger_Action_Controller {

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

    function process(Vtiger_Request $request) {
        try {
            $serial = $request->get('serial');
            $isSerialValid = Products_Record_Model::checkSerial($serial);
           $checkSerial = [
            'check' => $isSerialValid,
            ];
            $response = new Vtiger_Response();
            $response->setResult($checkSerial);
            $response->emit();
        } catch (Exception $e) {
            $response = new Vtiger_Response();
            $response->setError($e->getMessage());
            $response->emit();
        }
    }
}
?>
