{strip}
    {if $RECORD->get('events_call_purpose') != 'call_purpose_other'}
        <style>
            .events_call_purpose_other {
                display: none;
            }
        </style>
    {/if}
    {$RECORD->get('events_call_purpose_other')}
{/strip}