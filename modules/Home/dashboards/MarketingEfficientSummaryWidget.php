<?php

/**
 * Name: MarketingEfficientSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_MarketingEfficientSummaryWidget_Dashboard extends Home_BaseSummaryCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }
}