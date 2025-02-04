{* Added by Hieu Nguyen on 2018-08-29 to override the original ListViewPreProcesss.tpl *}

{include file="modules/Vtiger/partials/Topbar.tpl"}

{strip}
	<div class="container-fluid app-nav">
		<div class="row">
			{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
			
			<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-{$SELECTED_MENU_CATEGORY}">
				<div class="row" title="{strtoupper(vtranslate($TARGET_MODULE_MODEL->getName()))}" onclick="location.href='{$TARGET_MODULE_MODEL->getListViewUrl()}';">
					{$TARGET_MODULE_MODEL->getModuleIcon()}
				</div>
			</div>
			
			{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}
			
			<div class="col-sm-12 col-xs-12 module-action-bar clearfix"></div>
		</div>
	</div>
</nav>
<div class="main-container">
	{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get("leftpanelhide")}
	
	<div id="modnavigator" class="module-nav calendar-navigator"> {* Modified by Phu Vo on 2020.12.25 *}
		<div class="hidden-xs hidden-sm mod-switcher-container">
			
		</div>
	</div>
	
	<div id="custom-view" class="content-area full-width" style="padding-left: 55px !important; padding-top: 10px !important">
{/strip}
