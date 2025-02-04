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
    {assign var=LISTVIEW_MASSACTIONS_1 value=array()}
    <div id="listview-actions" class="listview-actions-container">
        {foreach item=LIST_MASSACTION from=$LISTVIEW_MASSACTIONS name=massActions}
            {if $LIST_MASSACTION->getLabel() eq 'LBL_EDIT'}
                {assign var=editAction value=$LIST_MASSACTION}
            {else if $LIST_MASSACTION->getLabel() eq 'LBL_DELETE'}
                {assign var=deleteAction value=$LIST_MASSACTION}
            {else if $LIST_MASSACTION->getLabel() eq 'LBL_ADD_COMMENT'}
                {assign var=commentAction value=$LIST_MASSACTION}
            {else}
                {$a = array_push($LISTVIEW_MASSACTIONS_1, $LIST_MASSACTION)}
                {* $a is added as its print the index of the array, need to find a way around it *}
            {/if}
        {/foreach}
        <div class = "row">
            <div class="col-md-2">
            <div class="btn-group listViewActionsContainer" role="group" aria-label="...">
                {if $deleteAction}
                    <button type="button" class="btn btn-default" id="{$MODULE}_listView_massAction_LBL_MOVE_REPORT" 
                            onclick='Reports_List_Js.massMove("index.php?module=Reports&view=MoveReports")' title="{vtranslate('LBL_MOVE_REPORT', $MODULE)}" disabled="disabled">
                        <i class="vicon-foldermove" style='font-size:13px;'></i>
                    </button>
                {/if}
                {if $deleteAction}
                    <button type="button" class="btn btn-default" id={$MODULE}_listView_massAction_{$deleteAction->getLabel()} 
                            {if stripos($deleteAction->getUrl(), 'javascript:')===0} href="javascript:void(0);" onclick='{$deleteAction->getUrl()|substr:strlen("javascript:")}'{else} href='{$deleteAction->getUrl()}' {/if} title="{vtranslate('LBL_DELETE', $MODULE)}" disabled="disabled">
                        <i class="far fa-trash-alt"></i>
                    </button>
                {/if}

            </div>
        </div>
            <div class='col-md-7'>
                {* Modified by Phu Vo on 2019.09.24 to apply display selected rows mechanism *}             
                {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getDefaultUrl()}
                {assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}

                {if $DEFAULT_FILTER_ID}
                    {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:"&viewname="|cat:$DEFAULT_FILTER_ID}
                {/if}
                
                {if $VIEWNAME eq 'All'}
                    {$replaceParams = ['%module_name' => vtranslate($MODULE, $MODULE)]}
                    <div class="current-filter-message" data-toggle="tooltip" title="{vtranslate('LBL_DISPLAYING_RESULTS_ALL', $MODULE, $replaceParams)}">
                        {vtranslate('LBL_DISPLAYING_RESULTS_ALL', $MODULE, $replaceParams)}
                    </div>
                {else if $VIEWNAME eq 'shared'}
                    {$replaceParams = ['%module_name' => vtranslate($MODULE, $MODULE), '%filter_name' => vtranslate('LBL_SHARED_REPORTS', $MODULE)]}
                    <div class="current-filter-message" data-toggle="tooltip" title="{vtranslate('LBL_DISPLAYING_RESULTS_FILTER', $MODULE, $replaceParams)}">
                        {vtranslate('LBL_DISPLAYING_RESULTS_FILTER', $MODULE, $replaceParams)}
                    </div>
                {else}
                    {$replaceParams = ['%module_name' => vtranslate($MODULE, $MODULE), '%filter_name' => vtranslate($FOLDERS[$VIEWNAME]->getName(), $MODULE)]}
                    <div class="current-filter-message" data-toggle="tooltip" title="{vtranslate('LBL_DISPLAYING_RESULTS_FILTER', $MODULE, $replaceParams)}">
                        {vtranslate('LBL_DISPLAYING_RESULTS_FILTER', $MODULE, $replaceParams)}
                    </div>
                {/if}
                    
                <div class="hide messageContainer">
                    <center><a href="#" id="selectAllMsgDiv" data-message="{vtranslate('LBL_SELECTED_ALL_MESSAGE')}"></a></center>
                </div>
                <div class="hide messageContainer">
                    <center><a href="#" id="deSelectAllMsgDiv">{vtranslate('LBL_DESELECT_ALL_LINK_LABEL',$MODULE)}</a></center>
                </div>  
                {* End Phu Vo*}       
            </div>
            <div class="col-md-3">
                {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true }
            </div>
        </div>	
{/strip}