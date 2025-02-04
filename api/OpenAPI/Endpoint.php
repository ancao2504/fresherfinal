<?php

/*
	OpenAPI Endpoint.php
	Author: Hieu Nguyen
	Date: 2022-12-28
	Purpose: provide an endpoint for Open API to allow any client to integrate with CRM via RESTful APIs
*/

chdir(dirname(__FILE__) . '/../../');

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
require_once('includes/Loader.php');
require_once('include/utils/VtlibUtils.php');
require_once('includes/runtime/EntryPoint.php');
require_once('include/Webservice/OpenApiHandler.php');

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
	$request = OpenApiHandler::getRequest();
	$requestData = $request->getAll();
	saveLog('OPEN_API', 'Request data', $requestData);

	// Handle request action
	$action = $request->get('action');

	// Action auth: check username and access key
	if ($action == 'auth') {
		OpenApiHandler::auth($request);
	}
	// Other actions: check access token
	else {
		$headers = getallheaders();
		$accessToken = $headers['Access-Token'];

		if (empty($accessToken)) {
			OpenApiHandler::setResponse(400, 'Header param Access-Token is required!');
		}

		OpenApiHandler::checkSession($accessToken);

		if (!empty($action)) {
			$moduleName = $request->getModule();

			if (isForbiddenFeature('OpenAPI')) {
				OpenApiHandler::setResponse(400, 'Your package does not support Open API!');
			}

			if (empty(trim($moduleName)) && in_array($action, OpenApiHandler::ACTIONS_REQUIRED_MODULE_NAME)) {
				OpenApiHandler::setResponse(400, 'Module name is required!');
			}

			if (isset($requestData['module'])) {
				if (!is_string($moduleName)) {
					OpenApiHandler::setResponse(400, 'Module name is invalid!');
				}
			}			

			if (!empty($moduleName) && !vtlib_isModuleActive($moduleName)) {
				OpenApiHandler::setResponse(400, 'Module is not exist or inactive!');
			}

			$method = $action;

			if (!method_exists('OpenApiHandler', $method)) {
				OpenApiHandler::setResponse(400, 'This action is not supported');
			}
			
			OpenApiHandler::$method($request);
		}
		else {
			OpenApiHandler::setResponse(400);
		}
	}
} 
catch (Exception $e) {
	saveLog('OPEN_API', '[OpenAPI] Error ' . $e->getMessage(), $e->getTrace());
	OpenApiHandler::setResponse(500, $e->getMessage());
}