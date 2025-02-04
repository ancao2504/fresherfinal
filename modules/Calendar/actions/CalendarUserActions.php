<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_CalendarUserActions_Action extends Vtiger_Action_Controller{
	
	function __construct() {
        // Added by Hieu Nguyen on 2019-10-30 to modify the shared calendar page
		$this->exposeMethod('getAvailableUserFeedList');    
		$this->exposeMethod('saveUserFeed');
		$this->exposeMethod('updateUserFeedVisibility');
		$this->exposeMethod('deleteUserFeed');
        // End Hieu Nguyen

        // Modified by Hieu Nguyen on 2019-12-18 to handle actions in my calendar page
        $this->exposeMethod('checkDuplicateView');
        $this->exposeMethod('saveCalendarView'); 
        $this->exposeMethod('updateCalendarViewVisibility');
		$this->exposeMethod('deleteCalendarView');
        // End Hieu Nguyen
	}
	
	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		
		if(!Users_Privileges_Model::isPermitted($moduleName, 'View', $record)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

    // Implemented by Hieu Nguyen on 2019-10-30 to load available user feed list that matching the keyword for adding in shared calendar
    function getAvailableUserFeedList(Vtiger_Request $request) {
        $keyword = trim($request->get('keyword'));
        $ownerList = Calendar_SharedCalendar_Model::getAvailableUserFeedList($keyword);

        $response = new Vtiger_Response();
		$response->setResult($ownerList);
		$response->emit();
    }

    // Implemented by Hieu Nguyen on 2019-10-30 to save user feed
    function saveUserFeed(Vtiger_Request $request) {
        $selectedUserId = $request->get('selected_user_id');
        $selectedColor = $request->get('selected_color');
        $editorMode = $request->get('editor_mode');
		
        // Validate
        if (empty($selectedUserId) || empty($selectedColor) || empty($editorMode)) return;

        if ($editorMode == 'add') {
            // Commented out these lines as the new Calendar logic has no blind data anymore
            /*$accessibleStatus = Calendar_SharedCalendar_Model::getUserFeedAccessibleStatus($selectedUserId);

            if ($accessibleStatus !== true) {
                $response = new Vtiger_Response();
                $response->setResult(['success' => false, 'message' => $accessibleStatus]);
                $response->emit();
                exit;
            }*/
        }

        // No error
        Calendar_SharedCalendar_Model::saveUserFeed($selectedUserId, $selectedColor);

        $response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}

    // Implemented by Hieu Nguyen on 2019-10-31 to update feed visibility
    function updateUserFeedVisibility(Vtiger_Request $request) {
        $updateAll = trim($request->get('update_all'));
        $userFeedId = trim($request->get('user_feed_id'));
        $visible = trim($request->get('visible'));

        if ((empty($updateAll)) && empty($userFeedId)) return;
        if ($updateAll == '1') $userFeedId = 'all';

        Calendar_SharedCalendar_Model::updateUserFeedVisibility($userFeedId, $visible);

        $response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
    }

    // Implemented by Hieu Nguyen on 2019-10-30 to delete a saved user feed
    function deleteUserFeed(Vtiger_Request $request) {
        $deleteAll = trim($request->get('delete_all'));
        $userFeedId = trim($request->get('user_feed_id'));

        if ((empty($deleteAll)) && empty($userFeedId)) return;
        if ($deleteAll == '1') $userFeedId = 'all';

        Calendar_SharedCalendar_Model::deleteUserFeed($userFeedId);

        $response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
    }
	
	// Deleted function deleteUserCalendar as it is handled by deleteUserFeed function already
	// Deleted function addUserCalendar as it is handled by saveUserFeed function already
	
	/**
	 * Function to check duplication for calendar views while adding
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
	function checkDuplicateView(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		if (Calendar_MyCalendar_Model::checkDuplicateView($request)) {  // Modified by Hieu Nguyen on 2019-12-19 to call this function from My Calendar Model
			$result = array('success' => true, 'message' => vtranslate('LBL_DUPLICATE_VIEW_EXIST', $moduleName));
		} else {
			$result = array('success' => false);
		}
		
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

    /**
	 * Function to add calendar views to My calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
    // Modified by Hieu Nguyen on 2019-12-19 to return result with the calendar view id
	function saveCalendarView(Vtiger_Request $request) {
		$result = Calendar_MyCalendar_Model::saveCalendarView($request);
        $result['success'] = true;
		
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

    // Implemented by Hieu Nguyen on 2019-11-14 to upload calendar feed visibility
    function updateCalendarViewVisibility(Vtiger_Request $request) {
        $updateAll = trim($request->get('update_all'));
        $calendarViewId = trim($request->get('calendar_view_id'));
        $visible = trim($request->get('visible'));

        if ((empty($updateAll)) && empty($calendarViewId)) return;
        if ($updateAll == '1') $calendarViewId = 'all';

		Calendar_MyCalendar_Model::updateCalendarViewVisibility($calendarViewId, $visible);
		
		$response = new Vtiger_Response();
		$response->setResult(['success' => 1]);
		$response->emit();
	}
	
	/**
	 * Function to delete the calendar view from My Calendar
	 * @param Vtiger_Request $request
	 * @return Vtiger_Response $response
	 */
    // Modified by Hieu Nguyen on 2019-12-18 to delete calendar view by id
	function deleteCalendarView(Vtiger_Request $request) {
		$deleteAll = trim($request->get('delete_all'));
        $calendarViewId = trim($request->get('calendar_view_id'));

        if ((empty($deleteAll)) && empty($calendarViewId)) return;
        if ($deleteAll == '1') $calendarViewId = 'all';

        Calendar_MyCalendar_Model::deleteCalendarView($calendarViewId);

        $response = new Vtiger_Response();
		$response->setResult(['success' => true]);
		$response->emit();
	}
}