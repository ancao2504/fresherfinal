{* Added by Hieu Nguyen on 2023-01-18 *}

{strip}
	<link rel="stylesheet" href="{vresource_url('libraries/jquery/bootstrapswitch/css/bootstrap3/bootstrap-switch.min.css')}" />
	<script src="{vresource_url('libraries/jquery/bootstrapswitch/js/bootstrap-switch.min.js')}"></script>
	<link rel="stylesheet" href="{vresource_url('resources/libraries/FontAwesomeIconPicker/fontawesome-iconpicker.min.css')}"></link>
	<script src="{vresource_url('resources/libraries/FontAwesomeIconPicker/fontawesome-iconpicker.min.js')}"></script>
	<script src="{vresource_url('resources/CustomColorPicker.js')}"></script>

	<link rel="stylesheet" href="{vresource_url('modules/Settings/MenuEditor/resources/MenuEditor.css')}"></link>

	<div id="menu-editor">
		<div id="hint-text" class="row-fluid">
			<div class="vt-default-callout vt-info-callout">
				<h4 class="vt-callout-header"><i class="far fa-info-circle"></i>&nbsp;{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_MENU_EDITOR_INFO', $QUALIFIED_MODULE)}</p>
			</div>
		</div>

		<div id="menu-editor-wrapper" class="row-fluid">
			<div id="list-view">
				<div id="main-menus" class="fancyScrollbar">
					{include file='modules/Settings/MenuEditor/tpls/MainMenuList.tpl'}
				</div>
				<br/>
				<div class="text-center">
					<button type="button" id="btn-add-main-menu" name="add_main_menu" class="btn btn-default"><i class="fal fa-plus"></i> {vtranslate('LBL_ADD_MAIN_MENU', $QUALIFIED_MODULE)}</button>
				</div>
			</div>

			<div id="edit-view">
				<div id="top-actions" class="row-fluid">
					<div class="pull-right">
						<button type="button" name="layout" disabled class="btn btn-default svg-icon" value="1_column" data-toggle="tooltip" title="{vtranslate('LBL_BTN_1COLUMN_TOOLTIP', $QUALIFIED_MODULE)}"><img src="modules/Settings/MenuEditor/resources/images/1_column.png" height="20"></button>
						<button type="button" name="layout" disabled class="btn btn-default svg-icon" value="2_columns" data-toggle="tooltip" title="{vtranslate('LBL_BTN_2COLUMNS_TOOLTIP', $QUALIFIED_MODULE)}"><img src="modules/Settings/MenuEditor/resources/images/2_columns.svg" height="20" /></button>
						<button type="button" name="layout" disabled class="btn btn-default svg-icon" value="3_columns" data-toggle="tooltip" title="{vtranslate('LBL_BTN_3COLUMNS_TOOLTIP', $QUALIFIED_MODULE)}"><img src="modules/Settings/MenuEditor/resources/images/3_columns.svg" height="23" /></button>
						<button type="button" name="add_menu_group" disabled class="btn btn-default"><i class="fal fa-plus"></i> {vtranslate('LBL_ADD_MENU_GROUP', $QUALIFIED_MODULE)}</button>
					</div>
					<div class="clearFix"></div>
				</div>
				<div id="edit-view-hint-text" class="row-fluid text-center">{vtranslate('LBL_EDIT_VIEW_HINT_TEXT', $QUALIFIED_MODULE)}</div>
				<div id="edit-view-content" class="row-fluid fancyScrollbar"></div>
			</div>

			<!-- Modal add menu item -->
			<div class="modal-dialog modal-sm modal-content modal-add-menu-item hide">
				{include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE=vtranslate('LBL_MODAL_ADD_MENU_ITEM_TITLE', $QUALIFIED_MODULE)}

				<form name="add-menu-item" class="form-horizontal">
					<input type="hidden" name="main_menu_id" value="" />
					<input type="hidden" name="menu_group_id" value="" />

					<div class="form-content fancyScrollbar padding20">
						<button type="button" id="btn-add-modules" class="btn btn-default text-center"><i class="fal fa-cubes"></i> {vtranslate('LBL_MODAL_ADD_MENU_ITEM_MODULES', $QUALIFIED_MODULE)}</button>
						<button type="button" id="btn-add-web-url" class="btn btn-default text-center"><i class="fal fa-link"></i> {vtranslate('LBL_MODAL_ADD_MENU_ITEM_WEB_URL', $QUALIFIED_MODULE)}</button>
						<button type="button" id="btn-add-report" class="btn btn-default text-center"><i class="fal fa-chart-column"></i> {vtranslate('LBL_MODAL_ADD_MENU_ITEM_REPORT', $QUALIFIED_MODULE)}</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}