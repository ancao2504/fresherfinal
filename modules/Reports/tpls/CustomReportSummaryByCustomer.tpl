{*
CustomReportSummaryByCustomer
Author: Si Dat
Date: 2024-01-03
Purpose: custom report to show summary by customer
*}

{strip}
<div {if $PRINT}style="width:80%; margin:auto" {/if}>
    <h2>{$REPORT_NAME}</h2>

    {include file="modules/Reports/tpls/DateFilterFormBestSellers.tpl"}

    <table cellpadding="5" cellpadding="0"
        class="{if !$PRINT}table table-bordered{else}printReport reportPrintData{/if}" style="margin-top: 20px;">
        <thead>
            <tr class="blockHeader">
                {foreach item=HEADER_NAME from=$REPORT_HEADERS}
                <th {if !$PRINT}nowrap{/if}>{$HEADER_NAME}</th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {if $REPORT_DATA|@count > 0}
            {assign var="previousColumn2" value=""}
            {foreach from=$REPORT_DATA item=row}
            {assign var="currentColumn2" value=$row[1]}

            {if $previousColumn2 == "" || $previousColumn2 != $currentColumn2}
            <tr>
                <td colspan="100%" style="font-weight: bold; text-align: left;">{$currentColumn2}</td>
            </tr>
            {/if}

            <tr>
                {foreach from=$row item=column key=colIndex}
                <td {if !$PRINT}nowrap{/if}>
                    {$column}
                    {if $colIndex == 1}
                    {assign var="previousColumn2" value=$currentColumn2}
                    {/if}
                </td>
                {/foreach}
            </tr>
            {/foreach}
            {else}
            <tr>
                <td colspan="{count($REPORT_HEADERS)}" style="text-align: center;">{vtranslate('LBL_NOT_HAVE_RECORD',
                    'Reports')}</td>
            </tr>
            {/if}
        </tbody>
    </table>
</div>
{/strip}