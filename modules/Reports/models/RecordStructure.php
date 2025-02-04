<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

/**
 * Vtiger Edit View Record Structure Model
 */
class Reports_RecordStructure_Model extends Vtiger_RecordStructure_Model {

	// Implemented by Hieu Nguyen on 2021-08-31
	function __construct($values = []) {
		parent::__construct($values);
		$GLOBALS['current_view'] = 'detail';	// Tell Block and Field to load layout structure from module's DetailView
	}

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure($moduleName, $isSecondaryModule = false) { // Modified params by Hieu Nguyen on 2020-06-15
		if (!empty($this->structuredValues[$moduleName])) {
			return $this->structuredValues[$moduleName];
		}
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if ($moduleName === 'Emails') {
			$restrictedTablesList = array('vtiger_emaildetails', 'vtiger_attachments');
			$moduleRecordStructure = array();
			$blockModelList = $moduleModel->getBlocks();
			foreach ($blockModelList as $blockLabel => $blockModel) {
				$fieldModelList = $blockModel->getFields();
				if (!empty($fieldModelList)) {
					$moduleRecordStructure[$blockLabel] = array();
					foreach ($fieldModelList as $fieldName => $fieldModel) {
						if($fieldModel->get('table')=='vtiger_activity' && $this->getRecord()->getPrimaryModule()!='Emails'){
							$fieldModel->set('table','vtiger_activityEmails');
						}
						if (!in_array($fieldModel->get('table'), $restrictedTablesList) && $fieldModel->isViewable()) {
							$moduleRecordStructure[$blockLabel][$fieldName] = $fieldModel;
						}
					}
				}
			}
		} else if($moduleName === 'Calendar') { 
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = array();
			$calendarRecordStructure = $recordStructureInstance->getStructure();
			
			$eventsModel = Vtiger_Module_Model::getInstance('Events');
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($eventsModel);
			$eventRecordStructure = $recordStructureInstance->getStructure();

			foreach($eventRecordStructure as $blockLabel =>$blockFields){
				foreach($blockFields as $fieldName=>$fieldModel){
					if($fieldModel->isCustomField()){
						$eventCustomFields[$fieldName] = $fieldModel;
					}
				}
			}

			$blockLabel = 'LBL_CUSTOM_INFORMATION';
			if($eventCustomFields) {
				if($calendarRecordStructure[$blockLabel]) {
					$calendarRecordStructure[$blockLabel] = array_merge($calendarRecordStructure[$blockLabel],$eventCustomFields);
				} else {
					$calendarRecordStructure[$blockLabel] = $eventCustomFields;
				}
			}
			$moduleRecordStructure = $calendarRecordStructure;
		} else {
			$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
			$moduleRecordStructure = $recordStructureInstance->getStructure();
		}
		//To remove starred and tag fields 
		foreach($moduleRecordStructure as $blockLabel => $blockFields) {
			foreach($blockFields as $fieldName => $fieldModel) {
                // Added by Hieu Nguyen on 2020-06-15 to hide owner and reference field from parent module
                if ($isSecondaryModule && in_array($fieldModel->getFieldDataType(), ['owner', 'reference'])) {
                    unset($moduleRecordStructure[$blockLabel][$fieldName]);
                }
                // End Hieu Nguyen

				if($fieldModel->getDisplayType() == '6') {
					unset($moduleRecordStructure[$blockLabel][$fieldName]);
				}
			}
		}
		$this->structuredValues[$moduleName] = $moduleRecordStructure;
		return $moduleRecordStructure;
	}

	/**
	 * Function returns the Primary Module Record Structure
	 * @return <Vtiger_RecordStructure_Model>
	 */
	function getPrimaryModuleRecordStructure() {
		$primaryModule = $this->getRecord()->getPrimaryModule();
		$primaryModuleRecordStructure = $this->getStructure($primaryModule);
		return $primaryModuleRecordStructure;
	}

	/**
	 * Function returns the Secondary Modules Record Structure
	 * @return <Array of Vtiger_RecordSructure_Models>
	 */
	function getSecondaryModuleRecordStructure() {
		$recordStructureInstances = array();

		$secondaryModule = $this->getRecord()->getSecondaryModules();
		if (!empty($secondaryModule)) {
			$moduleList = explode(':', $secondaryModule);

			foreach ($moduleList as $moduleName) {
				if (!empty($moduleName)) {
					$recordStructureInstances[$moduleName] = $this->getStructure($moduleName, true);    // Modified by Hieu Nguyen on 2020-06-15 to hide owner and reference field from parent module
				}
			}
		}
		return $recordStructureInstances;
	}

}

?>
