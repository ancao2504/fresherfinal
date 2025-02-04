{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}

{strip}
	<div id="listview-actions" class="listview-actions-container">
		<div class = "row">
			<div class="btn-group col-md-4" role="group" aria-label="...">
				<span class="recordDependentListActions" style="float: left;">
					{assign var=LISTVIEW_ACTIONS value=array_reverse($LISTVIEW_MASSACTIONS)}
					{foreach item="LISTVIEW_MASSACTION" from=$LISTVIEW_ACTIONS}
						<button type="button" class="btn btn-default" id="{$MODULE}_listView_massAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_MASSACTION->getLabel())}"
								{if stripos($LISTVIEW_MASSACTION->getUrl(), 'javascript:')===0}onclick='{$LISTVIEW_MASSACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick="Vtiger_List_Js.triggerMassAction('{$LISTVIEW_MASSACTION->getUrl()}')"{/if} disabled="disabled"
								data-toggle="tooltip" title="{if $LISTVIEW_MASSACTION->getLabel() eq 'LBL_RESTORE'}{vtranslate('LBL_RESTORE', $MODULE)}{else}{vtranslate('LBL_DELETE', $MODULE)}{/if}">
							<i class="{if $LISTVIEW_MASSACTION->getLabel() eq 'LBL_RESTORE'} far fa-refresh {else} far fa-trash-alt {/if}"></i>
						</button>
					{/foreach}
				</span>

				{* Fix for empty Recycle bin Button *} 
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS['LISTVIEWBASIC']} 
					<span class="btn-group" style="margin-left: 5px;">
						<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger clearRecycleBin" {if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} 
								onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if} {if !$IS_RECORDS_DELETED} disabled="disabled" {/if}>
							{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}
						</button> 
					</span> 
				{/foreach} 
			</div>
			<div class='col-md-5'>
				{* Modified by Phu Vo on 2019.09.24 to apply display selected rows mechanism *}
				{$replaceParams = ['%module_name' => vtranslate($SOURCE_MODULE, $MODULE)]}
                <div class="current-filter-message" data-toggle="tooltip" title="{vtranslate('LBL_DISPLAYING_RESULTS_ALL', $MODULE, $replaceParams)}">
					{vtranslate('LBL_DISPLAYING_RESULTS_ALL', $MODULE, $replaceParams)}
				</div>

				<div class="hide messageContainer">
					<center><a href="#" id="selectAllMsgDiv" data-message="{vtranslate('LBL_SELECTED_ALL_MESSAGE')}"></a></center>
				</div>
				<div class="hide messageContainer">
					<center><a href="#" id="deSelectAllMsgDiv">{vtranslate('LBL_DESELECT_ALL_LINK_LABEL', $MODULE)}</a></center>
				</div>
				{* End Phu Vo*}
			</div>
			<div class="col-md-3">
				{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
				{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
			</div>
		</div>
	</div>
{/strip}