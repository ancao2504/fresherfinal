
{*
    SalesOrderByCustomerTypeReportChart.tpl
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
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>

            {assign var="WIDTH" value=100 / ($REPORT_DATA|count + 2)}

            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            <th width="{$WIDTH}%"></th>
                            {foreach from=$REPORT_DATA item=COLUMN key=KEY}
                                {if $KEY != 'total'}
                                    <th width="{$WIDTH}%">{vtranslate($DISPLAYED_BY_LABEL, 'Reports')} {if !isset($PARAMS['displayed_by']) || $PARAMS['displayed_by'] != 'quarter'}{$KEY}{else}{$QUARTER[$KEY]}{/if}</th>
                                {/if}
                            {/foreach}
                            <th width="{$WIDTH}%">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><strong>{vtranslate('LBL_REPORT_SALES', 'Reports')}</strong></td>
                            {foreach from=$REPORT_DATA item=COLUMN key=KEY}
                                {if $KEY != 'total'} 
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($COLUMN.sales)}
                                    </td>
                                {/if}
                            {/foreach}
                            <td class="text-right">
                                <strong>{CurrencyField::convertToUserFormat($REPORT_DATA['total'].sales)}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-center"><strong>{vtranslate('LBL_REPORT_REVENUE', 'Reports')}</strong></td>
                            {foreach from=$REPORT_DATA item=COLUMN key=KEY}
                                {if $KEY != 'total'} 
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($COLUMN.revenue)}
                                    </td>
                                {/if}
                            {/foreach}                            
                            <td class="text-right">
                                <strong>{CurrencyField::convertToUserFormat($REPORT_DATA['total'].revenue)}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesAndRevenueByEmployeeReportDetail.js")}"></script>
{/strip}