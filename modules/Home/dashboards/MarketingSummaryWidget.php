<?php

/**
 * Name: MarketingSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_MarketingSummaryWidget_Dashboard extends Home_BaseSummaryCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->assign('CUMULATE', true);
        parent::process($request);
    }
}