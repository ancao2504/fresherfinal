
{*
    AnalyzeSalesFluctuationReport.tpl
    Author: Phuc Lu
    Date: 2020.08.19
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
                            {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER}
                                <th width="{$WIDTH}">{$HEADER}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr>
                                <td class="text-left" style="font-weight:bold; white-space: nowrap;">{$ROW.name}</td>                                
                                {foreach from=$ROW.data item=SALES key=TIME_NO}
                                    <td class="text-right">{formatNumberToUser($SALES, $FORMAT_NUMBER)}</td>     
                                {/foreach}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/AnalyzeSalesFluctuationReportDetail.js")}"></script>
{/strip}