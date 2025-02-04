
{*
    SummarySalesByMarketReport.tpl
    Author: Phuc Lu
    Date: 2020.04.14
*}

{strip}
    <div id="custom-report-detail">
        <div id="filter">
            {assign var="QUARTER" value=[1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV']}
            {assign var="CURRENT_YEAR" value='Y'|date}
            {assign var="FROM_YEAR" value=$CURRENT_YEAR - 5}
            {assign var="TO_YEAR" value=$CURRENT_YEAR + 5}
            
            {if isset($PARAMS['year']) && !empty($PARAMS['year'])}
                {assign var="CURRENT_SELECTED_YEAR" value=$PARAMS['year']}
            {else}
                {assign var="CURRENT_SELECTED_YEAR" value='Y'|date}
            {/if}

            {if isset($PARAMS['quarter']) && !empty($PARAMS['quarter'])}
                {assign var="CURRENT_SELECTED_QUARTER" value=$PARAMS['quarter']}
            {else}
                {assign var="CURRENT_SELECTED_QUARTER" value=('m'|date)/4+1}
            {/if}

            {if isset($PARAMS['month']) && !empty($PARAMS['month'])}
                {assign var="CURRENT_SELECTED_MONTH" value=$PARAMS['month']}
            {else}
                {assign var="CURRENT_SELECTED_MONTH" value='m'|date}
            {/if}

            <script>
                var current_selected_month = {$CURRENT_SELECTED_MONTH};
                var current_selected_quarter = {$CURRENT_SELECTED_QUARTER};
                uimeta.field.set({Zend_Json::encode($FIELD_VALIDATORS)});
            </script>

            <form id="form-filter" name="filter" action="" method="GET" class="filter-container recordEditView">
                <input type="hidden" name="module" value="Reports"/>
                <input type="hidden" name="view" value="Detail"/>
                <input type="hidden" name="record" value="{$REPORT_ID}"/>
                <div class="filter-group">
                    <div class="control-label fieldLabel col-sm-4">
                        {vtranslate('LBL_REPORT_CHOOSE_TIME', 'Reports')}:
                    </div>
                    <input type="hidden" name="report_detail" value="1"/>
                    <div class="control-label col-sm-8">
                        <div class="time-group">    
                            <select name="period" id="period" class="filter dislayed-time">
                                <option value="month" {if isset($PARAMS['period']) && $PARAMS['period'] == 'month'}selected{/if}>{vtranslate('LBL_REPORT_MONTH', 'Reports')}</option>
                                <option value="quarter" {if isset($PARAMS['period']) && $PARAMS['period'] == 'quarter'}selected{/if}>{vtranslate('LBL_REPORT_QUARTER', 'Reports')}</option>
                                <option value="year" {if isset($PARAMS['period']) && $PARAMS['period'] == 'year'}selected{/if}>{vtranslate('LBL_REPORT_YEAR', 'Reports')}</option>
                                <option value="custom" {if isset($PARAMS['period']) && $PARAMS['period'] == 'custom'}selected{/if}>{vtranslate('LBL_REPORT_CUSTOM', 'Reports')}</option>
                            </select>

                            <select name="month" id="month" class="filter dislayed-time">
                                {for $INDEX=1 to 12}
                                    <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_MONTH}selected{/if}>{$INDEX}</option>
                                {/for}
                            </select>
                            <select name="quarter" id="quarter" class="filter dislayed-time hide">
                                {foreach from=$QUARTER key=INDEX item=ROMAN_NUMBER}
                                    <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_QUARTER}selected{/if}>{$ROMAN_NUMBER}</option>
                                {/foreach}
                            </select>

                            <select name="year" id="year" class="filter dislayed-time">
                                {for $INDEX=$FROM_YEAR to $TO_YEAR}
                                    <option value="{$INDEX}" {if $INDEX == $CURRENT_SELECTED_YEAR}selected{/if}>{$INDEX}</option>
                                {/for}
                            </select>

                            <span class="date-time-field hide">{vtranslate('LBL_REPORT_FROM', 'Reports')}</span>&nbsp;
                            <div class="input-group date-time-field hide">
                                <input name="from_date" id="from-date" type="text" class="dateField form-control dislayed-time" data-fieldtype="date" value="{if isset($PARAMS['from_date'])}{$PARAMS['from_date']}{/if}" placeholder=""><span class="input-group-addon"><i class="fa fa-calendar "></i></span>
                            </div>

                            <span class="date-time-field hide">{vtranslate('LBL_REPORT_TO', 'Reports')}</span>&nbsp;
                            <div class="input-group date-time-field hide">
                                <input name="to_date" id="to-date" data-specific-rules={literal}'[{"name":"greaterThanDependentField","params":["from_date"]}]'{/literal} type="text" class="dateField form-control dislayed-time" data-fieldtype="date"  value="{if isset($PARAMS['to_date'])}{$PARAMS['to_date']}{/if}" placeholder=""><span class="input-group-addon"><i class="fa fa-calendar "></i></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="filter-group">
                    <div class="control-button">
                        <button type="submit" class="btn btn-success saveButton">{vtranslate('LBL_REPORT_VIEW_REPORT', 'Reports')}</button>
                    </div>
                </div>             
            </form>
        </div>

        <div id="chart">
            {include file="modules/Reports/tpls/CustomReportAddChartToDashboard.tpl"}
            <div style="clear: both;"></div>
            {$CHART}
        </div>
        
        <div id="result">
            <div id="result-actions">
                <button type="button" name="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" class="cursorPointer btn btn-default customReportAction" title="{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}" data-href="index.php?module=Reports&view=ExportReport&mode=GetXLS&record={$REPORT_ID}&source=tabular"><div class="fa fa-download" aria-hidden="true"></div>{vtranslate('LBL_REPORT_EXPORT_REPORT', 'Reports')}</button>
                <button type="button" name="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" class="cursorPointer btn btn-default" title="{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}" onclick="window.print();"><div class="fa fa-print" aria-hidden="true"></div>{vtranslate('LBL_REPORT_PRINT_REPORT', 'Reports')}</button>                
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
                                <td class="text-center">{$ROW.no}</td>
                                <td class="text-left">{$ROW.bill_city}</td>
                                <td class="text-right">{CurrencyField::convertToUserFormat($ROW.sales)}</td>
                                <td class="text-right">{formatNumberToUser($ROW.ratio, 'float')}%</td>
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
    <script type="text/javascript" src="{vresource_url("modules/Reports/resources/SummarySalesByMarketReportDetail.js")}"></script>
{/strip}