<?php

/**
 * Name: CustomerWillReachHigherMemberLevelWidget.php
 * Author: Phu Vo
 * Date: 2020.08.25
 */

class Home_CustomerWillReachHigherMemberLevelWidget_Dashboard extends Home_BaseListCustomDashboard_Dashboard {

    public function getWidgetMeta($params) {
        $parentWidgetMeta = parent::getWidgetMeta($params);
        $customerGroups = Reports_CustomReport_Helper::getCustomerGroups(false);

        $widgetMeta = [
            'customer_groups' => $customerGroups,
        ];

        $widgetMeta = array_merge($parentWidgetMeta, $widgetMeta);
        
        return $widgetMeta;
    }
}
