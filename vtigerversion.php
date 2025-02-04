<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

// Modified by Hieu Nguyen on 2022-08-25 to read version from custom config
$customConfig = include('config_override.cus.php');
$vtiger_current_version = $customConfig['currentVersion'];
$_SESSION['vtiger_version'] = $vtiger_current_version;
// End Hieu Nguyen

// Added by Hieu Nguyen on 2021-11-23 to tell the client if this server use a self signed ssl
header('self-signed-ssl: false');
// End Hieu Nguyen

// Added by Hieu Nguyen on 2021-09-24 to check CRM version
if (strpos($_SERVER['SCRIPT_NAME'], 'vtigerversion.php') > 0) {
	echo $vtiger_current_version;
}
// End Hieu Nguyen