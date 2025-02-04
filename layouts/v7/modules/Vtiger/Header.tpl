{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}

{* Move this line here by Hieu Nguyen on 2019-04-17 for global usage *}
{assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
{* End Hieu Nguyen *}

<!DOCTYPE html>
<html>
	<head>
		<title>{vtranslate($PAGETITLE, $QUALIFIED_MODULE)}</title>
        <link rel="SHORTCUT ICON" href="layouts/v7/resources/Images/logo_favicon.ico">
        <link rel="manifest" href="manifest.json">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/bootstrap.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/docs.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/todc/css/todc-bootstrap.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/lib/font-awesome/css/font-awesome.min.css'>
		<link type='text/css' rel='stylesheet' href='layouts/v7/resources/fonts/fontawsome6/css/all.css'> {* [UI] Added by Phu Vo on 2021.04.15 *}
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/select2/select2.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/select2-bootstrap/select2-bootstrap.css'>
        <link type='text/css' rel='stylesheet' href='libraries/bootstrap/js/eternicode-bootstrap-datepicker/css/datepicker3.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/jquery-ui-1.11.3.custom/jquery-ui.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/vt-icons/style.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/animate/animate.min.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/malihu-custom-scrollbar/jquery.mCustomScrollbar.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/jquery.qtip.custom/jquery.qtip.css'>
        <link type='text/css' rel='stylesheet' href='layouts/v7/lib/jquery/daterangepicker/daterangepicker.css'>
        
        <input type="hidden" id="inventoryModules" value={ZEND_JSON::encode($INVENTORY_MODULES)}>
        
        {assign var=V7_THEME_PATH value=Vtiger_Theme::getv7AppStylePath($SELECTED_MENU_CATEGORY)}

        {* Added by Hieu Nguyen on 2019-09-17 to support custom menu groups *}
        {if !$V7_THEME_PATH}
            {assign var=V7_THEME_PATH value=Vtiger_Theme::getv7AppStylePath('SALES')}
        {/if}
        {* End Hieu Nguyen *}

        {if strpos($V7_THEME_PATH,".less")!== false}
            <link type="text/css" rel="stylesheet/less" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
        {else}
            <link type="text/css" rel="stylesheet" href="{vresource_url($V7_THEME_PATH)}" media="screen" />
        {/if}
        
        {foreach key=index item=cssModel from=$STYLES}
			<link type="text/css" rel="{$cssModel->getRel()}" href="{vresource_url($cssModel->getHref())}" media="{$cssModel->getMedia()}" />
		{/foreach}

        {* INCLUDE GLOBAL CUSTOM STYLE *}
		<link type="text/css" rel="stylesheet" href="layouts/v7/resources/font.css" media="screen" /> {* Added by Phu Vo on 2019.09.24 *}
        <link type="text/css" rel="stylesheet" href="layouts/v7/resources/custom.css" media="screen" />

		{* For making pages - print friendly *}
		<style type="text/css">
            @media print {
            .noprint { display:none; }
		}
		</style>
		<script type="text/javascript">var __pageCreationTime = (new Date()).getTime();</script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery.min.js')}"></script>
		<script src="{vresource_url('layouts/v7/lib/jquery/jquery-migrate-1.0.0.js')}"></script>

        {* Added by Hieu Nguyen on 2018-10-02 *}
        {if $CURRENT_USER_MODEL->get('id')}
            {include file="HeaderScripts.tpl"|vtemplate_path:$MODULE}
        {/if}
        {* End Hieu Nguyen *}

		<script type="text/javascript">
			var _META = { 'module': "{$MODULE}", view: "{$VIEW}", 'parent': "{$PARENT_MODULE}", 'notifier':"{$NOTIFIER_URL}", 'app':"{$SELECTED_MENU_CATEGORY}" };
            {if $EXTENSION_MODULE}
                var _EXTENSIONMETA = { 'module': "{$EXTENSION_MODULE}", view: "{$EXTENSION_VIEW}"};
            {/if}
            var _USERMETA;
            {if $CURRENT_USER_MODEL}
               _USERMETA =  { 'id' : "{$CURRENT_USER_MODEL->get('id')}", 'menustatus' : "{$CURRENT_USER_MODEL->get('leftpanelhide')}", 
                              'currency' : "{$USER_CURRENCY_SYMBOL}", 'currencySymbolPlacement' : "{$CURRENT_USER_MODEL->get('currency_symbol_placement')}",
                          'currencyGroupingPattern' : "{$CURRENT_USER_MODEL->get('currency_grouping_pattern')}", 'truncateTrailingZeros' : "{$CURRENT_USER_MODEL->get('truncate_trailing_zeros')}"};
            {/if}
		</script>

        <!-- Added by Phu Vo on 2021.12.14 to save user language to localstorage -->
        {if !empty($LANGUAGE)}
            <script type="text/javascript">
                localStorage.setItem('login_language', '{$LANGUAGE}');
            </script>
        {/if}
        <!-- End Phu Vo -->
	</head>

    {* Modified by Hieu Nguyen on 2021-09-21 *}
	<body data-skinpath="{Vtiger_Theme::getBaseThemePath()}"
        data-language="{$LANGUAGE}"
        data-module="{$MODULE}"
        data-user-decimalseparator="{$CURRENT_USER_MODEL->get('currency_decimal_separator')}"
        data-user-dateformat="{$CURRENT_USER_MODEL->get('date_format')}"
        data-user-groupingseparator="{$CURRENT_USER_MODEL->get('currency_grouping_separator')}"
        data-user-numberofdecimals="{$CURRENT_USER_MODEL->get('no_of_currency_decimals')}"
        data-user-hourformat="{$CURRENT_USER_MODEL->get('hour_format')}"
        data-user-calendar-reminder-interval="{$CURRENT_USER_MODEL->getCurrentUserActivityReminderInSeconds()}"
    >   
        <input type="hidden" id="start_day" value="{$CURRENT_USER_MODEL->get('dayoftheweek')}" /> 

        {* [CustomMenu] Added by Vu Mai on 2023-02-02 *}
        {assign var="PIN_MENU" value=Users_Preferences_Model::loadPreferences($CURRENT_USER_MODEL->get('id'), 'pin_menu') scope='global'}
        {assign var=MENU_ID value=$smarty.get.menu_id scope='global'}
        {assign var=MENU_GROUP_ID value=$smarty.get.menu_group_id scope='global'}
        {assign var=MENU_ITEM_ID value=$smarty.get.menu_item_id scope='global'}
        {assign var=MAIN_MENU value=Settings_MenuEditor_Data_Model::getMainMenuInfo($MENU_ID) scope='global'}

        {if empty($MAIN_MENU)}
            {assign var=MAIN_MENU_COLOR value='#2c3b49'}
        {else}
            {assign var=MAIN_MENU_COLOR value=$MAIN_MENU.color}
        {/if}

        {if $CURRENT_USER_MODEL->get('language') == 'vn_vn'}
            {assign var=MAIN_MENU_NAME value=$MAIN_MENU.name_vn scope='global'}
        {else}
            {assign var=MAIN_MENU_NAME value=$MAIN_MENU.name_en scope='global'}
        {/if}

        <style>
            html {
                --main-menu-color: {$MAIN_MENU_COLOR};
            } 
        </style>
        {* End Vu Mai *}

		<div id="page" class="{if $PIN_MENU == 'true'}fixed-menu{/if} {$MODULE}"> <!-- [CustomMenu] Modified by Vu Mai on 2023-02-02 -->
            <div id="pjaxContainer" class="hide noprint"></div>
            <div id="messageBar" class="hide"></div>

            {if $CURRENT_USER_MODEL->getId() && $HEADER_WARNING}
                <!-- Header Warning -->
                <div id="header-warning">
                    {$HEADER_WARNING}
                </div>

                <link type="text/css" rel="stylesheet" href="{vresource_url('modules/Vtiger/resources/HeaderWarning.css')}" />
                <!-- End Header Warning -->
            {/if}
    {* End Hieu Nguyen *}