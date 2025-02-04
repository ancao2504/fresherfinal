<?php
class Settings_CRM_UserNotifications_View extends Settings_CRM_BaseConfig_View 
{
 public function getPageTitle(Vtiger_Request $request) {
 return vtranslate('LBL_CONFIG_USER_NOTIFICATION_TITLE', 'CPNotifications');
 }
 function checkPermission(Vtiger_Request $request) {
 return true;
 }
 public function process(Vtiger_Request $request) {
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

