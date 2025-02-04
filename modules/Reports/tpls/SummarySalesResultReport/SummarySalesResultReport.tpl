
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
                        <tr>
                            <th rowspan="2">{vtranslate('LBL_REPORT_NO', 'Reports')}</th>
                            <th rowspan="2" width="175px">{vtranslate('LBL_REPORT_EMPLOYEE', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_LEAD', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_POTENTIAL', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_QUOTE', 'Reports')}</th>
                            <th colspan="4">{vtranslate('LBL_REPORT_SALES_ORDER', 'Reports')}</th>
                            <th rowspan="2" width="175px">{vtranslate('LBL_REPORT_SALES', 'Reports')}</th>
                            <th rowspan="2" width="175px">{vtranslate('LBL_REPORT_REVENUE', 'Reports')}</th>
                        </tr>
                        <tr>
                            <th width="75px">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_CONVERTED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_TAKING_CARE', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_PENDING', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_WON', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_TAKING_CARE', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_LOST', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_CONFIRMED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_CANCELLED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_TOTAL', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_CONFIRMED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_NOT_CONFIRMED', 'Reports')}</th>
                            <th width="75px">{vtranslate('LBL_REPORT_CANCELLED', 'Reports')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.id != 'all'}
                                    <td class="text-center">{$NO + 1}</td>
                                {else}
                                    <td class="text-center" colspan="2">{$ROW.user_full_name}</td>
                                {/if}
                                
                                {if $ROW.id != 'all'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>
                                {/if}

                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_total_link}'>{formatNumberToUser($ROW.lead_total)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.lead_total)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_converted_link}'>{formatNumberToUser($ROW.lead_converted)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.lead_converted)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_taking_care_link}'>{formatNumberToUser($ROW.lead_taking_care)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.lead_taking_care)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.lead_pending_link}'>{formatNumberToUser($ROW.lead_pending)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.lead_pending)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_total_link}'>{formatNumberToUser($ROW.potential_total)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.potential_total)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_won_link}'>{formatNumberToUser($ROW.potential_won)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.potential_won)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_taking_care_link}'>{formatNumberToUser($ROW.potential_taking_care)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.potential_taking_care)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_lost_link}'>{formatNumberToUser($ROW.potential_lost)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.potential_lost)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_total_link}'>{formatNumberToUser($ROW.quote_total)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.quote_total)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_confirmed_link}'>{formatNumberToUser($ROW.quote_confirmed)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.quote_confirmed)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_not_confirmed_link}'>{formatNumberToUser($ROW.quote_not_confirmed)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.quote_not_confirmed)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_cancelled_link}'>{formatNumberToUser($ROW.quote_cancelled)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.quote_cancelled)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_total_link}'>{formatNumberToUser($ROW.salesorder_total)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.salesorder_total)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_confirmed_link}'>{formatNumberToUser($ROW.salesorder_confirmed)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.salesorder_confirmed)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_not_confirmed_link}'>{formatNumberToUser($ROW.salesorder_not_confirmed)}</a>
                                   {else}
                                        {formatNumberToUser($ROW.salesorder_not_confirmed)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {if $ROW.id != 'all'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.salesorder_cancelled_link}'>{formatNumberToUser($ROW.salesorder_cancelled)}</a>
                                    {else}
                                        {formatNumberToUser($ROW.salesorder_cancelled)}
                                    {/if}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.revenue)}
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="20">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SummarySalesResultReportDetail.js")}"></script>
{/strip}