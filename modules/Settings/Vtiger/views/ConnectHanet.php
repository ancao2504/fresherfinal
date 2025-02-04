<?php

/*
    File: ConnectHanet.php
    Author: Phu Vo
    Date: 2021.04.02
    Purpose: Process ConnectHanet View
*/

class Settings_Vtiger_ConnectHanet_View extends Settings_Vtiger_BaseConfig_View {

	function __construct() {
		parent::__construct();
	}

	public function preProcess (Vtiger_Request $request, $display=true) {
        return;
    }
    
	public function postProcess (Vtiger_Request $request) {
        return;
    }
    
	public function getHeaderScripts(Vtiger_Request $request) {
        return [];
    }
    
    function getHeaderCss(Vtiger_Request $request) {
        return [];
    }

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_AI_CAMERA_INTEGRATION_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $targetView = $request->get('targetView');

        if ($targetView == 'PlaceList') {
            $this->viewPlaceList($request);
            return;
        }

        if ($targetView == 'CameraList') {
            $this->viewCameraList($request);
            return;
        }

        if ($targetView == 'Complete') {
            $this->viewComplete($request);
            return;
        }

        $authCode = $request->get('code');
        $connector = new CPAICameraIntegration_HanetAICamera_Connector();
        $accessToken = $connector->getAccessToken($authCode);

        if (!$accessToken) {
            throw new Exception(vtranslate('LBL_AI_CAMERA_ACCESS_TOKEN_NOT_FOUND_ERROR_MSG', $moduleName));
        }

        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);
        $config['credentials'] = $accessToken;
        Settings_Vtiger_Config_Model::saveConfig('ai_camera_config', $config);

        header ('Location: index.php?module=Vtiger&parent=Settings&view=ConnectHanet&targetView=PlaceList');
    }

    protected function viewPlaceList(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $connector = new CPAICameraIntegration_HanetAICamera_Connector();
        $places = $connector->getPlaces();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODE', 'PlaceList');
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('PLACES', $places);
        $viewer->display('modules/Settings/Vtiger/tpls/ConnectHanet.tpl');
    }

    protected function viewCameraList(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $placeId = $request->get('placeId');
        $placeName = $request->get('placeName');
        $placeAddress = $request->get('placeAddress');
        $connector = new CPAICameraIntegration_HanetAICamera_Connector();
        $cameras = $connector->getCameras($placeId);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODE', 'CameraList');
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('PLACE_ID', $placeId);
        $viewer->assign('PLACE_NAME', $placeName);
        $viewer->assign('PLACE_ADDRESS', $placeAddress);
        $viewer->assign('CAMERAS', $cameras);
        $viewer->display('modules/Settings/Vtiger/tpls/ConnectHanet.tpl');
    }

    protected function viewComplete(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $placeData = $request->get('place_data');
        $config = Settings_Vtiger_Config_Model::loadConfig('ai_camera_config', true);
        
        if (empty($config['cameras'])) $config['cameras'] = [];
        $config['cameras'][$placeData['id']] = $placeData;
        Settings_Vtiger_Config_Model::saveConfig('ai_camera_config', $config);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODE', 'Complete');
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->display('modules/Settings/Vtiger/tpls/ConnectHanet.tpl');
    }
}