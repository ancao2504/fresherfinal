{*
    CustomReport row template
    Author: Hieu Nguyen
    Date: 2018-12-09
    Purpose: provide default template for custom reports
    Note: if this template does not fit with your requirement, just clone this into your own file. DON'T EDIT THIS FILE!
*}

{strip}
    <tr>
        {foreach key=KEY item=VALUE from=$ROW_DATA}
            {if strpos($KEY, 'lbl_action') === false}
                <td {if !$PRINT}nowrap{/if}>{$VALUE}</td>
            {else if !$PRINT}
                <td><a target="_BLANK" href="index.php?module=Contacts&view=Detail&record={$VALUE}">{vtranslate('LBL_VIEW_DETAILS')}</a></td>
            {/if}
        {/foreach}
    </tr>
{/strip}