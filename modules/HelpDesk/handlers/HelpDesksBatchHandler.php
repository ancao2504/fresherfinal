<?php

/*
*	HelpDesksBatchHandler.php
*	Author: Phuc Lu
*	Date: 2020.06.29
*/

class HelpDesksBatchHandler extends VTEventHandler {

	function handleEvent($eventName, $entityDataList) {
		if ($entityDataList[0]->getModuleName() != 'HelpDesk') return;
	}

    static function processRecords(&$recordModel) {
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        if ($request->get('mode') == 'showRelatedList') {
            return;
        }

        // Refactored by Tin Bui on 2022.01.12 to re-use this render logic
        $displayStars = ['helpdesk_rating'];
        $ticketEntity = Vtiger_Record_Model::getInstanceById($recordModel->getId());
       
        foreach ($displayStars as $fieldName) {
            $rating = $ticketEntity->get($fieldName);
            $status = $ticketEntity->get('ticketstatus');
            $recordModel->set($fieldName, HelpDesk_GeneralUtils_Helper::displayRatingStars($rating, $status));
        }
        // Ended Tin Bui

        // Added by Tin Bui on 2022.01.12: Re-Format time fields display value in listview
        $processTime =  ['sla_total_process_time', 'total_waiting_for_assignment_time', 'total_process_time', 'total_time'];
        
        foreach ($processTime as $fieldName) {
            $recordModel->set($fieldName, HelpDesk_SLAUtils_Helper::secondsInString(intval($recordModel->get($fieldName)) * 60));
        }
        // Ended by Tin Bui
	}
}