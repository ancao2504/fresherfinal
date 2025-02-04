<?php
/**
 * Author: Phu Vo
 * Date: 2019.04.12
 * Purpose: External Report View processor
 */

class PBXManager_ExternalReport_View extends CustomView_Base_View {

    var $connector = null;

	function __construct() {
        parent::__construct($isFullView = true);

        $this->loadActiveConnector();
    }

    protected function loadActiveConnector() {
        $serverModel = PBXManager_Server_Model::getInstance();
        $this->connector = $serverModel->getConnector();

        if($this->connector == null) {
            throw new AppException(vtranslate('LBL_CONNECTOR_NOT_FOUND'));
        }
    }

	function checkPermission(Vtiger_Request $request) {
		if(!PBXManager_ExternalReport_Helper::isUserHasPermission()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
        $connectorName = $this->connector->getGatewayName();
        $templatePath = $this->getReportTemplatePath();

		$viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CONNECTOR', $connectorName);
        $viewer->assign('REPORT_TITLE', $this->getReportTitle($connectorName));

        $this->extraProcess($request);

		$viewer->display($templatePath);
    }

    function extraProcess(Vtiger_Request $request) {
        $connectorName = $this->connector->getGatewayName();

        // Process for Custom Title
        $subMethodName = "extraProcess{$connectorName}";

        if (method_exists($this, $subMethodName)) {
            $this->$subMethodName($request);
        }
    }

    function extraProcessYeaStar(Vtiger_Request $request) {
        // Detail date value
        $todayDateTime = new DateTimeField(null);

        // special validator
        $validator = [['name' => 'greaterThanDependentField', 'params' => ['starttime']]];

        $viewer = $this->getViewer($request);

        $viewer->assign('TODAY_DATETIME', $todayDateTime);
        $viewer->assign('SPECIAL_VALIDATOR', $validator);
    }
    
    /**
     * Method that return Report Title
     */
    function getReportTitle($connectorName) {
        // Process for Custom Title
        $subMethodName = "get{$connectorName}Title";

        if (method_exists($this, $subMethodName)) {
            return $this->$subMethodName($connectorName);
        }

        return replaceKeys(vtranslate('LBL_REPORT_TITLE', 'PBXManager'), ['%name' => $connectorName]);
    }

    /**
     * Method that return FreePBX Report Title
     */
    function getFreePBXTitle() {
        return replaceKeys(vtranslate('LBL_REPORT_TITLE', 'PBXManager'), ['%name' => $this->connector->deviceBrand]);
    }

    // Method that return Conector Report Template Path
    function getReportTemplatePath() {
        $connectorName = $this->connector->getGatewayName();
        return "modules/PBXManager/tpls/{$connectorName}Report.tpl";
    }
}