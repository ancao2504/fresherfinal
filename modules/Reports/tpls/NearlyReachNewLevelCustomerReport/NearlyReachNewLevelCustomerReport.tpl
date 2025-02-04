
{*
    NearlyReachNewLevelCustomerReport.tpl
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
                                <td class="text-center">{($NO + 1)}</td>
                                <td class="text-left">
                                    <a data-module="{$PARAMS.target}s" data-record-id="{$ROW.record_id}" target="_blank" onclick="window.open('index.php?module={$PARAMS.target}s&view=Detail&record={$ROW.record_id}')">{$ROW.record_name}</a>
                                </td>
                                <td class="text-center">
                                    <a class="emailField" data-rawvalue="{$ROW.email}" onclick="Vtiger_Helper_Js.getInternalMailer({$ROW.record_id},'email','{$PARAMS.target}s');">{$ROW.email}</a>
                                </td>
                                <td class="text-center">
                                    {$ROW.phone}
                                </td>                                
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.sales)}
                                </td>                                                       
                                <td class="text-right">
                                    {CurrencyField::convertToUserFormat($ROW.missing_value)}
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

    <div id="report_modal" title="{vtranslate('LBL_REPORT_ROI_CHART', 'Reports')}" class="modal-dialog modal-md modal-content modal-template-md hide">
        {include file='ModalHeader.tpl'|vtemplate_path:'Vtiger'}
        <div id="marketing_list"></div>
    </div>

    <link rel="stylesheet" type="text/css" href="{vresource_url("modules/Reports/resources/CustomReport.css")}" />
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/CustomReportHelper.js")}"></script>
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/NearlyReachNewLevelCustomerReportDetail.js")}"></script>
{/strip}