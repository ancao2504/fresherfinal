<?php

/*
    File: RelatedActivitiesConfig.php
    Author: Phu Vo
    Date: 2020.08.29
    Purpose: Process System Notification  View
*/

class Settings_Vtiger_RelatedActivitiesConfig_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_RELATED_ACTIVITIES_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $configs = Settings_Vtiger_Config_Model::loadConfig('related_activities_config', true);
        $moduleName = $request->getModule(false);

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIGS', $configs);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/RelatedActivitiesConfig.tpl');
    } 
}