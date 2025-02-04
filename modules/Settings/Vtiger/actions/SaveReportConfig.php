<?php
/*
*	SaveReportConfig.php
*	Author: Phuc Lu
*	Date: 2020.03.30
*   Purpose: Create action to save Report Config
*/

class Settings_Vtiger_SaveReportConfig_Action extends Settings_Vtiger_Basic_Action {

    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }

    public function process(Vtiger_Request $request) {
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        $customerGroups = $request->get('customer_groups');

        if (isset($currentConfig['customer_groups']) && isset($currentConfig['customer_groups']['customer_groups_max_id'])) {
            $maxId = $currentConfig['customer_groups']['customer_groups_max_id'];
        }
        else {
            $maxId = 0;
        }

        foreach ($customerGroups as $key => $customerGroup) {
            if (!isset($customerGroup['group_id']) || empty($customerGroup['group_id'])) {
                $maxId++;
                $customerGroups[$key]['group_id'] = $maxId;
            }

            $customerGroups[$key]['from_value'] = CurrencyField::convertToDBFormat($customerGroup['from_value']);
            $customerGroups[$key]['to_value'] = CurrencyField::convertToDBFormat($customerGroup['to_value']);
            $customerGroups[$key]['alert_value'] = CurrencyField::convertToDBFormat($customerGroup['alert_value']);
        }

        $currentConfig = array(
            'sales_forecast' => [
                'min_successful_percentage' => $request->get('min_successful_percentage')
            ],
            'customer_groups' =>  [
                'customer_group_calculate_by' => $request->get('customer_group_calculate_by'),
                'customer_groups_max_id' => $maxId,
                'groups' => $customerGroups
            ]
        );

        Settings_Vtiger_Config_Model::saveConfig('report_config', $currentConfig);

        // Save config to db
        Accounts_Data_Helper::saveCustomerGroup($customerGroups,  $maxId);
        
        // Apply for all current account
        Accounts_Data_Helper::setCustomerGroupForAllAccounts();
        
        $respone = new Vtiger_Response();
        $respone->setResult(true);
        $respone->emit();
    }
}