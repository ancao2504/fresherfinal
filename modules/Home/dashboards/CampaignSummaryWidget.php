<?php

/**
 * Name: CampaignSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_CampaignSummaryWidget_Dashboard extends Home_BaseSummaryCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }
}