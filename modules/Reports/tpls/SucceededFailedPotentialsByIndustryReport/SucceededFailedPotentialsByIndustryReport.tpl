
{*
    SucceededFailedPotentialsByIndustryReport.tpl
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
            <div id="result-summary">
                {assign var="LAST_ROW" value=$REPORT_DATA|count - 1}
                {vtranslate('LBL_REPORT_TOTAL_NUMBER', 'Reports')}: <span>{formatNumberToUser($REPORT_DATA[$LAST_ROW]['number'])}</span>
                <br>                
                {vtranslate('LBL_REPORT_OPPORTUNITY_TOTAL_VALUE', 'Reports')}: <span>{CurrencyField::convertToUserFormat($REPORT_DATA[$LAST_ROW]['value'])}</span>
            </div>
        
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>

            {assign var="WIDTH" value=100 / ($REPORT_DATA|count + 2)}

            <div id="result-content">
                <table celspacing=0 celpading=0>
                     <thead>
                        {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}">{$HEADER}</th>
                        {/foreach}
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                             <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.id != 'all'}
                                    <td class="text-center">{$NO + 1}</td>
                                {else}
                                    <td class="text-center" colspan="2">{$ROW.label}</td>
                                {/if}

                                {if $ROW.id != 'all'}
                                    <td class="text-left">{$ROW.label}</td>
                                {/if}

                                {if $REPORT_OBJECT == 'INDUSTRY' || $REPORT_OBJECT == 'PROVINCE'}
                                     <td class="text-right">
                                        {$ROW.number}
                                    </td>
                                {else}
                                    <td class="text-right">
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.number_link}'>{$ROW.number}</a>
                                    </td>
                                {/if}

                                <td class="text-right">
                                    {$ROW.number_rate}
                                </td>
                                
                                <td class="text-right">
                                     {CurrencyField::convertToUserFormat($ROW.value)}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.value_rate}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SucceededFailedPotentialsByIndustryReportDetail.js")}"></script>
{/strip}