<?php

class Accounts_ListPipeline_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct($isFullView = true);
    }
    function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $allowAccess = true;

        if(!$allowAccess) {
            throw new AppException(vtranslate($moduleName, $moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
        }
    }

    function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);
        $viewer->display('modules/Accounts/tpls/ListPipeline.tpl');
      
    }
}
?>
