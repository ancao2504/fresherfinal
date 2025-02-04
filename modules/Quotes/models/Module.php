<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Quotes_Module_Model extends Inventory_Module_Model {

	// Added by Phu Vo on 2021.09.25 base on UI UX Request
	public function isQuickCreateSupported() {
		return false;
	}

}
?>
