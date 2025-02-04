
{*
    SummarySalesByMarketReport.tpl
    Author: Phuc Lu
    Date: 2020.04.14
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
                        {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER}
                            <th width="{$WIDTH}">{$HEADER}</th>
                        {/foreach}
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO}
                            <tr>
                                {if $TARGET_REPORT == 'SUMMARY_SALES_BY_MARKET'}
                                    <td class="text-center">{$ROW.no}</td>
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW.sales)}</td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW.ratio, 'float')}%</td>
                                {/if}

                                {if $TARGET_REPORT == 'ANALYZE_ACCOUNT_BY_COMPANY_SIZE'}
                                    <td class="text-center">{$ROW.no}</td>
                                    <td class="text-left">{$ROW.accounts_company_size}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.account_number)}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.ratio, 'float')}%</td>
                                {/if}
                                
                                {if $TARGET_REPORT == 'ANALYZE_CALL_BY_PURPOSE'}
                                    <td class="text-center">{$ROW.no}</td>
                                    <td class="text-left">{$ROW.events_call_purpose}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.call_number)}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.ratio, 'float')}%</td>
                                {/if}

                                {if $TARGET_REPORT == 'ANALYZE_CALL_BY_RESULT'}
                                    <td class="text-center">{$ROW.no}</td>
                                    <td class="text-left">{$ROW.events_call_result}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.call_number)}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.ratio, 'float')}%</td>
                                {/if}

                                {if $TARGET_REPORT == 'ANALYZE_POTENTIAL_BY_TYPE'}
                                    <td class="text-center">{$ROW.no}</td>
                                    <td class="text-left">{$ROW.potentialtype}</td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW.potential_amount)}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.ratio, 'float')}%</td>
                                {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SummarySalesByMarketReportDetail.js")}"></script>
{/strip}