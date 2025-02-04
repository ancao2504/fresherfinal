<?php

/*
	View: List
	Author: Hieu Nguyen
	Date: 2022-09-05
	Purpose: handle custom logic for Calendar ListView
*/

class Calendar_List_View extends Vtiger_List_View {

	function __construct() {
		// Auto redirect to My Calendar view when Calendar Listview is not supported
		if (isForbiddenFeature('CalendarActivityList')) {
			$calendarModuleModel = Calendar_Module_Model::getCleanInstance('Calendar');
			header('Location: index.php?module=Calendar&view=' . $calendarModuleModel->getDefaultViewName());
		}
	}
}