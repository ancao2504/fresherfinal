<?php

/*
	Action Module
	Author: Hieu Nguyen
	Date: 2018-08-07
	Purpose: to handle logic for module level
*/

class Settings_LayoutEditor_Module_Action extends Settings_Vtiger_Index_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('savePopupAndRelationListLayout');
		$this->exposeMethod('saveTranslation');
	}

	public function savePopupAndRelationListLayout(Vtiger_Request $request) {
		$moduleName = $request->get('sourceModule');
		$layout = $request->get('layout');

		if (empty($layout) || !is_array($layout['popupLayout']) || !is_array($layout['relationListLayout'])) {
			return;
		}

		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($moduleName);
		$result = $moduleModel->savePopupAndRelationListLayout($layout);

		// Save audit log
		Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update Popup and Relation list layouts for module {$moduleName}", ['new_layout' => $layout]);

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function saveTranslation(Vtiger_Request $request) {
		require_once('include/utils/LangUtils.php');
		$moduleName = $request->get('sourceModule');
		$lang = $request->get('lang');
		$data = $request->get('labels');
		$data['changed'] = $request->get('changed_labels');

		try {
			if (!empty($data['changed'])) {
				if ($lang == 'all') {
					$this->writeLangFile('en_us', $data, $moduleName);
					$this->writeLangFile('vn_vn', $data, $moduleName);
				}
				else {
					$this->writeLangFile($lang, $data, $moduleName);
				}

				// Save audit log
            	Vtiger_AdminAudit_Helper::saveLog('LayoutEditor', "Update translation for module {$moduleName}", ['updated_labels' => $data['changed']]);
			}

			$result = array('success' => 1);
		}
		catch (Exception $ex) {
			$result = array('success' => 0);
		}
		
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	private function writeLangFile($lang, $data, $moduleName) {
		if (empty($data['changed'][$lang])) return;
		$languageStrings = [];
		$jsLanguageStrings = [];
		
		foreach ($data[$lang]['ui'] as $labelKey => $translation) {
			if (in_array((string)$labelKey, $data['changed'][$lang])) {
				$languageStrings[$labelKey] = $translation;
			}
		}

		foreach ($data[$lang]['js'] as $labelKey => $translation) {
			if (in_array((string)$labelKey, $data['changed'][$lang])) {
				$jsLanguageStrings[$labelKey] = $translation;
			}
		}

		LangUtils::writeModStrings($languageStrings, $jsLanguageStrings, $moduleName, $lang);
	}

}