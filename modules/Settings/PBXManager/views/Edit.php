<?php
//ini_set('display_errors','on'); error_reporting(E_ALL);
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
Class Settings_PBXManager_Edit_View extends Vtiger_Edit_View {

     function __construct() {
        $this->exposeMethod('showPopup');
    }

    public function process(Vtiger_Request $request) {
            $this->showPopup($request);
    }
    
    public function showPopup(Vtiger_Request $request) {
        // Added by Hieu Nguyen on 2019-12-17 to prevent changing Call Center Gateway config if this feature is not available in current CRM package
        checkAccessForbiddenFeature('CallCenterIntegration');
        // End Hieu Nguyen

        $id = $request->get('id');
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        if($id){
            $recordModel = Settings_PBXManager_Record_Model::getInstanceById($id, $qualifiedModuleName);
            $gateway = $recordModel->get('gateway');
        }else{
            $recordModel = Settings_PBXManager_Record_Model::getCleanInstance();
            $recordModel->set('gateway', 'PBXManager');  // Modified by Hieu Nguyen on 2018-10-05 to set default gateway
        }

        // Modified by Hieu Nguyen on 2018-10-03
        if($request->get('gateway')) {
            $recordModel->set('gateway', $request->get('gateway'));
        }

        $connectorList = Settings_PBXManager_Module_Model::getConnectorList();
        $gatewayOptions = '';

        foreach($connectorList as $connector) {
            $gatewayName = $connector->getGatewayName();
            $selected = ($recordModel && $recordModel->get('gateway') == $gatewayName) ? 'selected' : '';
            $gatewayOptions .= "<option value='{$gatewayName}' {$selected}>{$gatewayName}</option>";
        }

        $viewer->assign('GATEWAY_OPTIONS', $gatewayOptions);

        if($recordModel->get('gateway') == null) {
            $recordModel->set('gateway', 'PBXManager');
        }
        // End Hieu Nguyen

        $viewer->assign('RECORD_ID', $id);
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('MODULE', $request->getModule(false));
        $viewer->view('Edit.tpl', $request->getModule(false));
    }
    
}
