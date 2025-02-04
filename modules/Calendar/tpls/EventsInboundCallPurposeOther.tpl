{strip}
    {if $RECORD->get('events_inbound_call_purpose') != 'inbound_call_purpose_other'}
        <style>
            .events_inbound_call_purpose_other {
                display: none;
            }
        </style>
    {/if}
    {$RECORD->get('events_inbound_call_purpose_other')}
{/strip}