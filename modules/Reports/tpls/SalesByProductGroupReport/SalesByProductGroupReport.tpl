
{*
    SalesByProductGroupReport.tpl
    Author: Phuc Lu
    Date: 2020.06.25
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
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>

            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}">{$HEADER}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            {if $REPORT_OBJECT == 'PRODUCT' || $REPORT_OBJECT == 'SERVICE'}
                                <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                    {if $ROW.id != 'all'}
                                        <td class="text-center">{$NO + 1}</td>
                                    {else}
                                        <td class="text-center" colspan="2">
                                            {$ROW.name}
                                        </td>
                                    {/if}

                                    {if $ROW.id != 'all'}
                                        <td class="text-left">
                                            {$ROW.name}
                                        </td>
                                    {/if}
                                    
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.sales_number)}
                                    </td>

                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>
                                    
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.quote_sales)}
                                    </td>
                                </tr>
                            {/if}

                            {if $REPORT_OBJECT == 'CAMPAIGN_ROI'}
                                <tr>
                                    <td class="text-center">{$NO + 1}</td>

                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>

                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.actualcost)}
                                    </td>

                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.actual_revenue)}
                                    </td>
                                    
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.actualroi)}
                                    </td>
                                </tr>
                            {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesByProductGroupReportDetail.js")}"></script>
{/strip}