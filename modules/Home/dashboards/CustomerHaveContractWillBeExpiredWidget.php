<?php

/**
 * Home_CustomerHaveContractWillBeExpiredWidget_Dashboard
 * Author: Phu Vo
 * Date: 2020.08.25
 */

class Home_CustomerHaveContractWillBeExpiredWidget_Dashboard extends Home_BaseListCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }
}