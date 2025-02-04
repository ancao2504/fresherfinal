
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
            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            <th rowspan="2">{vtranslate('LBL_REPORT_SOURCE', 'Reports')}</th>
                            {foreach from=$REPORT_DATA item=GROUP_VALUES key=KEY}
                                {if $KEY != 'ext_data'}
                                    <th colspan="3">{vtranslate($DISPLAYED_BY_LABEL, 'Reports')} {if !isset($PARAMS['displayed_by']) || $PARAMS['displayed_by'] == 'month'}{$KEY}{else}{$QUARTER[$KEY]}{/if}</th>
                                {/if}
                            {/foreach}
                            <th rowspan="2">{vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')}</th>
                            <th rowspan="2">{vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')}</th>
                            <th rowspan="2">{vtranslate('LBL_REPORT_TOTAL_REVENUE', 'Reports')}</th>
                        </tr>
                        <tr>
                            {foreach from=$REPORT_DATA item=GROUP_VALUES key=KEY}
                                {if $KEY != 'ext_data'}
                                    <th>{vtranslate('LBL_REPORT_NUMBER', 'Reports')}</th>
                                    <th>{vtranslate('LBL_REPORT_SALES', 'Reports')}</th>
                                    <th>{vtranslate('LBL_REPORT_REVENUE', 'Reports')}</th>
                                {/if}
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA['ext_data']['customer_type'] item=TYPE key=NO}
                            <tr>
                                <td class="text-center"><strong>{vtranslate($TYPE, 'SalesOrder')}</strong></td>
                                {foreach from=$REPORT_DATA item=ROW key=KEY}
                                    {if $KEY != 'ext_data'}
                                        <td class="text-center">
                                            <a target="_blank" onclick="window.open('{$ROW[$TYPE].saleorder_link}')">{$ROW[$TYPE].saleorder_number}</a>
                                        </td>
                                        <td class="text-right">
                                            {CurrencyField::convertToUserFormat($ROW[$TYPE].sales)}
                                        </td>
                                        <td class="text-right">
                                            {CurrencyField::convertToUserFormat($ROW[$TYPE].revenue)}
                                        </td>
                                    {/if}
                                {/foreach}
                                <td class="text-center total-result ">
                                    {$REPORT_DATA['ext_data']['total_row'][$TYPE].saleorder_number}
                                </td>
                                <td class="text-right total-result">
                                    {CurrencyField::convertToUserFormat($REPORT_DATA['ext_data']['total_row'][$TYPE].sales)}
                                </td>
                                <td class="text-right total-result">
                                    {CurrencyField::convertToUserFormat($REPORT_DATA['ext_data']['total_row'][$TYPE].revenue)}
                                </td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td class="text-center text-nowrap"><strong>{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</strong></td>
                            {foreach from=$REPORT_DATA['ext_data']['total_column'] item=ROW key=KEY}
                                <td class="text-center">
                                    {$ROW.saleorder_number}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.revenue)}
                                </td>
                            {/foreach}
                            <td class="text-center total-result">
                                <strong>{$REPORT_DATA['ext_data']['total'].saleorder_number}</strong>
                            </td>
                            <td class="text-right total-result">
                                <strong>{CurrencyField::convertToUserFormat($REPORT_DATA['ext_data']['total'].sales)}</strong>
                            </td>
                            <td class="text-right total-result">
                               <strong>{CurrencyField::convertToUserFormat($REPORT_DATA['ext_data']['total'].revenue)}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesOrderByCustomerTypeReportDetail.js")}"></script>
{/strip}