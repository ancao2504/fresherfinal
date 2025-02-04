{* 
    ActivityTypeField.tpl
    Author: Phu Vo
    Date: 2019.11.04
    Purpose: Handle custom logic related to activitytype field
*}

{strip}
    {* Handle Checkin panel show/hide behavior base on actitity type *}
    {if $RECORD->get("activitytype") != "Meeting"}
        <style>
            [data-block="LBL_CHECKIN"] {
                display: none;
            }
        </style>
    {/if}

    <span class="value" data-field-type="picklist">
        <span>
            {$RECORD->getDisplayValue("activitytype")}
        </span>
    </span>
{/strip}