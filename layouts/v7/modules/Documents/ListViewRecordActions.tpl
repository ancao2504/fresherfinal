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
    <span class="input" >
        <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox"/>
    </span>
    {/if}
    {if $LISTVIEW_ENTRY->get('starred') eq {vtranslate('LBL_YES')}} {*Modified by Hoang Duc to fix not display followed record in list view*}
        {assign var=STARRED value=true}
    {else}
        {assign var=STARRED value=false}
    {/if}
	{if $MODULE_MODEL->isStarredEnabled()}
		<span>
			<a class="markStar far icon action {if $STARRED} fa-star active {else} fa-star{/if}" title="{if $STARRED} {vtranslate('LBL_STARRED', $MODULE)} {else} {vtranslate('LBL_NOT_STARRED', $MODULE)}{/if}"></a>
		</span>
	{/if}

    {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row actions *}
    {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowActions.tpl"}

    {if file_exists($CUSTOM_ROW_ACTIONS)}
        {include file=$CUSTOM_ROW_ACTIONS}
    {/if}
    {* End Hieu Nguyen *}
    
    <span class="more dropdown action">
        <span class="dropdown-toggle" data-toggle="dropdown"><i class="far fa-ellipsis-v-alt icon"></i></span>
        <ul class="dropdown-menu">
            <li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getFullDetailViewUrl()}">{vtranslate('LBL_DETAILS', $MODULE)}</a></li>
            {if $RECORD_ACTIONS}
				{if $RECORD_ACTIONS['edit'] && $LISTVIEW_ENTRY->isEditable()} {* Modified by Phu Vo on 2019.09.16 to show/hide edit button base on record editable *}
					<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="{$LISTVIEW_ENTRY->getEditViewUrl()}" name="editlink">{vtranslate('LBL_EDIT', $MODULE)}</a></li>
				{/if}
				{if $RECORD_ACTIONS['delete'] && $LISTVIEW_ENTRY->isDeletable()} {* Modified by Phu Vo on 2019.09.16 to show/hide delete button base on record deletable *}
					<li><a data-id="{$LISTVIEW_ENTRY->getId()}" href="javascript:void(0);" class="deleteRecordButton">{vtranslate('LBL_DELETE', $MODULE)}</a></li>
				{/if}
			{/if}
            {assign var=RECORD_ID value=$LISTVIEW_ENTRY->getId()}
            {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                <li><a data-id="{$LISTVIEW_ENTRY->getId()}" name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)">File Preview</a></li>
            {/if}
            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                <li><a data-id="{$LISTVIEW_ENTRY->getId()}" name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}">Download</a></li>
            {/if}

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
