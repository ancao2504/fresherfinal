
{*
    TopCustomerBySalesReport.tpl
    Author: Phuc Lu
    Date: 2020.04.14
*}

{strip}
    <div id="custom-report-detail">
        <div id="filter">
            {$REPORT_FILTER}
        </div>
        
        {assign var="QUARTER" value=[1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']}

        <div id="chart">
            {include file="modules/Reports/tpls/CustomReportAddChartToDashboard.tpl"}
            <div style="clear: both;"></div>
            {$CHART}
        </div>
        
        <div id="result">
            <div id="result-summary">
                {vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')}: <span>{formatNumberToUser($REPORT_DATA[0]['all'])}</span>
                <br>                
                {vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')}: <span>{CurrencyField::convertToUserFormat($REPORT_DATA[1]['all'])}</span>
            </div>
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>
            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            <th style="min-width:50px"></th>
                            {foreach from=$REPORT_DATA[0] key=KEY item=ITEM name=INDEX}
                                {if $KEY != 'name' && $KEY != 'all'}
                                    <th style="min-width:125px">{vtranslate('LBL_REPORT_'|cat:$PARAMS.displayed_by|upper, 'Reports')} {if $PARAMS.displayed_by == 'quarter'}{$QUARTER[$KEY]}{else}{$KEY}{/if}</th>
                                {/if}
                            {/foreach}
                            <th style="min-width:125px">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO}
                            <tr>
                                <td class="text-center">{$ROW['name']}</td>
                                {foreach from=$ROW item=VALUE key=KEY name=INDEX}
                                    {if !$smarty.foreach.INDEX.first}
                                        <td class="text-right" {if $smarty.foreach.INDEX.last}style="font-weight: bold;"{/if}>
                                            {if $NO == 0}
                                                {formatNumberToUser($VALUE)}
                                            {else}
                                                {CurrencyField::convertToUserFormat($VALUE)}
                                            {/if}
                                        </td>
                                    {/if}
                                {/foreach}
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="6">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/PredictionSalesReportDetail.js")}"></script>
{/strip}