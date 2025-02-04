<?php

class Home_Component_View extends CustomView_Base_View {

	function __construct() {
		parent::__construct();
	}

    function checkPermission(Vtiger_Request $request) {
        return true;
    }

    function process(Vtiger_Request $request) {
      $moduleName = $request->getModule();

      $viewer = $this->getViewer($request);
      $viewer->assign('MODULE', $moduleName);
      $viewer->display("modules/Home/tpls/Component.tpl");
    }

    /**
     * Function to get the list of Script models to be included
     * @param Vtiger_Request $request
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request) {
      $headerScriptInstances = parent::getHeaderScripts($request);
  
      $jsFileNames = array(
        '~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js',
      );
  
      $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
      $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
      return $headerScriptInstances;
    }

    function getHeaderCss(Vtiger_Request $request) {
      $headerCssInstances = parent::getHeaderCss($request);
      $moduleName = $request->getModule();
      $cssFileNames = array(
        '~/libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css',
      );
      $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
      $headerCssInstances = array_merge($cssInstances, $headerCssInstances);
      return $headerCssInstances;
    }
}