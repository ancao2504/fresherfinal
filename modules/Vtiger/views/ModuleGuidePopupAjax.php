<?php

/*
    View: ModuleGuidePopupAjax
    Author: Hieu Nguyen
    Date: 2020-01-18
    Purpose: handle ajax request to load Module Guide Popup
*/

class Vtiger_ModuleGuidePopupAjax_View extends Vtiger_Basic_View {

	function process(Vtiger_Request $request) {
        global $current_user;
        $targetModule = $request->get('target_module');
        $systemConfig = Settings_Vtiger_Config_Model::loadConfig('module_guide', true) ?? [];
        $userPreference = Users_Preferences_Model::loadPreferences($current_user->id, 'module_guide', true) ?? [];
        $viewer = $this->getViewer($request);

        // Popup title
        $translatedModuleName = vtranslate($targetModule, $targetModule);
        $viewer->assign('POPUP_TITLE', vtranslate('LBL_MODULE_GUIDE_POPUP_TITLE', 'Vtiger', ['%module_name' => $translatedModuleName]));

        // System module guide
        if (!empty($systemConfig[$targetModule])) {
            $viewer->assign('GUIDE_CONTENT', $systemConfig[$targetModule]);
        }

        // User preference
        $viewer->assign('SHOW_NEXT_TIME', true);

        if ($userPreference[$targetModule] === '0') {
            $viewer->assign('SHOW_NEXT_TIME', false);
        }

        $viewer->display('modules/Vtiger/tpls/ModuleGuidePopup.tpl');
    }
}