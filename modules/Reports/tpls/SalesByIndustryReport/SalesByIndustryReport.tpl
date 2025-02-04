
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
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="far fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="far fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
            </div>

            <div id="result-content">
                <table celspacing=0 celpading=0>
                    <thead>
                        <tr>
                            {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}">{$HEADER}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.id !== 'all'}
                                    <td class="text-center">{$NO + 1}</td>
                                {else}
                                    <td class="text-center" colspan="2">
                                        {$ROW.name}
                                    </td>
                                {/if}

                                {if $ROW.id !== 'all'}
                                    <td class="text-left">
                                        {if $REPORT_OBJECT == 'EMPLOYEE'}
                                            <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.name}</a>
                                        {else}
                                            {$ROW.name}
                                        {/if}
                                    </td>
                                {/if}

                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>
                                
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="11">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SalesByIndustryReportDetail.js")}"></script>
{/strip}