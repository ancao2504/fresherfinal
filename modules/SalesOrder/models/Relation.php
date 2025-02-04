<?php

/*
*	Relation.php
*	Author: Phuc Lu
*	Date: 2019.10.18
*/

class SalesOrder_Relation_Model extends Vtiger_Relation_Model {

    public function isEditable() {
        // Get request
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $noLinkModule = [
            'CPPayment',
            'CPReceipt'
        ];

        if (in_array($request->get('relatedModule'), $noLinkModule)) {
            return false;
        }

        return parent::isEditable();
    }

    public function isDeletable() {
        // Get request
        $request = new Vtiger_Request($_REQUEST, $_REQUEST);

        $noLinkModule = [
            'CPPayment',
            'CPReceipt'
        ];

        if (in_array($request->get('relatedModule'), $noLinkModule)) {
            return false;
        }

        return parent::isDeletable();
    }
}
