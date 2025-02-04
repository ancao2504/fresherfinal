<?php

/*
*	SaveMauticIntegrationConfig.php
*	Author: Phuc Lu
*	Date: 2019.06.23
*   Purpose: Create action to save Mautic Config
*/

// Refactored by Hieu Nguyen on 2021-11-15
class Settings_Vtiger_SaveMauticIntegrationConfig_Action extends Settings_Vtiger_Basic_Action {

	function __construct() {
		$this->exposeMethod('saveSettings');
		$this->exposeMethod('disconnect');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	public function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess();
	}

	// Modified by Hieu Nguyen on 2021-11-15
	public function saveSettings(Vtiger_Request $request) {
		require_once('include/utils/MauticUtils.php');
		require_once('include/utils/CustomConfigUtils.php');
		$config = $request->get('config');
		$currentConfig = CPMauticIntegration_Config_Helper::loadConfig();

		// Save default config to db
		$currentConfig['batch_limit'] = $config['batch_limit'];
		$currentConfig['sync_mautic_history_within_days'] = $config['sync_mautic_history_within_days'];
		$currentConfig['sync_mautic_history_when_customer_is_converted'] = $config['sync_mautic_history_when_customer_is_converted'];
		$currentConfig['delete_contact_in_mautic_when_delete_in_crm'] = isset($config['delete_contact_in_mautic_when_delete_in_crm']) ? $config['delete_contact_in_mautic_when_delete_in_crm'] : 0;
		
		$currentConfig['mapping_fields'] = [
			'cptarget' => $config['mapping_field_cptarget'],
			'leads' => $config['mapping_field_leads'],
			'contacts' => $config['mapping_field_contacts'],
		];

		$currentConfig['mapping_stages'] = [];
		
		foreach ($config['mapping_stages'] as $stage) {
			$stages = [];

			foreach ($stage['stages'] as $key => $value) {
				$stages[$value['crm']] = $value['mautic'];
			}

			$currentConfig['mapping_stages'][$stage['module']] = $stages;
		}
		
		$currentConfig['mapping_stages_segments'] = [];

		foreach ($config['mapping_stages_segments'] as $stageSegment) {
			$currentConfig['mapping_stages_segments']['x' . $stageSegment['stage']] = $stageSegment;
		}

		Settings_Vtiger_Config_Model::saveConfig('mautic_integration_config', $currentConfig);

		// Save custom config to file
		$customConfigs = ['mauticConfig.min_points_to_sync_data' => $config['min_points_to_sync_data']];
		CustomConfigUtils::saveCustomConfigs($customConfigs);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}

	// Added by Hieu Nguyen on 2021-11-15
	public function disconnect(Vtiger_Request $request) {
		CPMauticIntegration_Config_Helper::saveConfig([]);

		$response = new Vtiger_Response();
		$response->setResult(array('success' => 1));
		$response->emit();
	}
}
