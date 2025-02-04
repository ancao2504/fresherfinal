<?php
/*
	File: List.php
	Author: Phuc
	Date: 2020.02.24
*/

class Leads_List_View extends Vtiger_List_View {
	
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		// Modified by Hieu Nguyen on 2021-11-16
		$jsFileNames = array(
			'~modules/CPMauticIntegration/resources/MauticHelper.js',
		);
		// End Hieu Nguyen

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}