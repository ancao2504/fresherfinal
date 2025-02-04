
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
                            <th width="{$WIDTH}">{$HEADER}</th>
                        {/foreach}
                        <th width="3%">{vtranslate('LBL_REPORT_ROI_CHART', 'Reports')}</th>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.campaign_id != 'all'}
                                    <td class="text-center">{$NO + 1}</td>
                                {else}
                                    <td class="text-center name" colspan="2"><a style="color: inherit !important; cursor: inherit;" href="javascript:void(0)">{$ROW.campaign_name}</a></td>
                                {/if}

                                {if $ROW.campaign_id != 'all'}
                                    <td class="text-left name">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaign_id}')">{$ROW.campaign_name}</a>
                                    </td>
                                {/if}                                
                                <td class="text-right">
                                    {formatNumberToUser($ROW.expected_sales_order)}
                                </td>
                                <td class="text-right">
                                    {formatNumberToUser($ROW.actual_sales_order)}
                                </td>                                
                                <td class="text-right">
                                    {formatNumberToUser($ROW.expected_response)}
                                </td>                                
                                <td class="text-right">
                                    {formatNumberToUser($ROW.actual_response)}
                                </td>
                                <td class="text-right budget">
                                    {CurrencyField::convertToUserFormat($ROW.budget)}
                                </td>
                                <td class="text-right cost">
                                    {CurrencyField::convertToUserFormat($ROW.cost)}
                                </td>                                
                                <td class="text-right expected_revenue">
                                    {CurrencyField::convertToUserFormat($ROW.expected_revenue)}
                                </td>                                
                                <td class="text-right actual_revenue">
                                    {CurrencyField::convertToUserFormat($ROW.actual_revenue)}
                                </td>
                                <td class="text-right">
                                    <a href="javascript:void(0)" class="draw-roi">{vtranslate('LBL_REPORT_VIEW', 'Reports')}</a>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="{($REPORT_HEADERS|count + 1)}">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="report_modal" title="{vtranslate('LBL_REPORT_ROI_CHART', 'Reports')}" class="modal-dialog modal-md modal-content modal-template-md hide">
        {include file='ModalHeader.tpl'|vtemplate_path:'Vtiger'}
        <div id="report_chart"></div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CampaignEffectReportDetail.js")}"></script>
{/strip}