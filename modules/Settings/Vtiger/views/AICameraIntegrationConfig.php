<?php

/*
    File: AICameraIntegrationConfig.php
    Author: Phu Vo
    Date: 2021.04.02
    Purpose: Process AICameraIntegrationConfig View
*/

class Settings_Vtiger_AICameraIntegrationConfig_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct();
	}

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_AI_CAMERA_INTEGRATION_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        require('integration_providers.php');
        checkAccessForbiddenFeature('AICameraIntegration');
        $aiProviders = $providers['ai_camera'];
        $targetView = $request->get('targetView');
        $provider = $request->get('provider');
        
        if ($targetView == 'ShowDetail' && !empty($provider)) {
            $this->showShowDetail($request);
            return;
        }

        $moduleName = $request->getModule(false);
        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);
        $activeProvider = '';

        if (!empty($config['credentials']['access_token'])) {
            $activeProvider = $config['active_provider'];
        }

        $language = Vtiger_Language_Handler::getLanguage() == 'vn_vn' ? 'vn' : 'en';

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('PROVIDERS', $aiProviders);
        $viewer->assign('INTRO_KEY', 'intro_' . $language);
        $viewer->assign('LANGUAGE', Vtiger_Language_Handler::getLanguage());
        $viewer->assign('MODE', 'ShowList');
        $viewer->assign('CONFIG', $config);
        $viewer->assign('ACTIVE_PROVIDER', $activeProvider);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/AICameraIntegrationConfig.tpl');
    } 

    protected function showShowDetail(Vtiger_Request $request) {
        require('integration_providers.php');

        $aiProviders = $providers['ai_camera'];
        $moduleName = $request->getModule(false);
        $provider = $request->get('provider');
        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);
        $activeProvider = '';

        if (!empty($config['credentials']['access_token'])) {
            $activeProvider = $config['active_provider'];
        }

        if (empty($activeProvider)) {
            header ('Location: index.php?module=Vtiger&parent=Settings&view=AICameraIntegrationConfig');
        }

        if ($provider != $activeProvider) {
            throw new Exception('LBL_AI_CAMERA_PROVIDER_IS_NOT_CONNECTED_ERROR_MSG', $moduleName);
        }

        $language = Vtiger_Language_Handler::getLanguage() == 'vn_vn' ? 'vn' : 'en';

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('PROVIDERS', $aiProviders);
        $viewer->assign('INTRO_KEY', 'intro_' . $language);
        $viewer->assign('MODE', 'ShowDetail');
        $viewer->assign('ACTIVE_PROVIDER', $activeProvider);
        $viewer->assign('CONFIG', $config);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/AICameraIntegrationConfig.tpl');
    }
}