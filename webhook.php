<?php

/*
    Webhook structure
    Author: Hieu Nguyen
    Date: 2018-10-04
    Purpose: provide a webhook entrypoint to integrate with 3rd systems that support webhook
    Usage: when you access /webhook.php?name=<Webhook-Name>, the webhook inside include/WebHooks/<Webhook-Name>.php will be activated
*/

require_once('config.php');
require_once('include/Webservices/Relation.php');
require_once('vtlib/Vtiger/Module.php');
$specialConnectors = ['VCSConnector'];   // Define which connectors to ignore secret_key check

if (!in_array($_GET['name'], $specialConnectors)) {
    checkSecretKey($_GET['secret_key']);  // Check secret key before doing anything
}

$webhookName = preg_replace('/[^a-zA-Z0-9]/', '', $_REQUEST['name']);
$webhookFile = 'include/Webhooks/' . $webhookName . '.php';

if (file_exists($webhookFile)) {
    require_once('includes/Loader.php');
    require_once('include/utils/VtlibUtils.php');
    require_once('includes/runtime/EntryPoint.php');
    require_once($webhookFile);
    global $current_user;

    if ($current_user == null) {
        $current_user = Users::getRootAdminUser();    // Bypass permission check
    }
    
    // For testing
    if ($_REQUEST['test'] == 'true') {
        if (in_array($_REQUEST['name'], ['ZaloConnector', 'FacebookConnector'])) {
            include_once('include/Webhooks/SocialTestData.php');
        }
        else {
            include_once('include/Webhooks/WebhookTestData.php');
        }
    }

    $webhook = new $webhookName();
    $webhook->process(new Vtiger_Request($_REQUEST, $_REQUEST));
}
else {
    echo 'Webhook not found!';
}