{*
    TopEmployeesByPotentialSalesReport.tpl
    Author: Phuc Lu
    Date: 2020.08.11
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
            <div id="result-summary" class="{if $SUMMARY_DATA == false}hide{/if}">
                <div>{vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')}: <span>{$SUMMARY_DATA.number}</span></div>
                <div>{vtranslate('LBL_REPORT_TOTAL_SALES', 'Reports')}: <span>{$SUMMARY_DATA.amount}</span></div>
            </div>
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

                                {if $TARGET_MODULE == 'POTENTIAL'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.potential_number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'CONVERSION_LEAD'}    
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>                            
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>                                    
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.converted_lead_number)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.conversion_rate)}%
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'POTENTIAL_SALES_STAGE'}
                                    <td class="text-left">
                                        {$ROW.sales_stage}
                                    </td>                             
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.potential_number)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>
                                {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/TopEmployeesByPotentialSalesReportDetail.js")}"></script>
{/strip}