<?php

/**
 * CustomerHaveGuaranteeContractWillBeExpiredWidget.php
 * Author: Phu Vo
 * Date: 2020.08.25
 */

class Home_CustomerHaveGuaranteeContractWillBeExpiredWidget_Dashboard extends Home_BaseListCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }
}