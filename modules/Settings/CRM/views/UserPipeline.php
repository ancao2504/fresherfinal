<?php
class Settings_CRM_UserPipeline_View extends Settings_CRM_BaseConfig_View
{
    public function getPageTitle(CRM_Request $request)
    {
        return "Pipeline";
    }

    public function checkPermission(CRM_Request $request)
    {
        return true;
    }

    public function process(CRM_Request $request)
    {
        global $current_user;
        $moduleName = 'CPNotifications';
        $config = Users_Preferences_Model::loadPreferences($current_user->id, 'notification_config');

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/CRM/tpls/UserNotifications.tpl');
    }
}
