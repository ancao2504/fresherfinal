<?php
/*
*	DocumentsHandler.php
*	Author: Hieu Nguyen
*	Date: 2020-06-26
*   Purpose: provide handler events for Documents
*/

class DocumentsHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		if($entityData->getModuleName() != 'Documents') return;
		
		if($eventName === 'vtiger.entity.beforesave') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.entity.aftersave') {
            // Add handler functions here
		}

		if($eventName === 'vtiger.entity.beforedelete') {
			// Add handler functions here
		}

		if($eventName === 'vtiger.entity.afterdelete') {			
			// Add handler functions here
		}
	}
}