<?php

/*
	Non-SSL Webhook Proxy
	Author: Hieu Nguyen
	Data: 2019-06-18
	Purpose: to forward all wehook requests from HTTP server into HTTPS server in case SSL error
	Usage: 
		+ Put this file in public_html folder and ask the Call Center provider to send webhook data into this url
		+ To see the latest log, access this url with param ?showlog=true&secret=onlinecrmvn123
*/

ini_set('display_errors', 0);
error_reporting(E_ALL);

$logFile = 'latest.log';
$secret = 'onlinecrmvn123';

if($_REQUEST['showlog'] && $_REQUEST['secret'] == $secret) {
	if(!file_exists($logFile)) file_put_contents($logFile, '');

	echo '<pre>'. file_get_contents($logFile) . '</pre>';
	exit;
}

logRequest();

$destUrl = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
$data = $_POST;

if(empty($data)) {
	$data = file_get_contents("php://input");
}

echo forwardRequest($destUrl, $data);

///////////////////// UTIL FUNCTIONS //////////////////////////
function forwardRequest($destUrl, $data) {
    $curl = curl_init($destUrl);

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($curl);

    if(empty($response)) return null;

    return $response;
}

function logRequest() {
	global $logFile;

	$content = 'URL Params: ' . json_encode($_GET) . "\r\n";
	$content .= 'POST Data: ' . json_encode($_POST) . "\r\n";
	$content .= 'Raw POST Data: ' . file_get_contents("php://input");

	file_put_contents($logFile, $content);
}
///////////////////// END UTIL FUNCTIONS //////////////////////////