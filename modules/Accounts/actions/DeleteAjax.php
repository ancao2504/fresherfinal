<?php

/**
 * Account DeleteAjax Action
 * Author: Phu Vo
 * Date: 2019.09.16
 */

class Accounts_DeleteAjax_Action extends Vtiger_DeleteAjax_Action {

    public function checkPermission(Vtiger_Request $request) {
        // Prevent Delete Personal Account
        if (Accounts_Record_Model::isPersonalAccount($request->get('record'))) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }
        parent::checkPermission($request);
    }
    function process(Vtiger_Request $request) {
      
        // Find matched product
        $result = Accounts_Record_Model::deleteAccounts($request->get('accountid'));
       
        $response = new Vtiger_Response();
        $response->setResult($result);  // Trả về cả 'check' và 'message'
        $response->emit();
    }
}