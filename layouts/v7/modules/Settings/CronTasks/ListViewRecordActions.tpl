{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
    <div class="table-actions">
        <span class=cursorDrag>
            <i class="far fa-grip-lines alignTop" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"></i>
        </span>
        <span>
            {foreach item=RECORD_LINK from=$LISTVIEW_ENTRY->getRecordLinks()}
                {assign var="RECORD_LINK_URL" value=$RECORD_LINK->getUrl()}
                <a {if stripos($RECORD_LINK_URL, 'javascript:')===0} onclick="{$RECORD_LINK_URL|substr:strlen("javascript:")};if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}" {else} href='{$RECORD_LINK_URL}' {/if}>
                    <i class="far fa-pen" title="{vtranslate($RECORD_LINK->getLabel(), $QUALIFIED_MODULE)}"></i>
                </a>
                {* Added by Hieu Nguyen on 2020-06-25 *}
                &nbsp;&nbsp;
                <a class="btnResetService" href="javascript:void(0)" data-record-id="{$LISTVIEW_ENTRY->getId()}">
                    <i class="far fa-refresh" title="{vtranslate('LBL_BTN_RESET_SERVICE_TITLE', $QUALIFIED_MODULE)}"></i>
                </a>
                &nbsp;&nbsp;
                <a class="btnTestService" href="javascript:void(0)" data-record-id="{$LISTVIEW_ENTRY->getId()}">
                    <i class="far fa-play" title="{vtranslate('LBL_BTN_TEST_SERVICE_TITLE', $QUALIFIED_MODULE)}"></i>
                </a>
                {* End Hieu Nguyen *}
                {if !$RECORD_LINK@lastui-sortable}
                    &nbsp;&nbsp;
                {/if}
            {/foreach}
        </span>
    </div>
{/strip}        