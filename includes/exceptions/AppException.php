<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

// Modified by Hieu Nguyen on 2021-08-27 to store title & message in app exception
class AppException extends Exception {
    protected $title;

    function __construct($message = '', $code = 0, $title = '') {
        parent::__construct($message, $code);
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }
}