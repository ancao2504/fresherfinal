<?php

/*
*	ReportConfig.php
*	Author: Phuc Lu
*	Date: 2019.06.25
*   Purpose: Create view for Report Config
*/

class Settings_Vtiger_ReportConfig_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_REPORT_CONFIG_REPORT_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $baseCurrency = Settings_Currency_Record_Model::getBaseCurrency();
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        // Set default values
        if (empty($currentConfig)) {
            $currentConfig = array(
                'sales_forecast' => [
                    'min_successful_percentage' => 80
                ],
                'customer_groups' =>  [
                    'customer_group_calculate_by' => 'cummulation',
                    'groups' => []
                ]
            );
        }

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('BASE_CURRENCY', $baseCurrency);
        $viewer->assign('CURRENT_CONFIG', $currentConfig);
        $viewer->display('modules/Settings/Vtiger/tpls/ReportConfig.tpl');
    }
}