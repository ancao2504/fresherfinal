<?php

/**
 * Synchronize Customer Info Config
 * Author: Phu Vo
 * Date: 2020.06.24
 * Description: Provide View Layer for handle SyncCustomerInfo screen
 */

require_once('include/utils/SyncCustomerInfoUtils.php');

class Settings_Vtiger_SyncCustomerInfoConfig_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_SYNC_CUSTOMER_INFO_CONFIG_PAGE_TITLE', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $configs = SyncCustomerInfoUtils::getConfigs();
        $moduleName = $request->getModule(false);

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIGS', $configs);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/SyncCustomerInfoConfig.tpl');
    }
}