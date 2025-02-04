<?php

/*
    BookmarksWidget
    Author: Hieu Nguyen
    Date: 2021-03-29
    Purpose: to display bookmark list in the home page
*/

class Home_BookmarksWidget_Dashboard extends Home_BaseListCustomDashboard_Dashboard {

    public function getWidgetFilterTpl() {
        return '';  // This widget has no filter
    }

    public function process(Vtiger_Request $request) {
        parent::process($request);
    }
}
