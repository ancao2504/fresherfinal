<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
require_once('vtlib/Vtiger/Layout.php'); 

class Settings_ModuleManager_Basic_Action extends Settings_Vtiger_IndexAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('createModule');	// Added by Hieu Nguyen on 2018-08-14
		$this->exposeMethod('updateModuleStatus');
		$this->exposeMethod('importUserModuleStep3');
		$this->exposeMethod('updateUserModuleStep3');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	// Added by Hieu Nguyen on 2018-08-14
	public function createModule(Vtiger_Request $request) {
		require_once('include/utils/FileUtils.php');
		require_once('include/ModuleBuilder/ModuleBuilder.php');

		$moduleName = $request->get('moduleName');
		$displayNameEn = $request->get('displayNameEn');
		$displayNameVn = $request->get('displayNameVn');
		$menuGroup = $request->get('menuGroup');

		//--BEGIN: Modified by Phu Vo on 2020.08.11 -- Enable Activity for module new, Apply module is Extension
		$isExtension = $request->get('isExtension') == 'on' ? 1 : 0;
		$hasActivities = $request->get('hasActivities') == 'on' ? 1 : 0;
		$isPerson = $request->get('isPerson') == 'on' ? 1 : 0;
        //--END: Modified by Phu Vo on 2020.08.11 -- Enable Activity for module new, Apply module is Extension

		$moduleManager = new Settings_ModuleManager_Module_Model();
		$response = new Vtiger_Response();
		$result = array();

		// Check module exist
		if($moduleManager->moduleExists($moduleName)) {
			$result = array('success' => 0, 'message' => 'MODULE_EXISTS');
		}
		// Module not exist
		else {
			// Check register file for duplicate registration
			global $customModules;
			$registerFile = 'include/Extensions/CustomModules.php';
			require($registerFile);
			
			if(isset($customModules[$moduleName])) {
				$result = array('success' => 0, 'message' => 'MODULE_EXISTS_IN_REGISTER_FILE');
			}
			// No duplicate. Create new module
			else {
				$customModules[$moduleName] = array(
					'moduleName' => $moduleName,
					'displayNameEn' => $displayNameEn, 
					'displayNameVn' => $displayNameVn,
					'menu' => $menuGroup,

                    //--BEGIN: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new, Apply module is Extension
					'isExtension' => ($isExtension == 1)? true : false,
                    'hasActivities' => ($hasActivities == 1 && $isExtension == 0)? true : false,
					'isPerson' => ($isPerson == 1) ? true : false, // Added by Phu Vo on 2020.08.11
                    //--END: Added by Kelin Thang on 2019-11-25 -- Enable Activity for module new, Apply module is Extension

					'createdBy' => Vtiger_BlockAndField_Helper::getFileTypeForSaving() == 'base' ? 'base' : 'dev'
				);

				FileUtils::writeArrayToFile(array('customModules' => $customModules), $registerFile);
				$result = ModuleBuilder::build($moduleName, false);

				// Remove the new module from register file if there is an error
				if($result['success'] == 1) {
					//Settings_ModuleManager_Module_Model::initNewModule($moduleName);	// This is done in module builder already
				}
				else {
					unset($customModules[$moduleName]);
					FileUtils::writeArrayToFile(array('customModules' => $customModules), $registerFile);
				}
			}
		}

		// Response
		$response->setResult($result);
		$response->emit();
	}
	// End Hieu Nguyen

	public function updateModuleStatus(Vtiger_Request $request) {
		$moduleName = $request->get('forModule');
		$updateStatus = $request->get('updateStatus');

		$moduleManagerModel = new Settings_ModuleManager_Module_Model();

		if($updateStatus == 'true') {
			$moduleManagerModel->enableModule($moduleName);
		}else{
			$moduleManagerModel->disableModule($moduleName);
		}

		$response = new Vtiger_Response();
		$response->emit();
	}

	public function importUserModuleStep3(Vtiger_Request $request) {
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);

		$importType = $request->get('module_import_type');
		if(strtolower($importType) == 'language') {
			$package = new Vtiger_Language();
		} else if(strtolower($importType) == 'layout') {
			$package = new Vtiger_Layout();
		} else {
			$package = new Vtiger_Package();
		}

		$package->import($uploadFileName);
		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = array('success'=>true, 'importModuleName'=> $importModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function updateUserModuleStep3(Vtiger_Request $request){
		$importModuleName = $request->get('module_import_name');
		$uploadFile = $request->get('module_import_file');
		$uploadDir = Settings_ModuleManager_Extension_Model::getUploadDirectory();
		$uploadFileName = "$uploadDir/$uploadFile";
		checkFileAccess($uploadFileName);

		$importType = $request->get('module_import_type');
		if(strtolower($importType) == 'language') {
			$package = new Vtiger_Language();
		} else if(strtolower($importType) == 'layout') { 
			$package = new Vtiger_Layout(); 
		} else { 
			$package = new Vtiger_Package();
		}

		if (strtolower($importType) == 'language' || strtolower($importType) == 'layout' ) {
			$package->import($uploadFileName);
		} else {
			$package->update(Vtiger_Module::getInstance($importModuleName), $uploadFileName);
		}

		checkFileAccessForDeletion($uploadFileName);
		unlink($uploadFileName);

		$result = array('success'=>true, 'importModuleName'=> $importModuleName);
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	 public function validateRequest(Vtiger_Request $request) { 
		$request->validateWriteAccess(); 
	} 
}
