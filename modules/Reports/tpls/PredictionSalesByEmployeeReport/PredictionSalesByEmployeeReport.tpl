
{*
    PredictionSalesByEmployeeReport.tpl
    Author: Phuc Lu
    Date: 2020.05.20
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

            {assign var="WIDTH" value=100 / ($REPORT_DATA|count + 2)}

            <div id="result-content">
                <table celspacing=0 celpading=0>
                     <thead>
                        <tr>
                            <th width="20%" style="min-width: 300px;" rowspan="2">{vtranslate('LBL_REPORT_'|cat:{$REPORT_OBJECT}, 'Reports')}</th>

                            {foreach from=$RANGES item=RANGE key=KEY name=INDEX}
                                <th colspan="2">{vtranslate('LBL_REPORT_MONTH', 'Reports')} {$RANGE.label}</th>
                            {/foreach}

                            <th rowspan="2" style="min-width: 125px;">{vtranslate('LBL_REPORT_TOTAL_PREDICTED_POTENTIAL_SALES', 'Reports')}</th>
                        </tr>
                        <tr>
                            {foreach from=$RANGES item=RANGE key=KEY name=INDEX}
                                <th style="min-width: 75px;">{vtranslate('LBL_REPORT_POTENTIAL_NUMBER', 'Reports')}</th>
                                <th style="min-width: 125px;">{vtranslate('LBL_REPORT_VALUE', 'Reports')}</th>
                            {/foreach}
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr {if $smarty.foreach.INDEX.last} style="font-weight: bold !important;"{/if}>
                                {if $ROW.id != 'all'}
                                    <td class="text-left">
                                        {if $REPORT_OBJECT == 'EMPLOYEE'}
                                            <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                        {/if}

                                        {if $REPORT_OBJECT == 'DEPARTMENT'}
                                            {$ROW.name}
                                        {/if}
                                    </td>
                                {else}
                                    <td class="text-left">
                                        {$ROW.user_full_name}
                                    </td>
                                {/if}

                                {foreach from=$RANGES item=RANGE key=KEY name=INDEX}
                                    <td class="text-right">
                                        {formatNumberToUser($ROW[$RANGE.key|cat:'_number'])}
                                    </td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW[$RANGE.key|cat:'_value'])}</td>
                                {/foreach}
                                
                                <td class="text-right">
                                     {CurrencyField::convertToUserFormat($ROW.all)}
                                </td>
                                
                            </tr>
                        {foreachelse}
                            <tr>
                                <td class="text-center" colspan="{($RANGES|count * 2 + 2)}">{vtranslate('LBL_REPORT_NO_DATA', 'Reports')}</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/PredictionSalesByEmployeeReportDetail.js")}"></script>
{/strip}