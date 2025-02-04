{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* [CustomMenu] Modified by Vu Mai on 2022-02-08 *}

<div class="col-sm-12 col-xs-12 app-indicator-icon-container">
	<div class="row" title="{if $MODULE eq 'Home' || !$MODULE}{vtranslate('LBL_DASHBOARD')}{else}{$MAIN_MENU_NAME}{/if}" data-toggle="tooltip" data-placement="right">	
		<span class="app-indicator-icon
			{if $MODULE eq 'Home' || !$MODULE}
				fal fa-dashboard
			{elseif empty($MAIN_MENU.icon)}
				{assign var=MODULE_ICON value=getGlobalVariable('moduleIcons')}
				{$MODULE_ICON[$MODULE]}
			{else}
				fal {$MAIN_MENU.icon}
			{/if}
		"></span>	
	</div>
</div>

{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}