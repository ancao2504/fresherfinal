<?php

/*
*	MauticIntegrationConfig.php
*	Author: Phuc Lu
*	Date: 2019.06.25
*   Purpose: Create view for Mautic Integration Config
*   Run the following sql to create index menu for mautic integration. Remember to update fieldid with new database
*   INSERT INTO `vtiger_settings_field` (`fieldid`, `blockid`, `name`, `iconpath`, `description`, `linkto`, `sequence`, `active`, `pinned`, `allow_non_admin`) VALUES ('42', '6', 'LBL_MAUTIC_INTEGRATION_CONFIG', 'NULL', 'NULL', 'index.php?module=Vtiger&parent=Settings&view=MauticIntegrationConfig', '2', '0', '0', '1');
*/

// Refactored by Hieu Nguyen on 2021-11-15
class Settings_Vtiger_MauticIntegrationConfig_View extends Settings_Vtiger_BaseConfig_View {

	function getPageTitle(Vtiger_Request $request) {
		$qualifiedName = $request->getModule(false);
		return vtranslate('LBL_MAUTIC_INTEGRATION_CONFIG', $qualifiedName);
	}

	public function process(Vtiger_Request $request) {
		require_once('integration_providers.php');

		// Added by Phuc Lu on 2019.12.19 to prevent accessing Mautic Config if this feature is not available in current CRM packag
		checkAccessForbiddenFeature('MauticIntegration');
		// Ended by Phuc

		$connected = false;

		// Load current config
		$currentConfig = $this->getConfig();
		$viewer = $this->getViewer($request);
		$qualifiedName = $request->getModule(false);

		$viewer->assign('CONFIG', $currentConfig);
		$viewer->assign('MODULE_NAME', $qualifiedName);
		$viewer->assign('GUIDE_URL', $providers['mautic']['guide_url']);

		if (CPMauticIntegration_Config_Helper::hasConfig()) {
			$connected = CPMauticIntegration_Config_Helper::checkConnection();

			if ($connected) {
				// Get contact fields
				$mauticContactFields = CPMauticIntegration_Data_Helper::getContactFieldList();
				$requiredFields = CPMauticIntegration_Data_Helper::getContactRequiredFields();
				$mappingRequiredFields = CPMauticIntegration_Data_Helper::getContactMappingRequiredFields();
				$allMauticFields = [];
				$remainingFields = [];

				foreach ($mauticContactFields as $field) {
					if (!in_array($field['alias'], $requiredFields)) {
						$remainingFields[$field['alias']] = $field['label'];
					}

					$allMauticFields[$field['alias']] = $field['label'];
				}

				// Get target fields
				$mappingFields = [
					'CPTarget' => getFieldsForSyncMapping('CPTarget'),
					'Leads' => getFieldsForSyncMapping('Leads'),
					'Contacts' => getFieldsForSyncMapping('Contacts'),
				];

				$viewer->assign('MAUTIC_CONTACT_FIELDS', $mauticContactFields);
				$viewer->assign('MAPPING_REQUIRED_FIELDS', $mappingRequiredFields);
				$viewer->assign('ALL_MAUTIC_FIELDS', $allMauticFields);
				$viewer->assign('REMAINING_FIELDS', $remainingFields);
				$viewer->assign('MAPPING_FIELDS', $mappingFields);

				// Get mapping stage, segment
				/*$moduleStageFields = CPMauticIntegration_Data_Helper::getModuleStatusFields();

				foreach (CPMauticIntegration_Data_Helper::getAllStages(true) as $stage) {
					$mauticStages[$stage['id']] = $stage['name'];
				}

				foreach (CPMauticIntegration_Data_Helper::getAllSegments(true) as $segment) {
					$mauticSegments[$segment['id']] = $segment['name'];
				}

				$viewer->assign('MODULE_STAGE_FIELDS', $moduleStageFields);
				$viewer->assign('MAUTIC_STAGES', $mauticStages);
				$viewer->assign('MAUTIC_SEGMENTS', $mauticSegments);*/
			}
		}
		
		$viewer->assign('CONNECTED', $connected);
		$viewer->display('modules/Settings/Vtiger/tpls/MauticIntegrationConfig.tpl');
	}

	private function getConfig() {
		global $mauticConfig;
		$config = CPMauticIntegration_Config_Helper::loadConfig();
		$config['min_points_to_sync_data'] = $mauticConfig['min_points_to_sync_data'];

		return $config;
	}
};