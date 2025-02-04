<?php

/*
*	Field.php
*	Author: Phuc Lu
*	Date: 2020.06.29
*/

class HelpDesk_Field_Model extends Vtiger_Field_Model {

    public function getDisplayValue($value, $record = false, $recordInstance = false) {

        $fieldName = $this->getName();

        // Refactored by Tin Bui on 2022.01.12 to re-use this render logic
		if (!empty($value) && in_array($fieldName, ['helpdesk_rating'])) {
            return HelpDesk_GeneralUtils_Helper::displayRatingStars($value, 'Closed');
		}
        // Ended Tin Bui
        
        if (in_array($fieldName, ['sla_total_process_time', 'total_waiting_for_assignment_time', 'total_process_time', 'total_time'])) {
            return HelpDesk_SLAUtils_Helper::secondsInString(intval($value) * 60);
        }
        
        return parent::getDisplayValue($value, $record, $recordInstance);
    }

    public function isAjaxEditable() {
        return false;
    }
}
