<?php

/**
 * Accounts Edit View
 * Author: Phu Vo
 * Date: 2019.09.16
 */

class Accounts_Edit_View extends Vtiger_Edit_View {

    public function checkPermission(Vtiger_Request $request) {
        // Prevent Edit Personal Account
        if (Accounts_Record_Model::isPersonalAccount($request->get('record'))) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
        }

        parent::checkPermission($request);
    }
}