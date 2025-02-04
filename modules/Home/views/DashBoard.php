<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Home_DashBoard_View extends Vtiger_DashBoard_View {

    public function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = array(
            '~modules/Home/resources/DashboardConfig.js',
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }

    public function getHeaderCss(Vtiger_Request $request) {
        $parentHeaderCssScriptInstances = parent::getHeaderCss($request);

        $headerCss = array(
            '~modules/Home/resources/DashboardConfig.css',
        );
        $cssScripts = $this->checkAndConvertCssStyles($headerCss);
        $headerCssScriptInstances = array_merge($parentHeaderCssScriptInstances, $cssScripts);
        return $headerCssScriptInstances;
    }
}