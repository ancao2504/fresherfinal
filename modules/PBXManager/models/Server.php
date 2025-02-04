<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

// Refactord by Hieu Nguyen on 2022-07-29
class PBXManager_Server_Model extends Vtiger_Base_Model{

	public static function getCleanInstance(){
		return new self; 
	}

	public static function getInstance(){
		$serverModel = new self();
		$gatewayConfig = PBXManager_Config_Helper::getGatewayConfig();
		
		if (!empty($gatewayConfig) && !empty($gatewayConfig['active_gateway'])) {
			$serverModel->set('gateway', $gatewayConfig['active_gateway']);
			$serverModel->set('parameters', $gatewayConfig['params']);

			foreach ($gatewayConfig['params'] as $fieldName => $fieldValue) {
				$serverModel->set($fieldName,$fieldValue);
			}
		}

		return $serverModel;
	}
	
	public static function checkPermissionForOutgoingCall() {
		if (!PBXManager_Config_Helper::isCallCenterEnabled()) return false;
		$permission = Users_Privileges_Model::isPermitted('PBXManager', 'MakeOutgoingCalls');
		if (!$permission) return false;

		$serverModel = PBXManager_Server_Model::getInstance();
		$gateway = $serverModel->get('gateway');
		
		if ($gateway) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public static function generateVtigerSecretKey() {
		return uniqid(rand());
	}
	
	// Added by Hieu Nguyen on 2022-07-28
	static function getConnectorByName($gatewayName) {
		$className = 'PBXManager_'. $gatewayName .'_Connector';
		return new $className();
	}

	// Modified by Hieu Nguyen on 2018-10-05
	public function getConnector() {
		$gatewayName = $this->get('gateway');
		if(empty($gatewayName)) return null;

		return self::getConnectorByName($gatewayName);
	}

	// Added by Hieu Nguyen on 2021-03-09
	public static function getActiveConnector() {
		static $connector = null;
		if ($connector) return $connector;

		$serverModel = self::getInstance();
		$connector = $serverModel->getConnector();
		return $connector;
	}

	// Added by Hieu Nguyen on 2022-07-28
	public static function getConnectorList() {
		$pattern = 'modules/PBXManager/connectors/*.php';
		$connectorList = ['cloud' => [], 'physical' => []];
		
		foreach (glob($pattern) as $connectorFile) {
			$gatewayName = basename($connectorFile, '.php');
			if ($gatewayName == 'PBXManager') continue;   // Skip default connector file from vTiger

			try {
				$connector = self::getConnectorByName($gatewayName);

				if (!empty($connector)) {
					if ($connector->isPhysicalDevice) {
						if (!isForbiddenFeature('PhysicalCallCenterIntegration')) {
							$connectorList['physical'][$gatewayName] = $connector;
						}
					}
					else {
						if (!isForbiddenFeature('CloudCallCenterIntegration')) {
							$connectorList['cloud'][$gatewayName] = $connector;
						}
					}
				}
			}
			catch (Exception $ex) {
				// Nothing to do for now
			}
		}

		return $connectorList;
	}
}
