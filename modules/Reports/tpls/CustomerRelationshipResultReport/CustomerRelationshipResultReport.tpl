
{*
    SalesOrderByCustomerTypeReportChart.tpl
    Author: Phuc Lu
    Date: 2020.04.21
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
                                    {$ROW.call}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.meeting}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.task}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.comment}
                                </td>

                                <td class="text-right">
                                    {$ROW.emails}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.sms}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.zalo}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.facebook}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.hana}
                                </td>
                                
                                <td class="text-right">
                                    {$ROW.last_call_time}
                                </td>

                                <td class="text-right">
                                    {$ROW.last_activity_time}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomerRelationshipResultReportDetail.js")}"></script>
{/strip}