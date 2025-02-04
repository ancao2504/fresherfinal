
{*
    AverageDaysWonPotentialByEmployeeReport.tpl
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
                        {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER name=INDEX}
                            <th width="{$WIDTH}">{$HEADER}</th>
                        {/foreach}
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO name=INDEX}
                            <tr>
                                <td class="text-center">{$NO + 1}</td>

                                <td class="text-left">
                                    {if $REPORT_OBJECT == 'EMPLOYEE'}
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    {/if}

                                    {if $REPORT_OBJECT == 'DEPARTMENT'}
                                        {$ROW.name}
                                    {/if}
                                </td>

                                <td class="text-right">
                                    {if $REPORT_OBJECT == 'EMPLOYEE'}
                                        <a target="_blank" onclick="window.open(this.dataset.href)" data-href='{$ROW.number_link}'>{$ROW.number}</a>
                                    {/if}

                                    {if $REPORT_OBJECT == 'DEPARTMENT'}
                                        {$ROW.number}
                                    {/if}
                                </td>
                                
                                <td class="text-right">
                                     {CurrencyField::convertToUserFormat($ROW.avg_days)}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/AverageDaysWonPotentialByEmployeeReportDetail.js")}"></script>
{/strip}