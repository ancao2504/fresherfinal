<?php

/*
    Field Model
    Author: Hieu Nguyen
    Date: 2021-08-04
    Purpose: to control what field should do
*/

class Potentials_Field_Model extends Vtiger_Field_Model {
    
    // Control which field can be quicked edited in ListView and DetailView
    public function isAjaxEditable() {
        $importantFields = ['sales_stage', 'probability', 'potentialresult', 'potentiallostreason', 'lost_reason_description'];

        if (in_array($this->name, $importantFields)) {
            return false;
        }

        return true;
    }
}