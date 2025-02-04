<?php

/*
    View: FieldGuideConfig
    Author: Hieu Nguyen
    Date: 2021-01-20
    Purpose: Config user guide for all fields
*/

class Settings_Vtiger_FieldGuideConfig_View extends Settings_Vtiger_BaseConfig_View {

    function __construct() {
        parent::__construct($isFullView = true);
    }

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_FIELD_GUIDE_CONFIG', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        $targetModule = $request->get('target_module');
        $allModules = getModulesTranslatedSingleLabel();

        // Addition modules
        $additionModules = [
            'Calendar' => vtranslate('Calendar', 'Calendar'),
            'Documents' => vtranslate('Documents', 'Documents'),
        ];

        $allModules = array_merge($allModules, $additionModules);

        // Hide readonly modules
        foreach ($allModules as $name => $label) {
            $focus = CRMEntity::getInstance($name);
            
            if ($focus->isReadonly || in_array($name, ['ModComments'])) {
                unset($allModules[$name]);
            }
        }

        if (empty($targetModule)) $targetModule = array_keys($allModules)[0];
        $targetModuleModel = Vtiger_Module_Model::getInstance($targetModule);
        $fieldModels = $targetModuleModel->getFields();

        // Hide readonly fields
        foreach ($fieldModels as $fieldName => $fieldModel) {
            if (
                $fieldModel->isReadOnly() || in_array($fieldName, ['starred', 'tags'])
                || $fieldModel->get('displaytype') > 1 || $fieldModel->get('uitype') == 4
            ) {
                unset($fieldModels[$fieldName]);
            }
        }

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('TARGET_MODULE', $targetModule);
        $viewer->assign('ALL_MODULES', $allModules);
        $viewer->assign('FIELD_MODELS', $fieldModels);
        $viewer->display('modules/Settings/Vtiger/tpls/FieldGuideConfig.tpl');
    } 
}