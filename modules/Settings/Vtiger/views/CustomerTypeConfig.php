<?php

/**
 * Name: CustomerTypeConfig.php
 * Author: Phu Vo
 * Date: 2020.03.19
 * Description: Provide a view for customer type config user interface
 */

class Settings_Vtiger_CustomerTypeConfig_View extends Settings_Vtiger_BaseConfig_View {

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_CUSTOMER_TYPE_CONFIG', $moduleName);
    }

    function process(Vtiger_Request $request) {
        global $current_user;

        $moduleName = $request->getModule(false);
        $config = Settings_Vtiger_Config_Model::loadConfig('customer_type', true);

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/CustomerTypeConfig.tpl');
    }
}