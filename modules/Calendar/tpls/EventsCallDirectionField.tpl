{strip}
    {if $RECORD->get("activitytype") != "Mobile Call" && $RECORD->get("activitytype") != "Call"}
        <style>
            .block_LBL_CALL_INFORMATION {
                display: none;
            }
        </style>
    {/if}
    {vtranslate($RECORD->get("events_call_direction"), 'Events')}
{/strip}