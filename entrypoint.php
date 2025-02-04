<?php

/*
    EntryPoint structure
    Author: Hieu Nguyen
    Date: 2018-08-24
    Purpose: provide an entry point structure similar to SugarCRM
    Usage: when you access /entrypoint.php?name=<Entry-Point-Name>, the entry point inside include/EntryPoints/<Entry-Point-Name>.php will be activated
*/

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');

$entryPointName = preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['name']);;
$entryPointFile = 'include/EntryPoints/' . $entryPointName . '.php';

if(file_exists($entryPointFile)) {
    require_once('includes/Loader.php');
    require_once('include/utils/VtlibUtils.php');
    require_once('includes/runtime/EntryPoint.php');
    require_once($entryPointFile);
    global $current_user;

    if($current_user == null) {
        $current_user = Users::getRootAdminUser();    // Bypass permission check
    }
    
    $entryPoint = new $entryPointName();
    $entryPoint->process(new Vtiger_Request($_REQUEST, $_REQUEST));
}
else {
    echo 'EntryPoint not found!';
}