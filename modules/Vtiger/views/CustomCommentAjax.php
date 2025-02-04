<?php

/*
	File: GetComments.php
	Author: Vu Mai
	Date: 2022-09-09
	Purpose: Render parent or child comment list
*/

class Vtiger_CustomCommentAjax_View extends CustomView_Base_View {

	function __construct() {
		$this->exposeMethod('getParentComments');
		$this->exposeMethod('getChildComments');
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

	function getParentComments(Vtiger_Request $request) {
		global $adb;
		$customerId = $request->get('customer_id');
		$maxResults = intval($request->get('max_results'));
		$offset = intval($request->get('offset'));

		$fullNameConcatSql = getSqlForNameInDisplayFormat(['first_name' => 'u.first_name', 'last_name' => 'u.last_name'], 'Users');
		$sql = "SELECT c.modcommentsid AS id, c.commentcontent AS content, c.userid AS user_id, {$fullNameConcatSql} AS user_fullname, ce.createdtime AS created_time 
			FROM vtiger_modcomments AS c
			INNER JOIN vtiger_crmentity AS ce ON (c.modcommentsid = ce.crmid AND ce.deleted = 0)
			INNER JOIN vtiger_users AS u ON (u.id = c.userid)
			WHERE c.related_to = ? AND c.parent_comments = 0
			ORDER BY ce.createdtime DESC
			LIMIT ?, ?";
		$queryParams = [$customerId, $offset, $maxResults];
		$result = $adb->pquery($sql, $queryParams);
		$comments = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);
			$row['comment_content'] = renderCommentWithMentions($row['content']);
			$row['comment_title'] = $this->generateCommentTitle($row);

			// User extra query for simplicity
			$sql = "SELECT COUNT(vtiger_modcomments.modcommentsid) FROM vtiger_modcomments 
				INNER JOIN vtiger_crmentity ON (vtiger_modcomments.modcommentsid = vtiger_crmentity.crmid AND vtiger_crmentity.setype = 'ModComments' AND vtiger_crmentity.deleted = 0)       
				WHERE vtiger_modcomments.related_to = ? AND vtiger_modcomments.parent_comments = ?";
			$queryParams = [$customerId, $row['id']];
			$childCount = $adb->getOne($sql, $queryParams);
			
			$row['child_count'] = $childCount;
			$comments[] = $row;
		}

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('COMMENTS', $comments);
		$result = $viewer->fetch('modules/Vtiger/tpls/ListComments.tpl');
		echo $result;
	}

	function getChildComments(Vtiger_Request $request) {
		global $adb;
		$parentCommentId = $request->get('parent_id');

		$fullNameConcatSql = getSqlForNameInDisplayFormat(['first_name' => 'u.first_name', 'last_name' => 'u.last_name'], 'Users');
		$sql = "SELECT  c.modcommentsid AS id, c.commentcontent AS content, c.userid AS user_id, {$fullNameConcatSql} AS user_fullname, ce.createdtime AS created_time
			FROM vtiger_modcomments AS c
			INNER JOIN vtiger_crmentity AS ce ON (c.modcommentsid = ce.crmid AND ce.deleted = 0)
			INNER JOIN vtiger_users AS u ON (u.id = c.userid)
			WHERE  c.parent_comments = ?
			ORDER BY ce.createdtime DESC";
		$queryParams = [$parentCommentId];
		$result = $adb->pquery($sql, $queryParams);
		$comments = [];

		while ($row = $adb->fetchByAssoc($result)) {
			$row = decodeUTF8($row);
			$row['comment_content'] = renderCommentWithMentions($row['content']);
			$row['comment_title'] = $this->generateCommentTitle($row);
			$comments[] = $row;
		}

		// Render view
		$viewer = $this->getViewer($request);
		$viewer->assign('COMMENTS', $comments);
		$result = $viewer->fetch('modules/Vtiger/tpls/ListComments.tpl');
		echo $result;
	}

	function generateCommentTitle($comment) {
		$dateTime = new DateTimeField($comment['created_time']);

		$replaceParams = [
			'%user_name' => $comment['user_fullname'],
			'%comment_date' => $dateTime->getDisplayDate(),
			'%comment_time' => $dateTime->getDisplayTime(),
			'%comment_content' => strip_tags(renderCommentWithMentions($comment['content'])),
		];

		$commentContent = vtranslate('LBL_CUSTOM_COMMENT_COMMENT_TITLE', 'Vtiger', $replaceParams);
		return $commentContent;
	}
}