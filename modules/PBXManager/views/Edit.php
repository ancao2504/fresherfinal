<?php

/**
 * @author Phu Vo
 * Data: 2019.05.17
 * Purpose PBXManager Edit View Controller
 */

class PBXManager_Edit_View extends Vtiger_Edit_View {
    
    public function checkPermission(Vtiger_Request $request) {
        throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
    }
}