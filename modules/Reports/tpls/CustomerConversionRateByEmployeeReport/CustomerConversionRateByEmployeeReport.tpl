
{*
    CustomerConversionRateByEmployeeReport.tpl
    Author: Phuc Lu
    Date: 2020.05.12
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
                            {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}" style="min-width: 100px">{$HEADER}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
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
                                        {if $REPORT_OBJECT == 'EMPLOYEE'}
                                            <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.name}</a>
                                        {else}
                                            {$ROW.name}
                                        {/if}
                                    </td>
                                {/if}

                                <td class="text-right">
                                    {$ROW.lead}
                                </td>

                                <td class="text-right">
                                    {$ROW.converted_lead}
                                </td>

                                {if $ROW.id != 'all'}
                                    <td class="text-right">
                                        <span>{$ROW.lead_to_converted}%</span><span class="spn-compare {if $ROW.cp_lead_to_converted > 0}spn-positive far fa-arrow-up{else}{if $ROW.cp_lead_to_converted < 0}spn-negative far fa-arrow-down{/if}{/if}">{if $ROW.cp_lead_to_converted != 0}{$ROW.cp_lead_to_converted|replace:'-':''}%{else}&#x268A;{/if}</span>
                                    </td>
                                {else}
                                    <td></td>
                                {/if}

                                <td class="text-right">
                                    {$ROW.potential}
                                </td>

                                {if $ROW.id != 'all'}
                                    <td class="text-right">
                                        <span>{$ROW.lead_to_potential}%</span><span class="spn-compare {if $ROW.cp_lead_to_potential > 0}spn-positive far fa-arrow-up{else}{if $ROW.cp_lead_to_potential < 0}spn-negative far fa-arrow-down{/if}{/if}">{if $ROW.cp_lead_to_potential != 0}{$ROW.cp_lead_to_potential|replace:'-':''}%{else}&#x268A;{/if}</span>
                                    </td>
                                {else}
                                    <td></td>
                                {/if}
                                
                                <td class="text-right">
                                    {$ROW.quote}
                                </td>
                                
                                {if $ROW.id != 'all'}
                                    <td class="text-right">
                                        <span>{$ROW.lead_to_quote}%</span><span class="spn-compare {if $ROW.cp_lead_to_quote > 0}spn-positive far fa-arrow-up{else}{if $ROW.cp_lead_to_quote < 0}spn-negative far fa-arrow-down{/if}{/if}">{if $ROW.cp_lead_to_quote != 0}{$ROW.cp_lead_to_quote|replace:'-':''}%{else}&#x268A;{/if}</span>
                                    </td>
                                {else}
                                    <td></td>
                                {/if}

                                <td class="text-right">
                                    {$ROW.closed_won_potential}
                                </td>

                                {if $ROW.id != 'all'}
                                    <td class="text-right">
                                        <span>{$ROW.lead_to_closed_won_potential}%</span><span class="spn-compare {if $ROW.cp_lead_to_closed_won_potential > 0}spn-positive far fa-arrow-up{else}{if $ROW.cp_lead_to_closed_won_potential < 0}spn-negative far fa-arrow-down{/if}{/if}">{if $ROW.cp_lead_to_closed_won_potential != 0}{$ROW.cp_lead_to_closed_won_potential|replace:'-':''}%{else}&#x268A;{/if}</span>
                                    </td>
                                {else}
                                    <td></td>
                                {/if}

                                <td class="text-right">
                                    {formatNumberToUser($ROW.avg_deal_days)}
                                </td>

                                <td class="text-right">
                                    {$ROW.salesorder}
                                </td>
                                {if $ROW.id != 'all'}
                                    <td class="text-right">
                                        <span>{$ROW.lead_to_salesorder}%</span><span class="spn-compare {if $ROW.cp_lead_to_salesorder > 0}spn-positive far fa-arrow-up{else}{if $ROW.cp_lead_to_salesorder < 0}spn-negative far fa-arrow-down{/if}{/if}">{if $ROW.cp_lead_to_salesorder != 0}{$ROW.cp_lead_to_salesorder|replace:'-':''}%{else}&#x268A;{/if}</span>
                                    </td>
                                {else}
                                    <td></td>
                                {/if}

                                <td class="text-right">
                                     {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>
                                
                                <td class="text-right">
                                     {CurrencyField::convertToUserFormat($ROW.revenue)}
                                </td>
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomerConversionRateByEmployeeReportDetail.js")}"></script>
{/strip}