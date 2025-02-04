{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
<!--LIST VIEW RECORD ACTIONS-->

<div class="table-actions">
    {if !$SEARCH_MODE_RESULTS}

    {* Modified by Hieu Nguyen on 2020-12-09 to display ListView checkbox only when record is editable *}
    <span class="input">
        {if $RECORD_ACTIONS['edit'] && $LISTVIEW_ENTRY->isEditable()}
            <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
        {/if}
    </span>
    {* End Hieu Nguyen *}

    {/if}
    {if $LISTVIEW_ENTRY->get('starred') eq {vtranslate('LBL_YES')}} {*-- Modified by Kelvin to fix not display followed record in list view*}
        {assign var=STARRED value=true}
    {else}
        {assign var=STARRED value=false}
    {/if}
    {if $QUICK_PREVIEW_ENABLED eq 'true'}
		<span>
			<a class="quickView far fa-eye icon action" data-app="{$SELECTED_MENU_CATEGORY}" title="{vtranslate('LBL_QUICK_VIEW', $MODULE)}"></a>
		</span>
    {/if}
	{if $MODULE_MODEL->isStarredEnabled()}
		<span>
			<a class="markStar far icon action {if $STARRED} fa-star active {else} fa-star{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
		</span>
	{/if}
    <!-- Added by Kelvin Thang -- Date: 2018-07-27-->
    {if $RECORD_ACTIONS}
        {* Modified by Vu Mai on 2023-02-14 to hide action edit telesales campaign if user not permission *}
        {if $MODULE == 'Campaigns' && $LISTVIEW_ENTRY->get('campaigntype') == 'Telesales'}
            {assign var=IS_EDITABLE value=Campaigns_Telesales_Model::currentUserCanCreateOrRedistribute()}
        {else}
            {assign var=IS_EDITABLE value=$LISTVIEW_ENTRY->isEditable()}
        {/if}

        {if $RECORD_ACTIONS['edit'] && $IS_EDITABLE} {* Modified by Phu Vo on 2019.09.16 to show/hide edit button base on record editable *}
            <span>
                <a class="far fa-pen icon action edit" data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}" name="editlink" title="{vtranslate('LBL_EDIT', $MODULE)}"></a>
            </span>
        {/if}
        {* End Vu Mai *}
	{/if}

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
			{if $RECORD_ACTIONS}
				<!--{if $RECORD_ACTIONS['edit'] && $LISTVIEW_ENTRY->isEditable()} {* Modified by Phu Vo on 2019.09.16 to show/hide edit button base on record deletable *}
					<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" data-url="{$LISTVIEW_ENTRY->getEditViewUrl()}&app={$SELECTED_MENU_CATEGORY}" name="editlink">{vtranslate('LBL_EDIT', $MODULE)}</a></li>
				{/if}-->
				{if $RECORD_ACTIONS['delete'] && $LISTVIEW_ENTRY->isDeletable()} {* Modified by Phu Vo on 2019.09.16 to show/hide delete button base on record deletable *}
					<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
				{/if}
                
                {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row advanced actions *}
                {assign var="CUSTOM_ROW_ADVANCED_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowAdvancedActions.tpl"}

                {if file_exists($CUSTOM_ROW_ADVANCED_ACTIONS)}
                    {include file=$CUSTOM_ROW_ADVANCED_ACTIONS}
                {/if}
                {* End Hieu Nguyen *}
			{/if}
        </ul>
    </span>

    <div class="btn-group inline-save hide">
        <button class="button btn-success btn-small save" type="button" name="save"><i class="far fa-check"></i></button>
        <button class="button btn-danger btn-small cancel" type="button" name="Cancel"><i class="far fa-close"></i></button>
    </div>
</div>
{/strip}
