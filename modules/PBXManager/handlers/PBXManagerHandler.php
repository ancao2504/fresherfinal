<?php

/*
    PBXManagerHandler.php
    Author: Hieu Nguyen
    Date: 2020-09-08
    Purpose: handle events for PBXManager
*/

class PBXManagerHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if ($entityData->getModuleName() != 'PBXManager') return;
		
		if ($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.aftersave') {
            // Add handler functions here
		}

		if ($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if ($eventName === 'vtiger.entity.afterdelete') {			
			// Add handler functions here
		}
	}
}