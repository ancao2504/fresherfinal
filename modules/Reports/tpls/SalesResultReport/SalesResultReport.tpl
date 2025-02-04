
{*
    SalesResultReport.tpl
    Author: Phuc Lu
    Date: 2020.04.21
*}

{strip}
    <div id="custom-report-detail">
        <div id="filter">
            {$REPORT_FILTER}
        </div>

        <div id="chart">
            {include file="modules/Reports/tpls/CustomReportAddChartToDashboard.tpl"}
            <div style="clear: both;"></div>
            {$CHART}
        </div>
        
        <div id="result">
            <div id="result-summary">
                {assign var="LAST_ROW" value=$REPORT_DATA|count - 1}
                {vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')}: <span>{CurrencyField::convertToUserFormat($REPORT_DATA[$LAST_ROW]['sales'])}</span>
                <br>                
                {vtranslate('LBL_REPORT_TOTAL_REVENUE', 'Reports')}: <span>{CurrencyField::convertToUserFormat($REPORT_DATA[$LAST_ROW]['revenue'])}</span>
            </div>

            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>

            {assign var="WIDTH" value=100 / ($REPORT_DATA|count + 2)}

            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER}
                                <th width="{$WIDTH}">{$HEADER}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                <td class="text-center">{$ROW.period}</td>
                                <td class="text-right">
                                    {if !$smarty.foreach.INDEX.last}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_number_link}'>{formatNumberToUser($ROW.potential_number)}</a>
                                    {else}
                                        {formatNumberToUser($ROW.potential_number)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if !$smarty.foreach.INDEX.last}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_number_link}'>{formatNumberToUser($ROW.quote_number)}</a>
                                    {else}
                                        {formatNumberToUser($ROW.quote_number)}
                                    {/if}
                                </td>
                                </td> <td class="text-right">
                                    {if !$smarty.foreach.INDEX.last}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.sales_order_number_link}'>{formatNumberToUser($ROW.sales_order_number)}</a>
                                    {else}
                                        {formatNumberToUser($ROW.sales_order_number)}
                                    {/if}
                                </td>
                                <td class="text-right">{CurrencyField::convertToUserFormat($ROW.sales)}</td>
                                <td class="text-right">{CurrencyField::convertToUserFormat($ROW.revenue)}</td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="{$REPORT_HEADERS|count}">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesResultReportDetail.js")}"></script>
{/strip}