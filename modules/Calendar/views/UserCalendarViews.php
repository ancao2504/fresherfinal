<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_UserCalendarViews_View extends Vtiger_Index_View {

	function __construct() {
        // Modified by Hieu Nguyen on 2019-10-30
		$this->exposeMethod('getSavedUserFeedList');
		$this->exposeMethod('showUserFeedEditor');
        // End Hieu Nguyen
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

    // Implemented by Hieu Nguyen on 2019-10-30 to get saved user feed list
    function getSavedUserFeedList(Vtiger_Request $request) {
		global $current_user;
		$moduleName = $request->getModule();
		$currentUserFeedInfo = Calendar_SharedCalendar_Model::getCurrentUserFeedInfo();
		$savedUserFeedList = Calendar_SharedCalendar_Model::getSavedUserFeedList();

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER', $current_user);
        $viewer->assign('CURRENT_USER_FEED', $currentUserFeedInfo);
        $viewer->assign('SAVED_USER_FEEDS', $savedUserFeedList);
		$viewer->display('modules/Calendar/tpls/SharedCalendarUserFeedList.tpl');
    }

    // Implemented by Hieu Nguyen on 2019-10-30. This function will handle both add and edit forms
    public function showUserFeedEditor(Vtiger_Request $request) {
        global $current_user;
		$moduleName = $request->getModule();
		$selectedUserId = $request->get('selected_user_id');

        $viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENT_USER', $current_user);
        $viewer->assign('EDITOR_MODE', 'add');

        if (!empty($selectedUserId)) {
            if ($selectedUserId == $current_user->id) {
                $selectedUserFeedInfo = Calendar_SharedCalendar_Model::getCurrentUserFeedInfo();
            }
            else {
                $selectedUserFeedInfo = Calendar_SharedCalendar_Model::getSavedUserFeedInfo($selectedUserId);
            }

            if (empty($selectedUserFeedInfo)) {
                echo 'SELECTED_USER_NOT_IN_SAVED_FEED';
                exit;
            }

            $viewer->assign('SELECTED_USER_ID', $selectedUserFeedInfo['id']);
            $viewer->assign('SELECTED_USER_NAME', $selectedUserFeedInfo['name']);
            $viewer->assign('SELECTED_COLOR', $selectedUserFeedInfo['color']);
            $viewer->assign('EDITOR_MODE', 'update');
        }

		$viewer->display('modules/Calendar/tpls/SharedCalendarUserFeedEditor.tpl');
    }

    // Deleted function editUserCalendar, addUserCalendar by Hieu Nguyen on 2019-10-30 as we have the function showUserFeedEditor above that can handle both cases
}
