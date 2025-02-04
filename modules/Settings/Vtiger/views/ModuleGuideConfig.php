<?php

/*
    View: ModuleGuideConfig
    Author: Hieu Nguyen
    Date: 2021-01-18
    Purpose: Config user guide for all modules
*/

class Settings_Vtiger_ModuleGuideConfig_View extends Settings_Vtiger_BaseConfig_View {

    function __construct() {
        parent::__construct($isFullView = true);
    }

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_MODULE_GUIDE_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $allModules = getModulesTranslatedSingleLabel();
        
        // Hidden modules
        $hiddenModules = ['Events', 'ModComments'];
        
        foreach ($hiddenModules as $hiddenModuleName) {
            unset($allModules[$hiddenModuleName]);
        }

        // Addition modules
        $additionModules = [
            'Home' => vtranslate('Home', 'Home'),
            'Reports' => vtranslate('Reports', 'Reports'),
            'Calendar' => vtranslate('Calendar', 'Calendar'),
            'Documents' => vtranslate('Documents', 'Documents'),
            'EmailTemplates' => vtranslate('EmailTemplates', 'EmailTemplates'),
            'RecycleBin' => vtranslate('RecycleBin', 'RecycleBin'),
        ];

        $allModules = array_merge($allModules, $additionModules);

        // Load config        
        $config = Settings_Vtiger_Config_Model::loadConfig('module_guide');

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('ALL_MODULES', $allModules);
        $viewer->display('modules/Settings/Vtiger/tpls/ModuleGuideConfig.tpl');
    } 
}