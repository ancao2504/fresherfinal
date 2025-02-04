<?php

/*
*   PartnerApi.php
*   Author: Hieu Nguyen
*   Date: 2020-06-18
*   Purpose: provide an endpoint for partners to integrate with CRM
*/

chdir(dirname(__FILE__) . '/../');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/Webservice/PartnerApiHandler.php');

session_start();    // Session should be started after the declaration

// Begin: Allow CORS requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']}");
    }

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }

    exit;
}
// End: Allow CORS requests

try {
    $request = PartnerApiHandler::getRequest();
    PartnerApiHandler::saveLog('[PartnerApi] Request data', $request->getAll());

    $action = $request->get('RequestAction');
    $headers = getallheaders();
    $accessKey = $headers['Access-Key'];

    if (empty($accessKey)) {
        PartnerApiHandler::setResponse(401);
    }

    PartnerApiHandler::authenticate($accessKey);

    if (!empty($action)) {
        $method = strtolower($action[0]) . substr($action, 1);

        if (!method_exists('PartnerApiHandler', $method)) {
            PartnerApiHandler::setResponse(400);
        }
        
        PartnerApiHandler::$method($request);
    }
    else {
        PartnerApiHandler::setResponse(400);
    }
} 
catch (Exception $e) {
    PartnerApiHandler::saveLog('[PartnerApi] Error ' . $e->getMessage(), $e->getTrace());
    PartnerApiHandler::setResponse(500, $e->getMessage());
}