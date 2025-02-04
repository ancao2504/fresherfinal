
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
                       <thead>
                        <tr>
                            <th rowspan="2">{vtranslate('LBL_REPORT_NO', 'Reports')}</th>
                            <th rowspan="2" style="min-width: 300px;">{vtranslate('LBL_REPORT_CAMPAIGN', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_LEAD', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_POTENTIAL', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_QUOTE', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_SALES_ORDER', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_SALES', 'Reports')}</th>
                            <th rowspan="2" style="min-width: 125px;">{vtranslate('LBL_REPORT_REVENUE', 'Reports')}</th>
                            <th rowspan="2" style="min-width: 125px;">{vtranslate('LBL_REPORT_COST', 'Reports')}</th>
                        </tr>
                        <tr>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_CONVERTED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TAKING_CARE', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_PENDING', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_WON', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TAKING_CARE', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_LOST', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_CONFIRMED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_CANCELLED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_CONFIRMED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports')}</th>
                            <th style="min-width: 50px;">{vtranslate('LBL_REPORT_CANCELLED', 'Reports')}</th>
                            <th style="min-width: 125px;">{vtranslate('LBL_REPORT_POTENTIAL_SALES', 'Reports')}</th>
                            <th style="min-width: 125px;">{vtranslate('LBL_REPORT_PREDICTED_POTENTIAL_SALES', 'Reports')}<span class="info-container" title="{vtranslate('LBL_REPORT_PREDICTED_POTENTIAL_SALES_FORMULA', 'Reports')}{$PREDICTED_PERCENTAGE}%"><i class="far fa-info"></i></span></th>
                            <th style="min-width: 125px;">{vtranslate('LBL_REPORT_QUOTE_SALES', 'Reports')}</th>
                            <th style="min-width: 125px;">{vtranslate('LBL_REPORT_SALES_ORDER_SALES', 'Reports')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.campaign_id != 'all'}
                                    <td class="text-center">{$NO + 1}</td>
                                {else}
                                    <td class="text-center" colspan="2">{$ROW.campaign_name}</td>
                                {/if}
                                
                                {if $ROW.campaign_id != 'all'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaign_id}')">{$ROW.campaign_name}</a>
                                    </td>
                                {/if}

                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_total_link}'>{$ROW.lead_total}</a>
                                    {else}
                                        {$ROW.lead_total}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_converted_link}'>{$ROW.lead_converted}</a>
                                    {else}
                                        {$ROW.lead_converted}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_taking_care_link}'>{$ROW.lead_taking_care}</a>
                                    {else}
                                        {$ROW.lead_taking_care}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_pending_link}'>{$ROW.lead_pending}</a>
                                    {else}
                                        {$ROW.lead_pending}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_total_link}'>{$ROW.potential_total}</a>
                                    {else}
                                        {$ROW.potential_total}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_won_link}'>{$ROW.potential_won}</a>
                                    {else}
                                        {$ROW.potential_won}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_taking_care_link}'>{$ROW.potential_taking_care}</a>
                                    {else}
                                        {$ROW.potential_taking_care}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_lost_link}'>{$ROW.potential_lost}</a>
                                    {else}
                                        {$ROW.potential_lost}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_total_link}'>{$ROW.quote_total}</a>
                                    {else}
                                        {$ROW.quote_total}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_confirmed_link}'>{$ROW.quote_confirmed}</a>
                                    {else}
                                        {$ROW.quote_confirmed}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_not_confirmed_link}'>{$ROW.quote_not_confirmed}</a>
                                    {else}
                                        {$ROW.quote_not_confirmed}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_cancelled_link}'>{$ROW.quote_cancelled}</a>
                                    {else}
                                        {$ROW.quote_cancelled}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_total_link}'>{$ROW.salesorder_total}</a>
                                    {else}
                                        {$ROW.salesorder_total}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_confirmed_link}'>{$ROW.salesorder_confirmed}</a>
                                    {else}
                                        {$ROW.salesorder_confirmed}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_not_confirmed_link}'>{$ROW.salesorder_not_confirmed}</a>
                                    {else}
                                        {$ROW.salesorder_not_confirmed}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.campaign_id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_cancelled_link}'>{$ROW.salesorder_cancelled}</a>
                                    {else}
                                        {$ROW.salesorder_cancelled}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.predicted_potential_sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.quote_sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.salesorder_sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.revenue)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.cost)}
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="24">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
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