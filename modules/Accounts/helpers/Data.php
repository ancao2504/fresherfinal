<?php

class Accounts_Data_Helper {
    static function getPersonalAccountId() {
        global $adb;

        $sql = "SELECT accountid
            FROM vtiger_account AS a
            INNER JOIN vtiger_crmentity AS e ON (e.crmid = a.accountid AND e.deleted = 0)
            WHERE account_no = ?";

        return $adb->getOne($sql, ['PACC']);
    }

    static function getPersonalAccount() {
        return Vtiger_Record_Model::getInstanceByConditions('Accounts', ['account_no' => 'PACC']);
    }

    // Added by Phuc on 2020.05.11
    static function getSalesByAccount($accountId, $getType = '') {
        global $adb;
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);

        if (empty($getType)) {
            if (isset($currentConfig['customer_groups']) && isset($currentConfig['customer_groups']['customer_group_calculate_by'])) {
                $getType = $currentConfig['customer_groups']['customer_group_calculate_by'];
            }
            else {
                $getType = 'cummulation';
            }
        }

        $extSql = '';

        if ($getType == 'year') {
            $fromDate = Date('Y-01-01 00:00:00');
            $toDate = Date('Y-12-31 23:59:59');
            $extSql = "AND createdtime BETWEEN '{$fromDate}' AND '{$toDate}'";
        }

        $sql = "SELECT SUM(total) AS sales
            FROM vtiger_salesorder
            INNER JOIN vtiger_crmentity ON (salesorderid = crmid AND deleted = 0)
            WHERE sostatus NOT IN ('Created', 'Cancelled') AND accountid = ? {$extSql}";

        $sales = $adb->getOne($sql, [$accountId]);

        if (empty($sales)) {
            return 0;
        }

        return (float)$sales;
    }

    static function setCustomerGroupForAccount($accountId) {
        global $adb;
        
        $currentConfig = Settings_Vtiger_Config_Model::loadConfig('report_config', true);
        $sales = self::getSalesByAccount($accountId);

        if (!isset($currentConfig['customer_groups']) || !isset($currentConfig['customer_groups']['groups']) || !count($currentConfig['customer_groups']['groups'])) {
            return false;
        }

        $groupName = '';

        foreach ($currentConfig['customer_groups']['groups'] as $group) {
            $toValue = (float)(CurrencyField::convertToDBFormat($group['to_value']));
            $groupName = $group['group_name'];

            if ($sales < $toValue) {
                break;
            }
        }

        $sql = "UPDATE vtiger_account SET accounts_customer_group = ? WHERE accountid = ?";
        $adb->pquery($sql, [$groupName, $accountId]);
    }

    static function setCustomerGroupForAllAccounts() {
        global $adb;

        $sql = "SELECT GROUP_CONCAT(accountid) FROM vtiger_account INNER JOIN vtiger_crmentity ON (deleted = 0 AND crmid = accountid)";
        $accountIds = $adb->getOne($sql, []);

        if (!empty($accountIds)) {
            $accountIds = explode(',', $accountIds);

            foreach ($accountIds as $accountId) {
                self::setCustomerGroupForAccount($accountId);
            }
        }
    }

    static function saveCustomerGroup($groups, $maxId) {
        global $adb;

        $sql = "DELETE FROM vtiger_accounts_customer_group";
        $adb->pquery($sql);

        // Add new rows
        $order = 0;

        foreach ($groups as $group) {
            $sql = "INSERT INTO vtiger_accounts_customer_group VALUES (?, ?, ?, '1', null)";
            $adb->pquery($sql, [$group['group_id'], $group['group_name'], $order]);
            $order++;
        }

        $sql = "UPDATE vtiger_accounts_customer_group_seq SET id = ?";
        $adb->pquery($sql, [$maxId]);
    }    
    // Ended by Phuc
}