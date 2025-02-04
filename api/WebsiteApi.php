<?php

/*
*   WebsiteApi.php
*   Author: Hieu Nguyen
*   Date: 2018-12-06
*   Purpose: provide an endpoint for website requests
*/

chdir(dirname(__FILE__) . '/../');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/utils/WebsiteApiUtils.php');

session_start();    // Session should be started after the declaration

try {
    $request = WebsiteApiUtils::getRequest();
    $action = $request->get('RequestAction');
    
    // Login: check username and password
    if($action == 'Login') {
        WebsiteApiUtils::login($request);
    }

    // Other methods: check token
    $headers = getallheaders();
    $tokenExist = false;

    if(!empty($headers['Token'])) {
        $tokenExist = true;
    }

    if(!$tokenExist){
        WebsiteApiUtils::setResponse(401);
    }

    $sessionId = $headers['Token'];

    // Supports lowercase headers
    if(empty($sessionId)) {
        $sessionId = $headers['token'];
    }

    WebsiteApiUtils::checkSession($sessionId);

    // Logout
    if($action == 'Logout') {
        WebsiteApiUtils::logout($sessionId);
    }

    if(!empty($action)) {
        $method = strtolower($action[0]) . substr($action, 1);

        if(!method_exists('WebsiteApiUtils', $method)) {
            WebsiteApiUtils::setResponse(400);
        }
        
        WebsiteApiUtils::$method($request);
    }
    else {
        WebsiteApiUtils::setResponse(400);
    }
} 
catch (Exception $e) {
    WebsiteApiUtils::setResponse(500, $e->getMessage());
}