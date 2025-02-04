<?php


$displayParams = array(
   'scripts' => '
		<link type="text/css" rel="stylesheet" href="{vresource_url("modules/Accounts/resources/DetailView.css")}" />
		<script type="text/javascript" src="{vresource_url("modules/Accounts/resources/DetailView.js")}"></script>
	',
    'form' => array(
        'hiddenFields' => '',
    ),
    'fields' => array(
        'accounts_business_type' => array(
            'customTemplate' => '{include file="modules/Accounts/tpls/BusinessTypeQuickCreate.tpl"}',
        ),
    ),
);