{*
CustomReport template
Author: Hieu Nguyen
Date: 2018-12-09
Purpose: provide default template for custom reports
Note: if this template does not fit with your requirement, just clone this into your own file. DON'T EDIT THIS FILE!
*}

{strip}
<div {if $PRINT}style="width:80%; margin:auto" {/if}>
    <h2>{$REPORT_NAME}</h2>

    <table cellpadding="5" cellpadding="0"
        class="{if !$PRINT}table table-bordered{else}printReport reportPrintData{/if}">
        <thead>
            <tr class="blockHeader">
                {foreach item=HEADER_NAME from=$REPORT_HEADERS}
                <th {if !$PRINT}nowrap{/if}>{$HEADER_NAME}</th>
                {/foreach}
                <!-- <th {if !$PRINT}nowrap{/if}>{vtranslate('LBL_AGE', $PRIMARY_MODULE)}</th> -->
                {if !$PRINT}<th nowrap>{vtranslate('LBL_ACTION')}</th>{/if}
            </tr>
        </thead>
        <tbody>
            {$REPORT_RESULT}
        </tbody>
    </table>
</div>
{/strip}