<?php

/**
 * Name: GlobalSearchConfig.php
 * Author: Phu Vo
 * Date: 2020.07.15
 */

class Settings_Vtiger_GlobalSearchConfig_View extends Settings_Vtiger_BaseConfig_View {

    public function getPageTitle(Vtiger_Request $request) {
        $moduleName = $request->getModule(false);
        return vtranslate('LBL_GLOBAL_SEARCH_PAGE_TITLE', $moduleName);
    }

    public function process(Vtiger_Request $request) {
        $configs = Settings_Vtiger_Config_Model::loadConfig('global_search', true);
        $moduleName = $request->getModule(false);
        $moduleList = $this->getEntityModuleList();
        $modulesFields = $this->getModulesFields(array_keys($moduleList));

        // Render view
        $viewer = $this->getViewer($request);
        $viewer->assign('CONFIGS', $configs);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODULE_LIST', $moduleList);
        $viewer->assign('MODULES_FIELDS', $modulesFields);

        $viewer->display('modules/Settings/Vtiger/tpls/GlobalSearchConfig.tpl');
    }

    private function getEntityModuleList() {
        global $adb, $packageFeatures;

        $query = "SELECT name, tablabel FROM vtiger_tab WHERE isentitytype = 1 AND name <> 'Events'"; // Events will handle using Calendar module
        $result = $adb->pquery($query);

        $moduleList = [];

        while ($row = $adb->fetchByAssoc($result)) {
            if (in_array($row['name'], array_keys($packageFeatures)) && !$packageFeatures[$row['name']]) continue;
            $moduleList[$row['name']] = vtranslate($row['tablabel'], $row['name']);
        }

        return $moduleList;
    }

    /** Get all fields from modules array */
    private function getModulesFields(array $moduleNames) {
        $moduleFields = [];

        foreach ($moduleNames as $moduleName) {
            $moduleFields[$moduleName] = $this->getSearchableModuleFields($moduleName);
        }

        return $moduleFields;
    }

    /** Get All fields from module */
    private function getSearchableModuleFields($moduleName) {
        $fieldLabelModuleName = $moduleName != 'Events' ? $moduleName : 'Calendar';
        $moduleFieldModels= $this->getModuleFieldModels($moduleName);
        $moduleFields = [];

        foreach ($moduleFieldModels as $fieldName => $fieldModel) {
            if (in_array($fieldName, ['starred', 'tags', 'productid'])) continue;
            if (!in_array($fieldModel->getFieldDataType(), ['text', 'string', 'personName', 'phone', 'email'])) continue;

            // Added by Hieu nguyen on 2022-10-05
            if (
                $moduleName == 'Calendar' && 
                (
                    $fieldName == 'pbx_call_id' ||                              // Ignore PBX Call relate field
                    strpos($fieldName, 'checkin_') !== false ||                 // Ignore all checkin fields
                    in_array($fieldName, ['contact_invitees', 'user_invitees']) // Ignore all invitee fields
                )
            ) {
                continue;
            }
            // End Hieu Nguyen
            
            $moduleFields[] = [
                'id' => $fieldName,
                'text' => vtranslate($fieldModel->label, $fieldLabelModuleName)
            ];
        }

        return $moduleFields;
    }

    private function getModuleFieldModels($moduleName) {
        global $adb;

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $moduleFields = [];

        $sql = "SELECT vtiger_field.fieldname
            FROM vtiger_field
            INNER JOIN vtiger_tab ON (vtiger_field.tabid = vtiger_tab.tabid)
            WHERE vtiger_field.presence IN (0, 2) AND vtiger_tab.name = ?";

        $result = $adb->pquery($sql, [$moduleName]);

        while ($row = $adb->fetchByAssoc($result)) {
            $fieldName = $row['fieldname'];

            $moduleFields[$fieldName] = Vtiger_Field_Model::getInstance($fieldName, $moduleModel);
        }

        return $moduleFields;
    }
}