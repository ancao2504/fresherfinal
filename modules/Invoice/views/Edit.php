<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Invoice_Edit_View extends Inventory_Edit_View {

    // Added by Hieu Nguyen on 2020-12-08 to allow prefill fields that is not exist in the layout
    var $allowPrefillFields = ['related_vendor', 'related_purchaseorder', 'account_id', 'salesorder_id'];
    // End Hieu Nguyen

	 /*
     * @Override by Phuc Lu
     * Date:    2019-09-13
     * Purpose: handle set default value when create record
     * */
    function preProcess(Vtiger_Request $request, $display = true) {
        if (!$request->get('record')) {           
            $request->setGlobal('invoice_paid_status', 'not_completed');
        }
        
        // Added by Hieu Nguyen on 2020-12-08 to switch invoice type to 'buy' when creating an invoice from Vendors
        if (!empty($request->get('related_vendor'))) {
            $request->setGlobal('invoice_type', 'buy');
        }
        // End Hieu Nguyen
        
        parent::preProcess($request, $display);
	}
}