{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* Modified by Hieu Nguyen on 2023-01-29 *}
{* Modified by Vu Mai on 2023-02-02 *}

<div class="app-menu" id="app-menu">
	<input type='hidden' name='pin_menu' value="{$PIN_MENU}">
	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-2 col-xs-2 cursorPointer app-switcher-container">
				<div class="row app-navigator">
					<span id="menu-toggle-action" class="app-icon far fa-bars"></span>
				</div>
			</div>

			<span id="btn-pin-menu" class="cursorPointer pull-right {if $PIN_MENU == 'true'}active{/if}"><i class="fas fa-thumbtack"></i></span>
		</div>

		{assign var='LICENSE_INFO' value=parseLicense()}
		{assign var='HAVE_LICENSE_INFO' value=false}

		{if $LICENSE_INFO.license.lifetime_license == false || $LICENSE_INFO.license.max_storage !== -1 || $LICENSE_INFO.license.max_normal_users !== -1}
			{assign var='HAVE_LICENSE_INFO' value=true}
		{/if}

		<div class="app-list row fancyScrollbar {if $HAVE_LICENSE_INFO}have-license-info{/if}">
			{assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
			{assign var=HOME_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Home')}
			{assign var=DASHBOARD_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Dashboard')}
			{assign var=MENUS value=Settings_MenuEditor_Structure_Model::getDisplayStructure()}

			{if $USER_PRIVILEGES_MODEL->hasModulePermission($DASHBOARD_MODULE_MODEL->getId())}
				<div class="home-menu-item menu-item app-item dropdown-toggle flex" data-default-url="{$HOME_MODULE_MODEL->getDefaultUrl()}">
					<div class="app-color"></div>
					<div class="menu-items-wrapper">
						<span class="app-icon-list far fa-dashboard"></span>
						<span class="app-name textOverflowEllipsis"> {vtranslate('LBL_DASHBOARD',$MODULE)}</span>
					</div>
				</div>
			{/if}

			{foreach key=KEY item=MENU from=$MENUS}
				{if count($MENU.children) > 0 && $MENU.items_menu_count > 0}
					<div class="dropdown app-modules-dropdown-container">
						<div class="menu-item app-item dropdown-toggle flex" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
							<div class="app-color" style="background-color: {$MENU.color};"></div>
							<div class="menu-items-wrapper app-menu-items-wrapper">
								<span class="app-icon-list {$MENU.icon}"></span>
								<span class="app-name textOverflowEllipsis">{$MENU.name}</span>
								<span class="far fa-chevron-right pull-right"></span>
							</div>
						</div>
						<div class="dropdown-menu app-modules-dropdown" layout="{if $MENU.layout == '1_column'}1_column{elseif count($MENU.children) >= 3}{$MENU.layout}{elseif count($MENU.children) >= 2}2_columns{else}1_column{/if}">
							<div class="flex-wrap col-md-12 fancyScrollbar">
								{foreach key=MENU_GROUP_NAME item=MENU_GROUP_ITEMS from=$MENU.children}
									{if count($MENU_GROUP_ITEMS) > 0}
										<div class="menu-group {if $MENU.layout == '3_columns' && count($MENU.children) >= 3}col-md-4{elseif ($MENU.layout == '3_columns' || $MENU.layout == '2_columns') && count($MENU.children) >= 2}col-md-6{else}col-md-12{/if}">
											{if $MENU_GROUP_NAME != 'uncategorized'}
												<h4 class="menu-group-title textOverflowEllipsis">{$MENU_GROUP_NAME}</h4>
											{/if}

											<ul class="menu-items">
												{foreach key=KEY item=MENU_ITEM from=$MENU_GROUP_ITEMS}
													<li>
														<a href="{if $MENU_ITEM.type == 'module'}{$MENU_ITEM.url}{else}{$MENU_ITEM.value.url}{/if}" {if $MENU_ITEM.type == 'web_url' && $MENU_ITEM.value.open_in_new_tab == 'true'}target="_blank"{/if}>
															<span class="module-icon {if $MENU_ITEM.type == 'module'}{$MENU_ITEM.icon}{else}{$MENU_ITEM.value.icon}{/if}"></span>
															<span class="module-name textOverflowEllipsis">{$MENU_ITEM.name}</span>
														</a>
													</li>
												{/foreach}
											</ul>
										</div>
									{/if}
								{/foreach}
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		{* [License] Added by Hieu Nguyen on 2022-10-21 to show License Info *}
		{include file='modules/Vtiger/tpls/LicenseInfoSidebarAppMenu.tpl'}
		{* End Hieu Nguyen *}
	</div>
</div>