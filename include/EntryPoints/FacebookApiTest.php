<?php

/*
    EntryPoint FacebookApiTest
    Author: Hieu Nguyen
    Date: 2020-01-17
    Purpose: test calling Facebook API
*/

class FacebookApiTest extends Vtiger_EntryPoint {

	function process (Vtiger_Request $request) {
        require_once('include/utils/FacebookUtils.php');
        $fb = FacebookUtils::getFacebookClient();
        $pageId = '294162338044491';
        $accessToken = FacebookUtils::retrieveAccessToken($pageId);
        
        // Get fanpage list
        $fanpageList = FacebookUtils::getFBFanpageList(true);
        var_dump($fanpageList);
	}
}