<?php

/*
*   Class WebsiteApiUtils
*   Author: Hieu Nguyen
*   Date: 2018-12-06
*   Purpose: A parent class for bravo api
*/

require_once('include/utils/IntegrationApiUtils.php');

class WebsiteApiUtils extends IntegrationApiUtils {

    // ADD API FUNCTIONS HERE
    // Implemented by Hieu Nguyen on 2018-11-13
    static function saveLead(Vtiger_Request $request) {
        $moduleName = 'Leads';
        $data = $request->get('Data');

        $response = self::saveRecord($moduleName, $data);

        self::setResponse(200, $response);
    }
}