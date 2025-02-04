{**
    Author: Phu Vo
    Description: Template for custom Event Call Purpose field
*}
{strip}
    {if $RECORD->get('events_call_direction') != 'Outbound'}
        <style>
            .events_call_purpose {
                display: none;
            }
        </style>
    {/if}
    {vtranslate($RECORD->get('events_call_purpose'), 'Events')}
{/strip}