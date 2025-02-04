<?php

/*
    EntryPoint structure
    Author: Hieu Nguyen
    Date: 2018-08-24
    Purpose: provide an entry point structure similar to SugarCRM
    Usage:
        - Copy this file into a new file, then rename the file name and class name that is corresponding to your logic
        - When you access /entrypoint.php?name=<Entry-Point-Name>, the entry point inside include/EntryPoints/<Entry-Point-Name>.php will be activated
*/

class SampleEntryPoint extends Vtiger_EntryPoint {

	function process (Vtiger_Request $request) {
        // Handle your logic and response the result here
		echo 'Hello World!';
	}
}