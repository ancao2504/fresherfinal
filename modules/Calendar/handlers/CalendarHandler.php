<?php

/*
*	CalendarHandler.php
*	Author: Hieu Nguyen
*	Date: 2019-11-27
*   Purpose: provide handler function for module Calendar
*/

require_once('modules/Events/handlers/EventsHandler.php');

class CalendarHandler extends EventsHandler {

    // Implemented by Phu Vo on 2020.03.19
	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'Calendar') {
			return parent::handleEvent($eventName, $entityData);
		}
		
		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.aftersave') {
			// Add handler functions here
			$this->sendNotificationForAssignedUsers($entityData);
			$this->sendUpdateNotification($entityData);
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {
			// Add handler functions here
		}
	}
}