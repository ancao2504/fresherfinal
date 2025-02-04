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
    
    <span class="restoreRecordButton">
        <i data-toggle="tooltip" title="{vtranslate('LBL_RESTORE', $MODULE)}" class="far fa-refresh alignMiddle"></i>
    </span>

    {* Commented out Delete button by Hieu Nguyen on 2020-11-19 to prevent user to empty recycle bin *}
    {* <span class="deleteRecordButton">
        <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="far fa-trash-alt alignMiddle"></i>
    </span> *}

    {* Added by Hieu Nguyen on 2019-11-05 to load module custom listview row actions *}
    {assign var="CUSTOM_ROW_ACTIONS" value="modules/$MODULE/tpls/ListViewCustomRowActions.tpl"}

    {if file_exists($CUSTOM_ROW_ACTIONS)}
        {include file=$CUSTOM_ROW_ACTIONS}
    {/if}
    {* End Hieu Nguyen *}
</div>
{/strip}