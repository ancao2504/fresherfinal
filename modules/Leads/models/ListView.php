<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

// Modified by Hieu Nguyen on 2020-11-11 to make this class extends from Contacts ListView
class Leads_ListView_Model extends Contacts_ListView_Model {

	// Override this function by Hieu Nguyen on 2022-05-26 to hide button Transfer Owner for Lead & Target
	public function getListViewMassActions($linkParams) {
		$massActionLinks = parent::getListViewMassActions($linkParams);

		foreach ($massActionLinks['LISTVIEWMASSACTION'] as $index => $link) {
			if ($link->getLabel() == 'LBL_TRANSFER_OWNERSHIP') {
				unset($massActionLinks['LISTVIEWMASSACTION'][$index]);
			}
		}

		return $massActionLinks;
	}
}