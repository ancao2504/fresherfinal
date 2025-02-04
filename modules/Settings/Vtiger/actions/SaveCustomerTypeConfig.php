<?php

/**
 * Name: SaveCustomerTypeConfig.php
 * Author: Phu Vo
 * Date: 2021.03.19
 * Description: Handle save action for Customer Type Config
 */

class Settings_Vtiger_SaveCustomerTypeConfig_Action extends Settings_Vtiger_Basic_Action {

    function process(Vtiger_Request $request) {
        $config = $request->get('config');

        Settings_Vtiger_Config_Model::saveConfig('customer_type', $config);

        // Response
        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }
}