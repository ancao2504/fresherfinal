{*
    StatusDetailView.tpl
    Author: Phuc Lu
    Date: 2019.10.31
*}

{strip}
    {if $RECORD->get('cppayment_status') == 'not_completed' || $RECORD->get('cppayment_status') == ''}
        <span class="span-status not_completed">{vtranslate('not_completed', 'CPPayment')}</span>
    {else}
        <span class="span-status {$RECORD->get('cppayment_status')}">{vtranslate($RECORD->get('cppayment_status'), 'CPPayment')}</span>
    {/if}
{/strip}