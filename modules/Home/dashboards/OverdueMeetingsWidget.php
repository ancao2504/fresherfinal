<?php

/**
 * OverdueMeetingsWidget
 * Author: Phu Vo
 * Date: 2020.08.27
 */

class Home_OverdueMeetingsWidget_Dashboard extends Home_BaseListCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->assign('CUMULATE', true);
        parent::process($request);
    }
}
