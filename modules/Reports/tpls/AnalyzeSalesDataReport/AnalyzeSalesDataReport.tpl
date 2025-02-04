
{*
    AnalyzeSalesDataReport.tpl
    Author: Phuc Lu
    Date: 2020.06.03
*}

{strip}
    <div id="custom-report-detail">
        {* Modified by Hieu Nguyen on 2020-09-07 to seperate report filter into another template file *}
        <div id="filter">
            {$REPORT_FILTER}
        </div>
        {* End Hieu Nguyen *}

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
                                <td class="text-center">{$NO + 1}</td>

                                <td class="text-left">
                                    {$ROW.label}
                                </td>

                                <td class="text-right">
                                     {formatNumberToUser($ROW.lead_number)}
                                </td>
                                
                                <td class="text-right">
                                     {formatNumberToUser($ROW.potential_number)}
                                </td>
                                
                                <td class="text-right">
                                     {formatNumberToUser($ROW.quote_number)}
                                </td>
                                
                                <td class="text-right">
                                     {formatNumberToUser($ROW.sales_order_number)}
                                </td>

                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>                                
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/AnalyzeSalesDataReportDetail.js")}"></script>
{/strip}