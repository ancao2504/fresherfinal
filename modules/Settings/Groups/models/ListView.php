<?php

/*
    Groups_ListView_Model
    Author: Hieu Nguyen
    Date: 2019-11-25
    Purpose: provide util functions to handle ListView data
*/

class Settings_Groups_ListView_Model extends Settings_Vtiger_ListView_Model {
    
    public function getBasicListQuery() {
        $query = parent::getBasicListQuery();
        $query .= ' WHERE is_custom = 0';

        return $query;
    }
}