<?php
class Settings_CRM_SystemNotifications_View extends Settings_CRM_BaseConfig_view {
    public function getPageTitle(CRM_Request $request) {
        return vtranslate('LBL_CONFIG_SYSTEM_NOTIFICATION_TITLE', 'CPNotifications');
    }
    public function process(CRM_Request $request) {
        $config = Settings_CRM_Config_Model::loadConfig('notification_config');
        $moduleName = 'CPNotifications';
        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        // $viewer->display('modules/Settings/CRM/tpls/SystemNotifications.tpl');
    }
}