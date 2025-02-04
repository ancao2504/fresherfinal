<?php
$displayParams = array(
    'scripts' => '
    <link type="text/css" rel="stylesheet" href="{vresource_url("modules/Accounts/resources/EditView.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Accounts/resources/EditView.js")}"></script>
',
	'form' => array(
		'hiddenFields' => '
		',
	),
	'fields' => array(
		
		'accounts_business_type' => array(
			// But complicated or multi-lines template PLEASE link to external file
			'customTemplate' => '{include file="modules/Accounts/tpls/BusinessTypeEditView.tpl"}',
		),
	),
);

