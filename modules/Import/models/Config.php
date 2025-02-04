<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
/**
 * Add By kelvin Thang
 * Date: 2018-06-28
 * Desc: Convert xlsx/xls to csv
 */
class Import_Config_Model extends Vtiger_Base_Model {

	function __construct() {
        global $ImportConfig; //add by Tung Bui - 30/08/2018 - move config to root         
        
		$this->setData($ImportConfig);
	}
}
