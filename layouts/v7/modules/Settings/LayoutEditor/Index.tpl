{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/LayoutEditor/views/Index.php *}

{strip}
	<div class="container-fluid main-scroll" id="layoutEditorContainer">
		<input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
		<input class="selectedTab" type="hidden" value="{$SELECTED_TAB}">
		<input class="selectedMode" type="hidden" value="{$MODE}">
		<input type="hidden" id="selectedModuleLabel" value="{vtranslate($SELECTED_MODULE_NAME,$SELECTED_MODULE_NAME)}" />
		<div class="widget_header row">
			<label class="col-sm-2 textAlignCenter" style="padding-top: 7px;">
				{vtranslate('SELECT_MODULE', $QUALIFIED_MODULE)}
			</label>
			<div class="col-sm-6">
				<select class="select2 col-sm-6" name="layoutEditorModules">
					<option value=''>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
					{foreach item=MODULE_NAME key=TRANSLATED_MODULE_NAME from=$SUPPORTED_MODULES}
						<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>
							{$TRANSLATED_MODULE_NAME}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
		<br>
		<br>
		{if $SELECTED_MODULE_NAME}
			<div class="contents tabbable">
				<ul class="nav nav-tabs layoutTabs massEditTabs marginBottom10px">
					{assign var=URL value="index.php?module=LayoutEditor&parent=Settings&view=Index"}

					{* Modified by Hieu Nguyen on 2018-07-31 *}
					<li class="{if $SELECTED_TAB eq 'editViewTab'}active {/if}layoutEditorTab editViewTab reload-tab"><a data-toggle="tab" href="#editViewLayout" data-url="{$URL}" data-mode="showFieldLayoutEditView"><strong>{vtranslate('LBL_EDITVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong></a></li>
					<li class="{if $SELECTED_TAB eq 'detailViewTab'}active {/if}layoutEditorTab detailViewTab reload-tab"><a data-toggle="tab" href="#detailViewLayout" data-url="{$URL}" data-mode="showFieldLayoutDetailView"><strong>{vtranslate('LBL_DETAILVIEW_LAYOUT', $QUALIFIED_MODULE)}</strong></a></li>
					{* End Hieu Nguyen *}

					{* Modified by Hieu Nguyen on 2018-12-24 *}
					{if isDeveloperMode()}
                        {* Added by Hieu Nguyen on 2019-10-09 *}
                        <li class="{if $SELECTED_TAB eq 'popupAndRelationListLayoutTab'}active {/if}popupAndRelationListTab reload-tab"><a data-toggle="tab" href="#popupAndRelationListLayout" data-url="{$URL}" data-mode="showPopupAndRelationListLayout"><strong>{vtranslate('LBL_POPUP_AND_RELATION_LIST_LAYOUT', $QUALIFIED_MODULE)}</strong></a></li>
                        {* End Hieu Nguyen *}
					{/if}
					{* End Hieu Nguyen *}

					<li class="{if $SELECTED_TAB eq 'relatedListTab'}active {/if}relatedListTab"><a data-toggle="tab" href="#relatedTabOrder" data-url="{$URL}" data-mode="showRelatedListLayout"><strong>{vtranslate('LBL_RELATION_SHIPS', $QUALIFIED_MODULE)}</strong></a></li>
					
					{* Modified by Hieu Nguyen on 2022-03-01 to check forbidden feature *}
					{if !isForbiddenFeature('TranslationEditor')}
						<li class="{if $SELECTED_TAB eq 'translationEditorTab'}active {/if}translationEditorTab reload-tab"><a data-toggle="tab" href="#translationEditor" data-url="{$URL}" data-mode="showTranslationEditor"><strong>{vtranslate('LBL_TRANSLATION_EDITOR', $QUALIFIED_MODULE)}</strong></a></li>
					{/if}
					{* End Hieu Nguyen *}

					<li class="{if $SELECTED_TAB eq 'duplicationTab'}active {/if}duplicationTab"><a data-toggle="tab" href="#duplicationContainer" data-url="{$URL}" data-mode="showDuplicationHandling"><strong>{vtranslate('LBL_DUPLICATE_HANDLING', $QUALIFIED_MODULE)}</strong></a></li>
				</ul>
				<div class="tab-content layoutContent themeTableColor overflowVisible">
					{* Modified by Hieu Nguyen on 2018-07-31 *}
					<div class="tab-pane layoutEditor {if $SELECTED_TAB eq 'editViewTab'} active{/if}" id="editViewLayout">
						{if $SELECTED_TAB eq 'editViewTab'}
							{include file=vtemplate_path('FieldsList.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
					{* End Hieu Nguyen *}
					
					<div class="tab-pane layoutEditor {if $SELECTED_TAB eq 'detailViewTab'} active{/if}" id="detailViewLayout">
						{if $SELECTED_TAB eq 'detailViewTab'}
							{include file=vtemplate_path('FieldsList.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>

                    {* Added by Hieu Nguyen on 2019-10-09 *}
                    <div class="tab-pane {if $SELECTED_TAB eq 'popupAndRelationListLayoutTab'} active{/if}" id="popupAndRelationListLayout">
						{if $SELECTED_TAB eq 'popupAndRelationListLayoutTab'}
							{include file='modules/Settings/LayoutEditor/tpls/PopupAndRelationListLayout.tpl'}
						{/if}
					</div>
                    {* End Hieu Nguyen *}

					{* Added by Hieu Nguyen on 2018-08-07 *}
					<div class="tab-pane {if $SELECTED_TAB eq 'translationEditorTab'} active{/if}" id="translationEditor">
						{if $SELECTED_TAB eq 'translationEditorTab'}
							{include file='modules/Settings/LayoutEditor/tpls/TranslationEditor.tpl'}
						{/if}
					</div>
					{* End Hieu Nguyen *}

					<div class="tab-pane {if $SELECTED_TAB eq 'relatedListTab'} active{/if}" id="relatedTabOrder">
						{if $SELECTED_TAB eq 'relatedListTab'}
							{include file=vtemplate_path('RelatedList.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
					<div class="tab-pane{if $SELECTED_TAB eq 'duplicationTab'} active{/if}" id="duplicationContainer">
						{if $SELECTED_TAB eq 'duplicationTab'}
							{include file=vtemplate_path('DuplicateHandling.tpl', $QUALIFIED_MODULE)}
						{/if}
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}