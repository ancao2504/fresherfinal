<?php

/*
*   SalesAppApi.php
*   Author: Hieu Nguyen
*   Date: 2018-09-07
*   Purpose: provide an endpoint for mobile app
*/

chdir(dirname(__FILE__) . '/../');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/Webservice/SalesAppApiHandler.php');

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
    $request = SalesAppApiHandler::getRequest();

    // Modified by Phu Vo on 2021.06.01 to replace password on log
    $requestData = $request->getAll();

    if ($requestData['RequestAction'] == 'Login') {
        $isOpenId = $requestData['IsOpenId'];
        $credentials = $requestData['Credentials'];

        if ($isOpenId != '1' && !empty($credentials['password'])) {
            $password = str_repeat('*', strlen($credentials['password']));
            $requestData['Credentials']['password'] = $password;
        }
    }

    SalesAppApiHandler::saveLog('[SalesAppApi] Request data', $requestData);
    // End Phu Vo

    $action = $request->get('RequestAction');

    // Login: check username and password
    if ($action == 'Login') {
        SalesAppApiHandler::login($request);
    }

    // Reset password: check username and email
    if ($action == 'ResetPassword') {
        SalesAppApiHandler::resetPassword($request);
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
        SalesAppApiHandler::setResponse(401);
    }

    SalesAppApiHandler::checkSession($sessionId);

    // Logout
    if ($action == 'Logout') {
        SalesAppApiHandler::logout($sessionId);
    }

    if (!empty($action)) {
        if (isForbiddenFeature('SalesApp')) {
            SalesAppApiHandler::setResponse(401);
        }

        $method = strtolower($action[0]) . substr($action, 1);

        if (!method_exists('SalesAppApiHandler', $method)) {
            SalesAppApiHandler::setResponse(400);
        }
        
        SalesAppApiHandler::$method($request);
    }
    else {
        SalesAppApiHandler::setResponse(400);
    }
} 
catch (Exception $e) {
    SalesAppApiHandler::saveLog('[SalesAppApi] Error ' . $e->getMessage(), $e->getTrace());
    SalesAppApiHandler::setResponse(500, $e->getMessage());
}