<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_Module_Model extends Inventory_Module_Model {

    // Custom by Phuc on 2019.08.18 to add balance field
    public function getPopupViewFieldsList() {
        $fields = parent::getPopupViewFieldsList();

        if (!in_array('balance', $fields)) {
            $fields[] = 'balance';
        }

        return $fields;
    }
    // End by Phuc
}