<?php

/*
	Iframe View
	Author: Hieu Nguyen
	Date: 2021-10-25
	Purpose: to support loading external web url as an iframe view inside CRM
	URL format: index.php?module={MODULE_NAME}&view=Iframe&iframe_url={IFRAME_URL}&custom_title={CUSTOM_TITLE}
		- MODULE_NAME: the name of module whe iframe should belong to. To load iframe for chatbot vendor, set MODULE_NAME to CPChatBotIntegration
		- IFRAME_URL: a base64 encoded string of iframe url, it's better to do urlencode also. Ex: urlencode(base64_encode('https://chatbot.com/live-chat'))
		- CUSTOM_TITLE: a base64 encoded string of the title you want to display, it's better to do urlencode also. Ex: urlencode(base64_encode('Live Chat'))
*/

class Vtiger_Iframe_View extends CustomView_Base_View {

	function __construct() {
		parent::__construct(true);
	}

    function checkPermission(Vtiger_Request $request) {
        return;
	}

	function getPageTitle(Vtiger_Request $request) {
		if (!empty($request->get('custom_title'))) {
			return base64_decode($request->get('custom_title'));
		}
		
		return parent::getPageTitle($request);
	}

	function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer($request);

		if (!empty($request->get('custom_title'))) {
			$viewer->assign('CUSTOM_TITLE', $this->getPageTitle($request));
		}

		parent::preProcess($request, $display);
	}

    function process(Vtiger_Request $request) {
		$encodedIframeUrl = $request->get('iframe_url');

		if (empty($encodedIframeUrl)) {
			throw new AppException('Iframe URL is empty!', 400);
			
		}

		$viewer = $this->getViewer($request);
		$viewer->assign('IFRAME_URL', base64_decode($encodedIframeUrl));
		$viewer->display('modules/Vtiger/tpls/Iframe.tpl');
    }
}