<?php

/*
	File: CustomTagViewAjax.php
	Author: Vu Mai
	Date: 2022-09-07
	Purpose: return custom tag view
*/

class Vtiger_CustomTagViewAjax_View extends CustomView_Base_View {

	function __construct() {
		$this->exposeMethod('getTagList');
		$this->exposeMethod('getTaggingModal');
	}

	function validateRequest(Vtiger_Request $request) {
		$request->validateWriteAccess(); 
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();

		if (!empty($mode) && $this->isMethodExposed($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	function getTagList(Vtiger_Request $request) {
		$tagList = $this->getSelectedTags($request);
		$tagListShow = array_slice($tagList, 0 , 2);

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('TAG_LIST', $tagList);
		$viewer->assign('TAG_LIST_SHOW', $tagListShow);
		$result = $viewer->fetch('modules/Vtiger/tpls/TagList.tpl');
		echo $result;
	}

	function getTaggingModal(Vtiger_Request $request) {
		$selectedTags = $this->getSelectedTags($request);

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('SELECTED_TAGS', $selectedTags);
		$result = $viewer->fetch('modules/Vtiger/tpls/TaggingModal.tpl');
		echo $result;
	}

	function getSelectedTags($request) {
		$result = CPSocialIntegration_SocialChatboxPopup_Model::getCustomerTags($request->get('customer_id'), $request->get('customer_type'));
		return $result;
	}
}