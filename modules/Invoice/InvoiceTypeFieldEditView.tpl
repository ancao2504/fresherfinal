{* Added by Hieu Nguyen on 2020-12-07 *}

{strip}
    {if $RECORD->get('id') eq ''}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $MODULE)}
    {else}
        <span class="numberCircle" style="padding: 1px 7px;font-size: 12px;">{vtranslate($RECORD->get('invoice_type'), $MODULE_NAME)}</span>
        <input type="hidden" name="invoice_type" value="{$RECORD->get('invoice_type')}" />
    {/if}
{/strip}