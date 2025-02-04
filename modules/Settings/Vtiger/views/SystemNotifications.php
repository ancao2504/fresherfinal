<?php

/*
    File: SystemNotifications.php
    Author: Phu Vo
    Date: 2019.03.22
    Purpose: Process System Notification Config View
*/

class Settings_Vtiger_SystemNotifications_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        return vtranslate('LBL_CONFIG_SYSTEM_NOTIFICATION_TITLE', 'CPNotifications');
    }

    public function process(Vtiger_Request $request) {
        $config = Settings_Vtiger_Config_Model::loadConfig('notification_config');
        $moduleName = 'CPNotifications';

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/SystemNotifications.tpl');
    } 
}