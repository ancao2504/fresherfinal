<?php

/**
 * Name: DashboardAjax.php
 * Author: Phu Vo
 * Date: 2020.10.12
 */

class Home_DashboardAjax_View extends CustomView_Base_View {

    function __construct() {
        parent::__construct();

        $this->exposeMethod('getDashboardConfigModal');
        $this->exposeMethod('getEditDashboardModal');
        $this->exposeMethod('getEditCategoryModal');
        $this->exposeMethod('getSelectWidgetModal');
        $this->exposeMethod('getAddWidgetModal');
    }

    public function process(Vtiger_Request $request) {
        try {
            $mode = $request->getMode();

            if (!empty($mode) && $this->isMethodExposed($mode)) {
                echo $this->invokeExposedMethod($mode, $request);
                return;
            }

            throw new AppException('Handler method not found');
        } catch (Exception $ex) {
            $response = new Vtiger_Response();
            $response->setError($ex->getMessage());
            $response->emit();
        }
    }

    public function getDashboardConfigModal(Vtiger_Request $request) {
        $moduleName = $request->get('module');
        $allWidgetCategories = Home_DashBoard_Model::getWidgetCategories($totalCount);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('WIDGET_CATEGORIES', $allWidgetCategories);

        return $viewer->fetch('modules/Home/tpls/DashboardConfigModal.tpl');
    }

    public function getEditDashboardModal(Vtiger_Request $request) {
        $templateId = $request->get('id');
        $moduleName = $request->get('module');
        $isDuplicate = $request->get('is_duplicate');
        $duplicateTemplateName = '';
        $roleList = Settings_Roles_Record_Model::getAll();
        $templateData = [];

        // View mode
        if (empty($templateId)) {
            $mode = 'create';

            // Set default values
            $templateData['status'] = 'Active';
            $templateData['permission'] = 'Read Only';
        } else if ($isDuplicate) {
            $mode = 'create';
            $templateData = Home_DashBoard_Model::getDashboardTemplateById($templateId);
            $duplicateTemplateName = $templateData['name'];
            unset($templateData['name']);
            unset($templateData['roles']);
            $templateId = null;
        } else {
            $mode = 'edit';
            $templateData = Home_DashBoard_Model::getDashboardTemplateById($templateId);
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('TEMPLATE_ID', $templateId);
        $viewer->assign('MODE', $mode);
        $viewer->assign('ROLE_LIST', $roleList);
        $viewer->assign('TEMPLATE_DATA', $templateData);
        $viewer->assign('IS_DUPLICATE', $isDuplicate);
        $viewer->assign('DUPLICATE_TEMPLATE_NAME', $duplicateTemplateName);
        return $viewer->fetch('modules/Home/tpls/EditHomepageModal.tpl');
    }

    public function getEditCategoryModal(Vtiger_Request $request) {
        $moduleName = $request->get('module');
        $categoryId = $request->get('category_id');
        $categoryData = [];

        if (!empty($categoryId)) {
            $categoryData = Home_DashBoard_Model::getWidgetCategoryById($categoryId);
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CATEGORY_DATA', $categoryData);
        return $viewer->fetch('modules/Home/tpls/EditCategoryModal.tpl');
    }

    public function getSelectWidgetModal(Vtiger_Request $request) {
        $moduleName = $request->get('module');
        $categoryId = $request->get('category_id');
        $totalCount = 0;
        $widgets = [];

        if (!empty($categoryId)) {
            $widgets = Home_DashBoard_Model::getWidgets($totalCount, null, null, $categoryId);
        }

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('CATEGORY_ID', $categoryId);
        $viewer->assign('WIDGETS', $widgets);
        return $viewer->fetch('modules/Home/tpls/SelectWidgetModal.tpl');
    }

    public function getAddWidgetModal(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$dashBoardModel = Vtiger_DashBoard_Model::getInstance($moduleName);
        $dashboardTabs = $dashBoardModel->getActiveTabs();

        if ($request->get('tabid')) {
            $tabId = $request->get('tabid');
        }
        else {
            // If no tab, then select first tab of the user
            $tabId = $dashboardTabs[0]['id'];
        }

        $dashBoardModel->set('tabid', $tabId);
        $selectableWidgets = $dashBoardModel->getSelectableWidgets();
        $groupedSelectableWidgets = Home_DashboardLogic_Helper::groupDashboardWidgetByCategories($selectableWidgets);

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('GROUPED_SELECTABLE_WIDGETS', $groupedSelectableWidgets);

        return $viewer->fetch('modules/Home/tpls/AddWidgetModal.tpl');
    }
}
