{**
    Author: Phu Vo
    Description: Template for custom Event Call Purpose field
*}
{strip}
    {if $RECORD->get('events_call_direction') != 'Inbound'}
        <style>
            .events_inbound_call_purpose {
                display: none;
            }
        </style>
    {/if}
    {vtranslate($RECORD->get('events_inbound_call_purpose'), 'Events')}
{/strip}