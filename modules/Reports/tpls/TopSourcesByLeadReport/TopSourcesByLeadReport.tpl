
{*
    TopSourcesByLeadReport.tpl
    Author: Phuc Lu
    Date: 2020.08.10
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
                        {foreach from=$REPORT_HEADERS item=WIDTH key=HEADER}
                            <th width="{$WIDTH}">{$HEADER}</th>
                        {/foreach}
                    </thead>
                    <tbody>
                        {foreach from=$REPORT_DATA item=ROW key=NO}
                            <tr>
                                <td class="text-center">{$NO + 1}</td>
                                {if $TARGET_MODULE == 'SOURCE_LEAD'}
                                    <td class="text-left">
                                        {$ROW.leadsource}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'INDUSTRY_LEAD'}
                                    <td class="text-left">
                                        {$ROW.industry}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_LEAD'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_CONTACT'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.contact_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_ACCOUNT'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.account_number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'CAMPAIGN_CONVERSION_RATE'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.converted_lead_number)}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.conversion_rate)}%
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_ACTUAL_COST'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.actualcost)}
                                    </td>
                                {/if}   

                                {if $TARGET_MODULE == 'CAMPAIGN_ACTUAL_ROI'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.actualroi)}
                                    </td>
                                {/if}                              
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_ACTUAL_SALES'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>                                    
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.sales_count)}
                                    </td>
                                {/if}                          
                                
                                {if $TARGET_MODULE == 'ACCOUNT_SALES'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.accountid}')">{$ROW.accountname}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>                                    
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.sales_count)}
                                    </td>
                                {/if}                            
                                
                                {if $TARGET_MODULE == 'CAMPAIGN_POTENTIAL_SALES'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Campaigns&view=Detail&record={$ROW.campaignid}')">{$ROW.campaignname}</a>
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>                                    
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.potential_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'SOURCE_SALES'}
                                    <td class="text-left">
                                        {$ROW.leadsource}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'INDUSTRY_SALES'}
                                    <td class="text-left">
                                        {$ROW.industry}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.sales)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'SOURCE_POTENTIAL_SALES'}
                                    <td class="text-left">
                                        {$ROW.leadsource}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.potential_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'INDUSTRY_POTENTIAL_SALES'}
                                    <td class="text-left">
                                        {$ROW.industry}
                                    </td>
                                    <td class="text-right">
                                        {CurrencyField::convertToUserFormat($ROW.potential_sales)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.potential_number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'SOURCE_CONVERTED_LEAD'}
                                    <td class="text-left">
                                        {$ROW.leadsource}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.converted_lead_number)}
                                    </td>
                                {/if}

                                {if $TARGET_MODULE == 'INDUSTRY_CONVERTED_LEAD'}
                                    <td class="text-left">
                                        {$ROW.industry}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.lead_number)}
                                    </td>
                                    <td class="text-right">
                                        {formatNumberToUser($ROW.converted_lead_number)}
                                    </td>
                                {/if}
                                
                                {if $TARGET_MODULE == 'CALL'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>
                                    <td class="text-right">{formatNumberToUser($ROW.number)}</td>
                                {/if}

                                {if $TARGET_MODULE == 'TICKET'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>
                                    <td class="text-right">{formatNumberToUser($ROW.ticket_number)}</td>
                                    <td class="text-right">{formatNumberToUser($ROW.closed_ticket_number)}</td>
                                {/if}

                                {if $TARGET_MODULE == 'CUSTOMER_RECEIPT'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Accounts&view=Detail&record={$ROW.id}')">{$ROW.account_name}</a>
                                    </td>
                                    <td class="text-right">{formatNumberToUser($ROW.receipt_number)}</td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW.receipt_amount)}</td>
                                {/if}

                                {if $TARGET_MODULE == 'USER_RECEIPT'}
                                    <td class="text-left">
                                        <a target="_blank" onclick="window.open('index.php?module=Users&view=PreferenceDetail&parent=Settings&record={$ROW.id}')">{$ROW.user_full_name}</a>
                                    </td>
                                    <td class="text-right">{formatNumberToUser($ROW.receipt_number)}</td>
                                    <td class="text-right">{CurrencyField::convertToUserFormat($ROW.receipt_amount)}</td>
                                {/if}
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/TopSourcesByLeadReportDetail.js")}"></script>
{/strip}