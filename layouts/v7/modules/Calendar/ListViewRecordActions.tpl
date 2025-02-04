{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	<div class="table-actions calendar-table-actions">
		{if !$SEARCH_MODE_RESULTS}
			<span class="input" >
				<input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
			</span>
		{/if}
		{if $LISTVIEW_ENTRY->get('starred') eq {vtranslate('LBL_YES')}} {*Modified by Hoang Duc to fix not display followed record in list view*}
			{assign var=STARRED value=true}
		{else}
			{assign var=STARRED value=false}
		{/if}
		{if $QUICK_PREVIEW_ENABLED eq 'true'}
			<span>
				<a class="quickView far fa-eye icon action" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
			</span>
		{/if}
		{if $MODULE_MODEL->isStarredEnabled()}
			<span>
				<a class="markStar far icon action {if $STARRED} fa-star active {else} fa-star{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
			</span>
		{/if}
		{assign var=EDIT_VIEW_URL value={$LISTVIEW_ENTRY->getEditViewUrl()}}
		{if $IS_MODULE_EDITABLE && $EDIT_VIEW_URL && $LISTVIEW_ENTRY->get('taskstatus') neq vtranslate('Held', $MODULE) && $LISTVIEW_ENTRY->get('taskstatus') neq vtranslate('Completed', $MODULE)}
			<span class="far fa-check icon action markAsHeld" title="{vtranslate('LBL_MARK_AS_HELD', $MODULE)}" onclick="Calendar_Calendar_Js.markAsHeld('{$LISTVIEW_ENTRY->getId()}');"></span>
		{/if}
		{if $IS_CREATE_PERMITTED && $EDIT_VIEW_URL && $LISTVIEW_ENTRY->get('taskstatus') eq vtranslate('Held', $MODULE)}
			<span class="far fa-flag icon action holdFollowupOn" title="{vtranslate('LBL_HOLD_FOLLOWUP_ON', "Events")}" onclick="Calendar_Calendar_Js.holdFollowUp('{$LISTVIEW_ENTRY->getId()}');"></span>
		{/if}

        {* Added by Hieu Nguyen on 2020-07-13 *}
        {if $RECORD_ACTIONS}
            {if $RECORD_ACTIONS['edit'] && $LISTVIEW_ENTRY->isEditable()}
                <span>
                    <a class="far fa-pen icon action edit" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}" name="editlink" title="{vtranslate('LBL_EDIT', $MODULE)}"></a>
                </span>
            {/if}
        {/if}
        {* End Hieu Nguyen *}

        {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row actions *}
        {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowActions.tpl"}

        {if file_exists($CUSTOM_ROW_ACTIONS)}
            {include file=$CUSTOM_ROW_ACTIONS}
        {/if}
        {* End Hieu Nguyen *}

		<span class="more dropdown action">
			<span href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
				<i class="far fa-ellipsis-v-alt icon"></i></span>
			<ul class="dropdown-menu">
				<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{vtranslate('LBL_DETAILS', $MODULE)}</a></li>
                
                {* Modified by Hieu Nguyen on 2020-07-13 to move edit button outside *}
                {if $RECORD_ACTIONS}
                    {if $RECORD_ACTIONS['delete'] && $LISTVIEW_ENTRY->isDeletable()} {* Modified by Phu Vo on 2019.09.16 to show/hide delete button base on record deletable *}
                        <li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
                    {/if}
                {/if}
                {* End Hieu Nguyen *}
                
                {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row advanced actions *}
                {assign var="CUSTOM_ROW_ADVANCED_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowAdvancedActions.tpl"}

                {if file_exists($CUSTOM_ROW_ADVANCED_ACTIONS)}
                    {include file=$CUSTOM_ROW_ADVANCED_ACTIONS}
                {/if}
                {* End Hieu Nguyen *}
			</ul>
		</span>

		<div class="btn-group inline-save hide">
			<button class="button btn-success btn-small save" name="save"><i class="far fa-check"></i></button>
			<button class="button btn-danger btn-small cancel" name="Cancel"><i class="far fa-close"></i></button>
		</div>
	</div>
{/strip}