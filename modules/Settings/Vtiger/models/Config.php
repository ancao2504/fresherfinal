<?php

/*
	Config_Model
	Author: Hieu Nguyen
	Date: 2018-11-23
	Purpose: provide util functions to handle custom configs
*/

class Settings_Vtiger_Config_Model extends Vtiger_Base_Model {

    static function saveConfig($category, $config) {
        global $adb;

        $sql = "SELECT COUNT(category) FROM vtiger_config WHERE category = ?";
        $params = [$category];
        $isExists = $adb->getOne($sql, $params);

        if($isExists) {
            $sql = "UPDATE vtiger_config SET value = ? WHERE category = ?";
            $params = [json_encode($config), $category];
        }
        else {
            $sql = "INSERT INTO vtiger_config(category, value) VALUES(?, ?)";
            $params = [$category, json_encode($config)];
        }

        $adb->pquery($sql, $params);
    }

    static function loadConfig($category, $toArray = false) {
        global $adb;

        $sql = "SELECT value FROM vtiger_config WHERE category = ?";
        $params = [$category];
        $config = $adb->getOne($sql, $params);

        return json_decode($config, $toArray);
    }
}