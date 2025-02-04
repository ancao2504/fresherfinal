{* Added by Hieu Nguyen on 2018-08-29 to override the original ListViewPreProcesss.tpl *}

{include file="modules/Vtiger/partials/Topbar.tpl"}

{strip}
	<div class="container-fluid app-nav">
		<div class="row">
			{include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
			{include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
		</div>
	</div>
	</nav>
		<div id="overlayPageContent" class="fade modal overlayPageContent content-area overlay-container-60" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="data"></div>
			<div class="modal-dialog"></div>
		</div>
		<div class="main-container">
			<div id="modnavigator" class="module-nav calendar-navigator">
				<div class="hidden-xs hidden-sm mod-switcher-container">
					{include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
				</div>
			</div>
			<div id="custom-view" class="content-area full-width content-area-{$MODULE}-{$VIEW}"> {* Modified by Phu Vo on 2021.11.15 *}
{/strip}
