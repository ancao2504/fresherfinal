{*
    SalesByCustomerGroupReportWidgetFilter.tpl
    Author: Phuc Lu
    Date: 2020.06.04
*}

{strip}
    {assign var="CURRENT_YEAR" value='Y'|date}
    {assign var="FROM_YEAR" value=$CURRENT_YEAR - 10}

    <div class="form-group">
        <label class="control-label fieldLabel col-sm-5">
            <strong>{vtranslate('LBL_REPORT_CHART_TITLE', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7">
            <input name="filter[chart_title]" class="form-control widgetFilter reloadOnChange" value="{$PARAMS.chart_title}" />
        </div>
        <div style="clear: both;"></div>
        
        <label class="control-label fieldLabel col-sm-5 month-field">
            <strong>{vtranslate('LBL_REPORT_CHOOSE_YEAR', 'Reports')}:</strong>
        </label>
        <div class="controls fieldValue col-sm-7 year-field">    
            <select name="filter[year]" id="year" class="inputElement select2 select2-offscreen form-control widgetFilter reloadOnChange">
                {for $INDEX=$FROM_YEAR to $CURRENT_YEAR}
                    <option value="{$INDEX}" {if $INDEX == $PARAMS.year}selected{/if}>{$INDEX}</option>
                {/for}
            </select>        
        </div>
    </div>
{/strip}