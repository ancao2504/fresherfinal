<?php

class Accounts_UIcomponent_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct($isFullView = true);
    }

    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        // Write your own logic to check for access permission
        $allowAccess = true; // Set this to false if a user's role is not permitted

        if(!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request) {
        
        $viewer = $this->getViewer($request);
        $viewer->assign('BOOTSTRAP_SWITCH_CSS', vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrapswitch.min.css'));
        $viewer->assign('BOOTSTRAP_SWITCH_JS', vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js'));

        $viewer->display('modules/Accounts/tpls/UIcomponent.tpl');
    }
}
?>
