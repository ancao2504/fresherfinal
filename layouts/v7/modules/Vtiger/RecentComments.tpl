{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{* Modified by Hieu Nguyen on 2021-03-16 to remove duplicate code so that it's a lot easier to maintain *}
{strip}
    <div class="commentContainer recentComments">
        {assign var="PARENT_COMMENTS" value=$COMMENTS}
        {include file='ShowAllComments.tpl'|@vtemplate_path}
        
        {if $PAGING_MODEL->isNextPageExists()}
            <div class="row">
                <div class="textAlignCenter">
                    <a href="javascript:void(0)" class="moreRecentComments">{vtranslate('LBL_SHOW_MORE', $MODULE_NAME)}</a>
                </div>
            </div>
        {/if}
    </div>
{/strip}