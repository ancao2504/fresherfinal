<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_LayoutEditor_Index_View extends Settings_Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showFieldLayout');
		$this->exposeMethod('showPopupAndRelationListLayout');	// Added by Hieu Nguyen on 2019-10-09
		$this->exposeMethod('showTranslationEditor');	// Added by Hieu Nguyen on 2018-08-07
		$this->exposeMethod('showRelatedListLayout');
		$this->exposeMethod('showFieldEdit');
		$this->exposeMethod('showDuplicationHandling');
	}

	public function process(Vtiger_Request $request) {
        // Added by Hieu Nguyen on 2019-12-17 to prevent accessing Layout Editor if this feature is not available in current CRM package
        checkAccessForbiddenFeature('LayoutEditor');
        // End Hieu Nguyen

		$mode = $request->getMode();

		// Modified by Hieu Nguyen on 2018-08-01
		switch($mode) {
			case 'showRelatedListLayout'	:	$selectedTab = 'relatedListTab';	break;
			case 'showDuplicationHandling'	:	$selectedTab = 'duplicationTab';	break;
			case 'showFieldLayoutEditView'	:	$selectedTab = 'editViewTab';		break;
			case 'showFieldLayoutDetailView':	$selectedTab = 'detailViewTab';		break;
			case 'showPopupAndRelationListLayout':	$selectedTab = 'popupAndRelationListLayoutTab';		break;
			case 'showTranslationEditor'	:	$selectedTab = 'translationEditorTab';		break;
			default:	
				$selectedTab = 'editViewTab';
				if (!$mode) {
					$mode = 'showFieldLayoutEditView';
				}

				break;
		}

		if (empty($_REQUEST['layouteditor_tab'])) {
			$_REQUEST['layouteditor_tab'] = $selectedTab;
		}
		// End Hieu Nguyen

		$sourceModule = $request->get('sourceModule');

		// Added by Hieu Nguyen on 2022-05-10 to support modules for developer only
		global $layoutEditorConfig;
		
		if (!empty($layoutEditorConfig['modules_allow_developer_only']) && !isDeveloperMode()) {
			if (in_array($sourceModule, $layoutEditorConfig['modules_allow_developer_only'])) {
				throw new AppException(vtranslate('LBL_NOT_ACCESSIBLE'));
			}
		}
		// End Hieu Nguyen

		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$supportedModulesList = array_flip($supportedModulesList);
		ksort($supportedModulesList);

		$viewer = $this->getViewer($request);
		$viewer->assign('MODE', $mode);
		$viewer->assign('SELECTED_TAB', $selectedTab);
		$viewer->assign('SUPPORTED_MODULES', $supportedModulesList);
		$viewer->assign('REQUEST_INSTANCE', $request);

		if ($sourceModule) {
			$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		}

		if($this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}else {
			//by default show field layout
			$this->showFieldLayout($request);
		}
	}

	public function showFieldLayout(Vtiger_Request $request) {
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$supportedModulesList = array_flip($supportedModulesList);
		ksort($supportedModulesList);

		if(empty($sourceModule)) {
			//To get the first element
			$sourceModule = reset($supportedModulesList);
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$fieldModels = $moduleModel->getAllFields();	// Modified by Hieu Nguyen on 2018-08-03
		$blockModels = $moduleModel->getBlocks();

		$blockIdFieldMap = array();
		$inactiveFields = array();
		$headerFieldsCount = 0;
		$headerFieldsMeta = array();
		foreach ($fieldModels as $fieldModel) {
			$blockIdFieldMap[$fieldModel->getBlockId()][$fieldModel->getName()] = $fieldModel;
			if(!$fieldModel->isActiveField()) {
				$inactiveFields[$fieldModel->getBlockId()][$fieldModel->getId()] = vtranslate($fieldModel->get('label'), $sourceModule);
			}
			if ($fieldModel->isHeaderField()) {
				$headerFieldsCount++;
			}
			$headerFieldsMeta[$fieldModel->getId()] = $fieldModel->isHeaderField() ? 1 : 0;
		}

		foreach($blockModels as $blockLabel => $blockModel) {
			$fieldModelList = $blockIdFieldMap[$blockModel->get('id')];
			$blockModel->setFields($fieldModelList);
		}

		$cleanFieldModel = Settings_LayoutEditor_Field_Model::getCleanInstance();
		$cleanFieldModel->setModule($moduleModel);

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('CLEAN_FIELD_MODEL', $cleanFieldModel);
		$viewer->assign('REQUEST_INSTANCE', $request);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('SELECTED_MODULE_MODEL', $moduleModel);
		$viewer->assign('BLOCKS',$blockModels);
		$viewer->assign('SUPPORTED_MODULES',$supportedModulesList);
		$viewer->assign('ADD_SUPPORTED_FIELD_TYPES', $moduleModel->getAddSupportedFieldTypes());
		$viewer->assign('FIELD_TYPE_INFO', $moduleModel->getAddFieldTypeInfo());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('IN_ACTIVE_FIELDS', $inactiveFields);
		$viewer->assign('HEADER_FIELDS_COUNT', $headerFieldsCount);
		$viewer->assign('HEADER_FIELDS_META', $headerFieldsMeta);

		$cleanFieldModel = Settings_LayoutEditor_Field_Model::getCleanInstance();
		$cleanFieldModel->setModule($moduleModel);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$this->setModuleInfo($request, $sourceModuleModel, $cleanFieldModel);

		if ($request->isAjax() && !$request->get('showFullContents')) {
			$viewer->view('FieldsList.tpl', $qualifiedModule);
		} else {
			$viewer->view('Index.tpl', $qualifiedModule);
		}
	}

    // Added by Hieu Nguyen on 2019-10-09
	public function showPopupAndRelationListLayout(Vtiger_Request $request) {
		$qualifiedModule = $request->getModule(false);
		$sourceModuleName = $request->get('sourceModule');
		$supportedModules = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$supportedModules = array_flip($supportedModules);
		ksort($supportedModules);

		// First module in supported list will be selected if the source module name is not specified
		if (empty($sourceModuleName)) {
			$sourceModuleName = reset($supportedModules);
		}

        // Only Calendar has relation list and popup
        if ($sourceModuleName == 'Events') $sourceModuleName = 'Calendar';

        $sourceModuleModel = Vtiger_Module::getInstance($sourceModuleName);
        $moduleFieldModels = $sourceModuleModel->getFields();
        $selectedPopupFields = $sourceModuleModel->getPopupViewFieldsList();
        $selectedRelatedListFields = $sourceModuleModel->getConfigureRelatedListFields();
        $select2ModuleFields = [];
        $select2SelectedPopupFields = [];
        $select2SelectedRelationListFields = [];
 
        // Transform all fields into select2 tagging format
        foreach ($moduleFieldModels as $fieldName => $fieldModel) {
            if (!in_array($fieldName, ['starred', 'tags', 'productid'])) {
                $select2ModuleFields[] = [
                    'id' => $fieldName,
                    'text' => vtranslate($fieldModel->label, $sourceModuleName)
                ];
            }         
        }

        // Transform selected popup fields into select2 tagging format
        foreach ($selectedPopupFields as $fieldName) {
            $fieldModel = $moduleFieldModels[$fieldName];

            if ($fieldModel) {
                $select2SelectedPopupFields[] = [
                    'id' => $fieldName,
                    'text' => vtranslate($fieldModel->label, $sourceModuleName)
                ];
            }
        }

        // Transform selected relation list fields into select2 tagging format
        foreach ($selectedRelatedListFields as $fieldName) {
            $fieldModel = $moduleFieldModels[$fieldName];

            if ($fieldModel) {
                $select2SelectedRelationListFields[] = [
                    'id' => $fieldName,
                    'text' => vtranslate($fieldModel->label, $sourceModuleName)
                ];
            }
        }
		
		// Render the view
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('SELECT2_MODULE_FIELDS', $select2ModuleFields);
		$viewer->assign('SELECT2_SELECTED_POPUP_FIELDS', $select2SelectedPopupFields);
		$viewer->assign('SELECTED_POPUP_SORTING', Settings_LayoutEditor_Module_Model::getModuleLayoutSorting($sourceModuleName, 'Popup'));
		$viewer->assign('SELECT2_SELECTED_RELATION_LIST_FIELDS', $select2SelectedRelationListFields);
        $viewer->assign('SELECTED_RELATION_LIST_SORTING', Settings_LayoutEditor_Module_Model::getModuleLayoutSorting($sourceModuleName, 'RelationList'));
		$viewer->view('Index.tpl', $qualifiedModule);
	}
	// End Hieu Nguyen

	// Added by Hieu Nguyen on 2018-08-07
	public function showTranslationEditor(Vtiger_Request $request) {
		checkAccessForbiddenFeature('TranslationEditor');
		$qualifiedModule = $request->getModule(false);
		$sourceModuleName = $request->get('sourceModule');
		$supportedModules = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$supportedModules = array_flip($supportedModules);
		ksort($supportedModules);

		// First module in supported list will be selected if the source module name is not specified
		if(empty($sourceModuleName)) {
			$sourceModuleName = reset($supportedModules);
		}
		
		// Load language strings
		$enLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile('en_us', $sourceModuleName);
		$enUiStrings = $enLanguageStrings['languageStrings'];
		$enJsStrings = $enLanguageStrings['jsLanguageStrings'];

		$vnLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile('vn_vn', $sourceModuleName);
		$vnUiStrings = $vnLanguageStrings['languageStrings'];
		$vnJsStrings = $vnLanguageStrings['jsLanguageStrings'];
        
		// Render the view
		$viewer = $this->getViewer($request);
		$viewer->assign('SUPPORTED_MODULES', $supportedModules);
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('UI_ENGLISH_STRINGS', $enUiStrings);
		$viewer->assign('JS_ENGLISH_STRINGS', $enJsStrings);
		$viewer->assign('UI_VIETNAMESE_STRINGS', $vnUiStrings);
		$viewer->assign('JS_VIETNAMESE_STRINGS', $vnJsStrings);
		$viewer->view('Index.tpl', $qualifiedModule);
	}
	// End Hieu Nguyen

	public function showRelatedListLayout(Vtiger_Request $request) {
		$sourceModule = $request->get('sourceModule');
		$supportedModulesList = Settings_LayoutEditor_Module_Model::getSupportedModules();

		if(empty($sourceModule)) {
			//To get the first element
			$moduleInstance = reset($supportedModulesList);
			$sourceModule = $moduleInstance->getName();
		}
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);
		$relatedModuleModels = $moduleModel->getRelations();

		$hiddenRelationTabExists = false;
		foreach ($relatedModuleModels as $relationModel) {
			if (!$relationModel->isActive()) {
				// to show select hidden element only if inactive tab exists 
				$hiddenRelationTabExists = true;
				break;
			}
		}

		$relationFields = array();
		$referenceFields = $moduleModel->getFieldsByType('reference');

		foreach ($referenceFields as $fieldModel) {
			if ($fieldModel->get('uitype') == '52' || !$fieldModel->isActiveField()) {
				continue;
			}
			$relationType = $moduleModel->getRelationTypeFromRelationField($fieldModel);
			$fieldModel->set('_relationType', $relationType);
			$relationFields[$fieldModel->getName()] = $fieldModel;
		}

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);

		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('RELATED_MODULES', $relatedModuleModels);
		$viewer->assign('RELATION_FIELDS', $relationFields);
		$viewer->assign('HIDDEN_TAB_EXISTS', $hiddenRelationTabExists);
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);

		// Added by Hieu Nguyen on 2018-08-30
		$moduleList = Settings_LayoutEditor_Module_Model::getSupportedModules();
		$moduleList = array_flip($moduleList);
		ksort($moduleList);

		$viewer->assign('MODULE_LIST', $moduleList);
		// End Hieu Nguyen

		if ($request->isAjax() && !$request->get('showFullContents')) {
			$viewer->view('RelatedList.tpl', $qualifiedModule);
		} else {
			$viewer->view('Index.tpl', $qualifiedModule);
		}
	}

	public function showFieldEdit(Vtiger_Request $request) {
		$sourceModule = $request->get('sourceModule');
		$fieldId = $request->get('fieldid');
		$fieldInstance = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
		$moduleModel = Settings_LayoutEditor_Module_Model::getInstanceByName($sourceModule);

		$fieldModels = $moduleModel->getFields();
		$headerFieldsCount = 0;
		foreach ($fieldModels as $fieldModel) {
			if ($fieldModel->isHeaderField()) {
				$headerFieldsCount++;
			}
		}

		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_INFO', $fieldInstance->getFieldInfo());
		$viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
		$viewer->assign('ADD_SUPPORTED_FIELD_TYPES', $moduleModel->getAddSupportedFieldTypes());
		$viewer->assign('FIELD_TYPE_INFO', $moduleModel->getAddFieldTypeInfo());
		$viewer->assign('FIELD_MODEL', $fieldInstance);
		$viewer->assign('IS_FIELD_EDIT_MODE', true);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('HEADER_FIELDS_COUNT', $headerFieldsCount);
		$viewer->assign('IS_NAME_FIELD', in_array($fieldInstance->getName(), $moduleModel->getNameFields()));

		$cleanFieldModel = Settings_LayoutEditor_Field_Model::getCleanInstance();
		$cleanFieldModel->setModule($moduleModel);
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$this->setModuleInfo($request, $sourceModuleModel, $cleanFieldModel);

		$viewer->view('FieldCreate.tpl', $qualifiedModule);
	}

	public function showDuplicationHandling(Vtiger_Request $request) {
		$qualifiedModule = $request->getModule(false);
		$sourceModuleName = $request->get('sourceModule');
		$moduleModel = Vtiger_Module_Model::getInstance($sourceModuleName);
		$blocks = $moduleModel->getBlocks();

		$fields = array();
		foreach ($blocks as $blockId => $blockModel) {
			$blockFields = $blockModel->getFields();
			foreach ($blockFields as $key => $fieldModel) {
				if ($fieldModel->isEditable()
					&& $fieldModel->get('displaytype') != 5
					&& !in_array($fieldModel->get('uitype'), array(28, 30, 53, 56, 69, 83))
					&& !in_array($fieldModel->getFieldDataType(), array('text', 'multireference'))) {
					$fields[$blockModel->get('label')][$fieldModel->getName()] = $fieldModel;
				}
			}
		}
		Vtiger_MenuStructure_Model::getAppMenuList();

		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $fields);
		$viewer->assign('SOURCE_MODULE', $sourceModuleName);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->assign('SOURCE_MODULE_MODEL', $moduleModel);
		$viewer->assign('ACTIONS', Vtiger_Module_Model::getSyncActionsInDuplicatesCheck());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		if ($request->isAjax() && !$request->get('showFullContents')) {
			$viewer->view('DuplicateHandling.tpl', $qualifiedModule);
		} else {
			$viewer->view('Index.tpl', $qualifiedModule);
		}
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'~libraries/garand-sticky/jquery.sticky.js',
			'~/libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/**
	 * Setting module related Information to $viewer (for Vtiger7)
	 * @param type $request
	 * @param type $moduleModel
	 */
	public function setModuleInfo($request, $moduleModel, $cleanFieldModel = false) {
		$fieldsInfo = array();
		$basicLinks = array();
		$viewer = $this->getViewer($request);

		if (method_exists($moduleModel, 'getFields')) {
			$moduleFields = $moduleModel->getFields();
			foreach ($moduleFields as $fieldName => $fieldModel) {
				$fieldsInfo[$fieldName] = $fieldModel->getFieldInfo();
			}

			//To set the clean field meta for new field creation
			if ($cleanFieldModel) {
				$newfieldsInfo['newfieldinfo'] = $cleanFieldModel->getFieldInfo();
				$viewer->assign('NEW_FIELDS_INFO', json_encode($newfieldsInfo));
			}

			$viewer->assign('FIELDS_INFO', json_encode($fieldsInfo));
		}

		if (method_exists($moduleModel, 'getModuleBasicLinks')) {
			$moduleBasicLinks = $moduleModel->getModuleBasicLinks();
			foreach ($moduleBasicLinks as $basicLink) {
				$basicLinks[] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
			}
			$viewer->assign('MODULE_BASIC_ACTIONS', $basicLinks);
		}
	}

	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$cssFileNames = array(
			'~/libraries/jquery/bootstrapswitch/css/bootstrap2/bootstrap-switch.min.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		return $headerCssInstances;
	}

}
