
{*
    TopCustomerBySalesReport.tpl
    Author: Phuc Lu
    Date: 2020.04.14
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
                {if $PARAMS.target == 'Contact'}
                    <button type="button" name="{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}" class="cursorPointer btn btn-default addToMarketingList" title="{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}"><div class="far fa-plus" aria-hidden="true"></div>{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}</button>
                {/if}
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
                                <td class="text-left">
                                    <a data-module="{$PARAMS.target}s" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module={$PARAMS.target}s&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                </td>
                                <td class="text-right">
                                    <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.potential_link}'>{$ROW.potential_number}</a>
                                </td>
                                <td class="text-right">
                                    <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.quote_link}'>{$ROW.quote_number}</a>
                                </td>
                                <td class="text-right">
                                    <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.saleorder_link}'>{$ROW.saleorder_number}</a>
                                </td>
                                <td class="text-right">{CurrencyField::convertToUserFormat($ROW.amount)}</td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="6">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/TopCustomerBySalesReportDetail.js")}"></script>
{/strip}