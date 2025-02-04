
{*
    GeographyReport.tpl
    Author: Phuc Lu
    Date: 2020.60.30
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
                {if $PARAMS.target == 'Contact'}
                    <button type="button" name="{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}" class="cursorPointer btn btn-default addToMarketingList" title="{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}"><div class="far fa-plus" aria-hidden="true"></div>{vtranslate('LBL_REPORT_ADD_TO_MARKETING_LIST', 'Reports')}</button>
                {/if}
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
                                {if $PARAMS.report_module == 'Accounts'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="Accounts" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.cur_sales)}
                                    </td>                             
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.prev_sales)}
                                    </td>                            
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>                                                      
                                    <td class="text-right">
                                        {$ROW.latest_date_of_so}
                                    </td>
                                {/if}

                                {if $PARAMS.report_module == 'Meeting'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="Calendar" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=Calendar&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                                
                                    <td class="text-center">
                                        {$ROW.starttime}
                                    </td>                            
                                    <td class="text-center">
                                        {$ROW.duetime}
                                    </td>                                           
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>
                                {/if}
                                
                                {if $PARAMS.report_module == 'SalesOrder'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="SalesOrder" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=SalesOrder&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-center">
                                        <a target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.accountid}')">{$ROW.accountname}</a>
                                    </td>                               
                                    <td class="text-center">
                                        {$ROW.sostatus}
                                    </td>                            
                                    <td class="text-center">
                                        {CurrencyField::convertToUserFormat($ROW.total)}
                                    </td>                                            
                                    <td class="text-center">
                                        {$ROW.createdtime}
                                    </td>                                         
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>
                                {/if}

                                {if $PARAMS.report_module == 'HelpDesk'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="HelpDesk" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=HelpDesk&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-center">
                                        {$ROW.status}
                                    </td>                            
                                    <td class="text-center">
                                        {$ROW.priority}
                                    </td>                                            
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>
                                {/if}
                                
                                {if $PARAMS.report_module == 'Potentials'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">{$ROW.potential_no}</td>
                                    <td class="text-left">
                                        <a data-module="Potentials" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=Potentials&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-center">
                                        {$ROW.sales_stage}
                                    </td>                            
                                    <td class="text-center">
                                        {$ROW.closingdate}
                                    </td>                                            
                                    <td class="text-center">
                                        <a target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.accountid}')">{$ROW.accountname}</a>
                                    </td>                                           
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>                                                        
                                    <td class="text-center">
                                        {CurrencyField::convertToUserFormat($ROW.forecast_amount)}
                                    </td>
                                {/if}
                                
                                {if $PARAMS.report_module == 'Contacts'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="Contacts" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=Contacts&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-center">
                                         <a target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.accountid}')">{$ROW.accountname}</a>
                                    </td>                             
                                    <td class="text-center">
                                        {$ROW.mobile}
                                    </td>                            
                                    <td class="text-center">
                                        <a class="emailField" data-rawvalue="{$ROW.email}" onclick="Vtiger_Helper_Js.getInternalMailer({$ROW.record_id},'email','{$PARAMS.report_module}');">{$ROW.email}</a>
                                    </td>                                                      
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>
                                {/if}

                                {if $PARAMS.report_module == 'Leads'}
                                    <td class="text-left">{$ROW.bill_city}</td>
                                    <td class="text-left">
                                        <a data-module="Leads" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module=Leads&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                    </td>                             
                                    <td class="text-center">
                                        {$ROW.leadstatus}
                                    </td>                             
                                    <td class="text-center">
                                        {$ROW.mobile}
                                    </td>                            
                                    <td class="text-center">
                                        <a class="emailField" data-rawvalue="{$ROW.email}" onclick="Vtiger_Helper_Js.getInternalMailer({$ROW.record_id},'email','{$PARAMS.report_module}');">{$ROW.email}</a>
                                    </td>                                                      
                                    <td class="text-center">
                                        {$ROW.assignee}
                                    </td>
                                {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/GeographyReportDetail.js")}"></script>
{/strip}