<?php

/**
 * Name: DashboardAjax.php
 * Author: Phu Vo
 * Date: 2020.10.12
 */

class Home_DashboardAjax_Action extends Vtiger_Action_Controller {

    function __construct() {
        $this->exposeMethod('enterDashboardEditMode');
        $this->exposeMethod('exitDashboardEditMode');
        $this->exposeMethod('getDashboardTemplates');
        $this->exposeMethod('saveDashboardTemplate');
        $this->exposeMethod('deleteDashboardTemplate');
        $this->exposeMethod('getWidgetCategories');
        $this->exposeMethod('saveWidgetCategory');
        $this->exposeMethod('deleteCategoryAndRelatedWidgets');
        $this->exposeMethod('getWidgetsByCategory');
        $this->exposeMethod('removeWidgetFromCategory');
        $this->exposeMethod('selectWidgetCategory');
        $this->exposeMethod('applyCurrentDashboardTemplateToUsers');
        $this->exposeMethod('checkDuplicateDashboardTemplate');
        $this->exposeMethod('checkDuplicateRolesInDashboardTemplate');
        $this->exposeMethod('removeAllWidgetFromTab');
        $this->exposeMethod('checkTemplateVadility');
    }

    public function checkPermission(Vtiger_Request $request) {
        return;
    }

    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            return $this->invokeExposedMethod($mode, $request);
        }
    }

    protected function _returnResponse($response) {
        $response = json_encode($response);
        echo ($response);
    }

    public function enterDashboardEditMode(Vtiger_Request $request) {
        $homepageId = $request->get('id');

        $_SESSION['dashboard_edit_mode'] = true;
        $_SESSION['editing_dashboard_id'] = $homepageId;

        header('Location: index.php?module=Home&view=DashBoard');
    }

    public function exitDashboardEditMode(Vtiger_Request $request) {
        unset($_SESSION['dashboard_edit_mode']);
        unset($_SESSION['editing_dashboard_id']);

        header('Location: index.php?module=Home&view=DashBoard');
    }

    public function getDashboardTemplates(Vtiger_Request $request) {
        $draw = $request->get('draw');

        $roleList = Settings_Roles_Record_Model::getAll();
        $permissionList = Home_DashBoard_Model::getDashboardPermissions();
        $statusList = Home_DashBoard_Model::getDashboardStatuses();

        $totalCount = 0;
        $dashboardTemplates = Home_DashBoard_Model::getDashboardTemplates($totalCount);

        foreach ($dashboardTemplates as $templateIndex => $template) {
            $dashboardTemplates[$templateIndex]['raw'] = $template;

            // Translate roles to readable data
            if (!empty($template['roles'])) {
                foreach ($template['roles'] as $index => $role) {
                    $roleRecord = $roleList[$role];

                    if (!empty($roleRecord)) {
                        $template['roles'][$index] = $roleRecord->get('rolename');
                    } else {
                        unset($template['roles'][$index]);
                    }
                }

                $dashboardTemplates[$templateIndex]['roles'] = implode(', ', $template['roles']);
            }

            // Translate status
            if (!empty($template['status'])) {
                $dashboardTemplates[$templateIndex]['status'] = $statusList[$template['status']] ?? $template['status'];
            }

            // Translate permission
            if (!empty($template['permission'])) {
                $dashboardTemplates[$templateIndex]['permission'] = $permissionList[$template['permission']] ?? $template['status'];
            }
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => intval($totalCount),
            'recordsFiltered' => intval($totalCount),
            'data' => $dashboardTemplates,
            'length' => intval(0),
            'offset' => intval(0),
        ];

        $this->_returnResponse($response);
    }

    public function saveDashboardTemplate(Vtiger_Request $request) {
        $moduleName = $request->get('module');
        $templateId = $request->get('id');
        $templateData = $request->get('template_data');
        $isDuplicate = $request->get('is_duplicate');
        if (!empty($templateId) && !$isDuplicate) $templateData['id'] = $templateId;

        // Check duplicate
        $excludeId = $templateId;
        if ($isDuplicate) $excludeId = null;
        if (Home_DashBoard_Model::checkDuplicateDashboardTemplate($templateData['name'], $excludeId)) {
            throw new AppException(vtranslate('LBL_DASHBOARD_DUPLICATE_DASHBOARD_TEMPLATE_NAME', $moduleName));
        }

        $templateData = Home_DashBoard_Model::saveDashboardTemplate($templateData);

        // Prevent duplicate layout when edit
        if ($isDuplicate) {
            Home_DashboardLogic_Helper::copyTemplateLayout($templateId, $templateData['id']);
        }

        $response = new Vtiger_Response();
        $response->setResult($templateData);
        $response->emit();
    }

    public function deleteDashboardTemplate(Vtiger_Request $request) {
        $templateId = $request->get('id');

        Home_DashBoard_Model::deleteDashboardTemplate($templateId);

        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function getWidgetsByCategory(Vtiger_Request $request) {
        $draw = $request->get('draw');
        $categoryId = $request->get('category_id');
        $widgets = [];
        $totalCount = 0;

        $filters = [
            'category_id' => $categoryId,
        ];

        if (!empty($categoryId)) $widgets = Home_DashBoard_Model::getWidgets($totalCount, $filters);

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => intval($totalCount),
            'recordsFiltered' => intval($totalCount),
            'data' => $widgets,
            'length' => intval(0),
            'offset' => intval(0),
        ];

        $this->_returnResponse($response);
    }

    public function getWidgetCategories(Vtiger_Request $request) {
        $nameField = Home_DashBoard_Model::getLanguageNameField();
        $keyword = $request->get('keyword');
        $totalCount = 0;
        $filters = [$nameField => $keyword];

        $widgetCategories = Home_DashBoard_Model::getWidgetCategories($totalCount, $filters);


        foreach ($widgetCategories as $index => $widgetCategory) {
            $widgetCategories[$index] = [
                'id' => $widgetCategory['id'],
                'text' => $widgetCategory['name'],
            ];
        }

        $response = new Vtiger_Response();
        $response->setResult($widgetCategories);
        $response->emit();
    }

    public function saveWidgetCategory(Vtiger_Request $request) {
        $categoryId = $request->get('category_id');
        $data = $request->get('data');

        $nameField = Home_DashBoard_Model::getLanguageNameField();

        if (!empty($categoryId)) $data['id'] = $categoryId;
        $data = Home_DashBoard_Model::saveWidgetCategory($data);
        $data['name'] = $data[$nameField];

        $response = new Vtiger_Response();
        $response->setResult($data);
        $response->emit();
    }

    public function deleteCategoryAndRelatedWidgets(Vtiger_Request $request) {
        $categoryId = $request->get('id');

        Home_DashBoard_Model::deleteCategoryAndRelatedWidgets($categoryId);

        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function removeWidgetFromCategory(Vtiger_Request $request) {
        $widgetId = $request->get('id');
        $categoryId = $request->get('category_id');

        if (empty($widgetId) || empty($categoryId)) return;

        Home_DashBoard_Model::removeWidgetFromCategoryById($widgetId, $categoryId);

        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function selectWidgetCategory(Vtiger_Request $request) {
        $categoryId = $request->get('category_id');
        $widgets = $request->get('widgets');

        foreach ($widgets as $widgetInfo) {
            if ($widgetInfo['active'] == 'on') {
                Home_DashBoard_Model::addWidgetToCategory($categoryId, $widgetInfo['id'], $widgetInfo['category_type']);
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function applyCurrentDashboardTemplateToUsers(Vtiger_Request $request) {
        $templateId = $request->get('template_id');
        if (empty($templateId)) $templateId = $_SESSION['editing_dashboard_id'];

        if (empty($templateId)) return;

        Home_DashboardLogic_Helper::applyTemplateToUsers($templateId);

        $response = New Vtiger_Response();
        $response->setResult(true);
        $response->emit();
    }

    public function checkDuplicateDashboardTemplate(Vtiger_Request $request) {
        $keyword = $request->get('keyword');
        $isDuplicate = Home_DashBoard_Model::checkDuplicateDashboardTemplate($keyword);

        $result = ['is_duplicate' => $isDuplicate];

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function checkDuplicateRolesInDashboardTemplate(Vtiger_Request $request) {
        $roles = $request->get('roles');
        $exclude = $request->get('exclude');
        $isDuplicate = $request->get('is_duplicate');

        if ($isDuplicate == 1) $exclude = null;

        $roleList = Settings_Roles_Record_Model::getAll();
        $duplicateRolesInDashboardTemplates = Home_DashBoard_Model::checkDuplicateRolesInDashboardTemplate($roles, $exclude) ?? [];

        foreach ($duplicateRolesInDashboardTemplates as $index => $roleId) {
            if (!empty($roleList[$roleId])) {
                $duplicateRolesInDashboardTemplates[$index] = $roleList[$roleId]->get('rolename');
            }
        }
        $result = ['duplicate_roles' => implode(', ', $duplicateRolesInDashboardTemplates)];

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function removeAllWidgetFromTab(Vtiger_Request $request) {
        global $current_user;

        $tabId = $request->get('tab_id');
        $result = Home_DashboardLogic_Helper::emptyDashboardTabWidget($tabId, $current_user->id);

        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }

    public function checkTemplateVadility(Vtiger_Request $request) {
        $dashboardId = $request->get('template_id');
        if (empty($dashboardId)) $dashboardId = $_SESSION['editing_dashboard_id'];

        if (empty($dashboardId)) return;

        $dashboardData = Home_DashBoard_Model::getDashboardTemplateById($dashboardId);
        $tabs = Home_DashboardLogic_Helper::getTabsByDashboard($dashboardId);

        if (empty($tabs)) {
            $replaceParams = ['%dashboard_name' => $dashboardData['name']];
            $message = vtranslate('LBL_DASHBOARD_EMPTY_TEMPLATE_ERR_MSG', 'Home', $replaceParams);
            throw new AppException($message);
        }

        foreach ($tabs as $tab) {
            $widgets = Home_DashboardLogic_Helper::getWidgetsByTab($tab['id']);

            if (empty($widgets)) {
                $replaceParams = ['%tab_name' => $tab['name'], '%dashboard_name' => $dashboardData['name']];
                $message = vtranslate('LBL_DASHBOARD_EMPTY_TAB_ERR_MSG', 'Home', $replaceParams);
                throw new AppException($message);
            }
        }

        $response = new Vtiger_Response();
        $response->getResult(true);
        $response->emit();
    }
}
