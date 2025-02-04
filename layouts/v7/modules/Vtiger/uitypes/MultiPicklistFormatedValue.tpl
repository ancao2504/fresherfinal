{*
    Name: MultiPicklistFormatedValue.tpl
    Author: Phu Vo
    Date: 2020.06.08
    Description: Use to process picklist fields raw HTML structure
*}

{strip}
    {assign var=PICKLIST_VALUES value=array_values(array_filter(explode(' |##| ', $RAW_VALUES_STRING)))}

    {foreach item=VALUE key=INDEX from=$PICKLIST_VALUES}
        {assign var=PICKLIST_COLOR value=Settings_Picklist_Module_Model::getPicklistColorByValue($FIELD_MODEL->getName(), trim($VALUE))}
        <span class="picklist-color" {if !empty($PICKLIST_COLOR)} style="background-color: {$PICKLIST_COLOR}; color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {/if}>{vtranslate(trim($VALUE), $MODULE)}</span>
    {/foreach}
{/strip}