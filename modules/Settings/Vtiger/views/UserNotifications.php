<?php

/*
    File: UserNotifications.php
    Author: Phu Vo
    Date: 2019.03.22
    Purpose: Process User Notification Config View
*/

class Settings_Vtiger_UserNotifications_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        return vtranslate('LBL_CONFIG_USER_NOTIFICATION_TITLE', 'CPNotifications');
    }

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    public function process(Vtiger_Request $request) {
        global $current_user;

        $moduleName = 'CPNotifications';
        $config = CPNotifications_Data_Model::loadUserConfig();

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/UserNotifications.tpl');
    } 
}