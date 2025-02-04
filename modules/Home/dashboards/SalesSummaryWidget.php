<?php

/**
 * Name: SalesSummaryWidget.php
 * Author: Phu Vo
 * Date: 2020.08.26
 */

class Home_SalesSummaryWidget_Dashboard extends Home_BaseSummaryCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return 'modules/Home/tpls/dashboard/TimePeriodFilters.tpl';
    }

    public function getWidgetMeta($params) {
        $metas = parent::getWidgetMeta($params);
        $metas['column'] = 6;
        return $metas;
    }
}