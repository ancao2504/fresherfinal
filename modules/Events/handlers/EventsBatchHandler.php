<?php

/*
*	EventsBatchHandler.php
*	Author: Phuc Lu
*	Date: 2019.11.26
*   Purpose: provide batch handler function for module Events
*/

class EventsBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if ($entityDataList[0]->getModuleName() != 'Events') return;

		if ($eventName === 'vtiger.batchevent.save') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.afterdelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.beforerestore') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.batchevent.afterrestore') {
			// Add handler functions here
		}
	}

    // Handle process_records event
    static function processRecords(&$recordModel) {
        // Added by Hieu Nguyen on 2019-11-27 to display contacts and users invitee list
        $recordModel->set('contact_invitees', Events_Invitation_Helper::getInvitedContactsForListView($recordModel->getId()));
        $recordModel->set('user_invitees', Events_Invitation_Helper::getInvitedUsersForListView($recordModel->getId()));
		// End Hieu Nguyen
		
		// Added by Phu Vo on 2020.07.12 to display event duration
		$recordModel->set('duration', formatDuration($recordModel->get('duration')));
		// End Phu Vo
	}
}