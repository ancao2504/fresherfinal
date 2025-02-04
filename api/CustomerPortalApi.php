<?php

/*
*   CutomerPortalApi.php
*   Author: Hieu Nguyen
*   Date: 2020-06-18
*   Purpose: provide an endpoint for customer portal (including web and mobile app)
*/

chdir(dirname(__FILE__) . '/../');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/Webservice/CustomerPortalApiHandler.php');

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
    $request = CustomerPortalApiHandler::getRequest();
    CustomerPortalApiHandler::saveLog('[SalesAppApi] Request data', $request->getAll());

    $action = $request->get('RequestAction');

    // Login: check username and password
    if ($action == 'Login') {
        CustomerPortalApiHandler::login($request);
    }

    // Reset password: check username and email
    if ($action == 'ResetPassword') {
        CustomerPortalApiHandler::resetPassword($request);
    }

    // Other methods: check token
    $headers = getallheaders();
    $tokenExist = false;

    if (!empty($headers['Token'])) {
        $tokenExist = true;
        $sessionId = $headers['Token'];
    }
    // Supports lowercase headers
    else if (!empty($headers['token'])) {
        $tokenExist = true;
        $sessionId = $headers['token'];
    }

    if (!$tokenExist){
        CustomerPortalApiHandler::setResponse(401);
    }

    CustomerPortalApiHandler::checkSession($sessionId);

    // Logout
    if ($action == 'Logout') {
        CustomerPortalApiHandler::logout($sessionId);
    }

    if (!empty($action)) {
        $method = strtolower($action[0]) . substr($action, 1);

        if (!method_exists('CustomerPortalApiHandler', $method)) {
            CustomerPortalApiHandler::setResponse(400);
        }
        
        CustomerPortalApiHandler::$method($request);
    }
    else {
        CustomerPortalApiHandler::setResponse(400);
    }
} 
catch (Exception $e) {
    CustomerPortalApiHandler::saveLog('[SalesAppApi] Error ' . $e->getMessage(), $e->getTrace());
    CustomerPortalApiHandler::setResponse(500, $e->getMessage());
}