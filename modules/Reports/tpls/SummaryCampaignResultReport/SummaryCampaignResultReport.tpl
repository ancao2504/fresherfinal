
{*
    SummarySalesResultReport.tpl
    Author: Phuc Lu
    Date: 2020.04.28
*}

{strip}
    <div id="custom-report-detail">
        <div id="filter">
            {$REPORT_FILTER}
        </div>
        
        <div id="result">
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>
            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}">{$HEADER}{if $smarty.foreach.INDEX.last}<span class="info-container" title="{vtranslate('LBL_REPORT_REVENUE_PER_COST_DESCRIPTION', 'Reports')}"><i class="far fa-info"></i></span>{/if}</th>
                        {/foreach}
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO}
                            {if $NO + 1 == count($REPORT_DATA)}
                                <tr>
                                    <td class="text-left" colspan="2">
                                        {vtranslate('LBL_REPORT_TOTAL', 'Reports')}
                                    </td>
                                    <td class="text-right">
                                        {$ROW.lead_number}
                                    </td>
                                    <td class="text-right">
                                        {$ROW.potential_number}
                                    </td>
                                    <td class="text-right">
                                        {$ROW.quote_number}
                                    </td>                                
                                    <td class="text-right">
                                        {$ROW.salesorder_number}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.revenue)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.cost)}
                                    </td>
                                    <td class="text-right">
                                        {if $ROW.cost != '' || $ROW.cost != '0'}
                                            {round($ROW.revenue/$ROW.cost, 2)}
                                        {/if}
                                    </td>
                                </tr>
                            {else}
                                <tr>
                                    <td class="text-center">{$NO + 1}</td>
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaign_id}')">{$ROW.campaign_name}</a>
                                    </td>
                                    <td class="text-right">
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_link}'>{$ROW.lead_number}</a>
                                    </td>
                                    <td class="text-right">
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_link}'>{$ROW.potential_number}</a>
                                    </td>
                                    <td class="text-right">
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_link}'>{$ROW.quote_number}</a>
                                    </td>                                
                                    <td class="text-right">
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_link}'>{$ROW.salesorder_number}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.revenue)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.cost)}
                                    </td>
                                    <td class="text-right">
                                        {if $ROW.cost != '' || $ROW.cost != '0'}
                                            {round($ROW.revenue/$ROW.cost, 2)}
                                        {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SummaryCampaignResultReportDetail.js")}"></script>
{/strip}