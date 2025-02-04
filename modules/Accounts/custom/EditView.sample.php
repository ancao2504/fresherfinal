<?php

/*
*	CustomCode Structure
*	Author: Hieu Nguyen
*	Date: 2018-07-17
*	Purpose: customize the layout easily with configurable display params
*/

$displayParams = array(
	'scripts' => '
		<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Accounts/resources/EditView.css")}" />
		<script type="text/javascript" src="{vresource_url("modules/Accounts/resources/EditView.js")}"></script>
	',
	'form' => array(
		'hiddenFields' => '
			<input type="hidden" name="is_draft" value="{$RECORD->get("is_draft")}" />
			<input type="hidden" name="is_awesome" value="{$RECORD->get("is_awesome")}" />
		',
	),
	'fields' => array(
		'fullname' => array(
			// Simple template can be defined here
			'customTemplate' => '<textarea name="notes">{$RECORD->get("notes")}}</textarea>',
		),
		'interests' => array(
			// But complicated or multi-lines template PLEASE link to external file
			'customTemplate' => '{include file="modules/Accounts/tpls/InterestsFieldEditView.tpl"}',
		),
	),
);