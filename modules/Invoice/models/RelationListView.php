<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_RelationListView_Model extends Inventory_RelationListView_Model {

    // Added by Phuc on 2019.08.15 to only allow select action with tab Receipt and Payment
    // Edited by Phuc on 2019.10.11 to allow action create only (not allow select)
    public function getLinks() {
        if (in_array($this->relatedModuleModel->name, ['CPReceipt', 'CPPayment'])) {
            $addLinks = $this->getAddRelationLinks();
            $relatedLink = array();
            $relatedLink['LISTVIEWBASIC'] = $addLinks;
            
            return $relatedLink;
        }
        
        return parent::getLinks();
	}
}
?>