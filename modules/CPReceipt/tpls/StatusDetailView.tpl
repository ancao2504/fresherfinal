{*
    StatusDetailView.tpl
    Author: Phuc Lu
    Date: 2019.10.31
*}

{strip}
    {if $RECORD->get('cpreceipt_status') == 'not_completed' || $RECORD->get('cpreceipt_status') == ''}
        <span class="span-status not_completed">{vtranslate('not_completed', 'CPReceipt')}</span>
    {else}
        <span class="span-status {$RECORD->get('cpreceipt_status')}">{vtranslate($RECORD->get('cpreceipt_status'), 'CPReceipt')}</span>
    {/if}
{/strip}